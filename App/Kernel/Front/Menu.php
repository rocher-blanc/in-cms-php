<?php

namespace App\Kernel\Front;

class Menu
{
    private $page_default_id = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    private function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    private function getDefaultIdPage()
    {
        return $this->page_default_id ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function load( $id )
    {
        $contentRows = \DB::for_table('menu_element')
            ->left_outer_join('menu_element_lang', array('menu_element.menu_element_id', '=', 'menu_element_lang.menu_element_lang_menu_element_id'))
            ->where(['menu_element_lang.menu_element_lang_lang_id' => \App\Kernel\Lang::getInstance()->getActive()->id,'menu_element.menu_element_menu_id' => $id])
            ->order_by_asc('menu_element.menu_element_parent_id')
            ->order_by_asc('menu_element.menu_element_order')
            ->find_many();

        $this->loadDefaultIdPage();
        $tree = $this->getTreeMenu( $contentRows ) ;


        // dump($tree);

        return $tree['array'] ;
    }

    private function loadDefaultIdPage()
    {
        $page = \DB::for_table('page')
            ->select('page_id')
            ->where(['page_default' => 1])
            ->find_one();

        $this->page_default_id = $page->page_id ;
    }

    private function getElementModule( $id )
    {
        $idMod = $id ;
        $mod = \DB::for_table('module')
            ->select('module_class_name')
            ->select('module_default')
            ->where(array('module_id' => $id))
            ->findOne();

        if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $mod->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Front\\" . ucfirst( $mod->module_class_name );
        else																			 				 $ControllerClass = '\App\Kernel\Front\Controller' ;

        $Controller = new $ControllerClass;
        $Controller->setEntityName( $mod->module_class_name );
        if ( $Controller->loadEntity() == true && $Controller->getEntity()->hasUrl() == true )
        {
            $id = $Controller->getEntity()->get( $Controller->getEntity()->getIdName() ) ;
            $url = $Controller->getEntity()->get( $Controller->getEntity()->getUrlName() ) ;

            $content = \DB::for_module( $Controller->getEntityName() ) ;

            if ( $url->hasLang() )
            {
                $tableLang  	= \DB::getTableNameLang( $Controller->getEntityName() ) ;
                $idNameInLang	= \DB::getIdNameInLang( $Controller->getEntityName() ) ;
                $langIdLangName	= \DB::getLangIdLangName( $Controller->getEntityName() ) ;

                $content = $content->left_outer_join( $tableLang , array( $id->fieldSql() , '=', $tableLang . '.' . $idNameInLang ))
                    ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id ) ;
            }

            $content = $content->select( $url->fieldSql() , 'url' )
                ->left_outer_join( 'seo' , array( $id->fieldSql() , '=', 'seo.seo_element_id' ))
                ->where_equal( 'seo.seo_lang_id' , \App\Kernel\Lang::getInstance()->getActive()->id )
                ->where_equal( 'seo.seo_module_id' , $idMod )
                ->select( $id->fieldSql() , 'id' )
                ->select('seo.seo_url')
                ->order_by_asc( $url->fieldSql() )
                ->find_many();

            $module = \DB::for_table('module_lang')
                ->select('module_lang_url')
                ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $idMod])
                ->find_one();

            $result = [];
            if ( $content )
            {
                foreach( $content as $row )
                {
                    $obj = new \stdClass();
                    $obj->id 		= -1;
                    $obj->label 	= $row->url;
                    $obj->url    	= \App\Kernel\Http::getInstance()->getUrl() . '/' . ( $this->Lang()->count() > 1 ? $this->Lang()->getActive()->url . '/' : '' ) . ( $mod->module_default == 0 ? $module->module_lang_url . '/' : '' ) . $row->seo_url;
                    $obj->type 		= 'link';
                    $obj->submenu 	= false;
                    $obj->subpages 	= [] ;
                    $obj->blank     = false ;
                    $obj->active    = false ;

                    $result[] = $obj ;
                }
            }

            return $result ;
        }

        return false ;
    }

    private function getTreeMenu($rows, $parent_id = -1)
    {
        $active = false ;
        $tree = [];

        foreach ( $rows as $row )
        {
            $row_parent_id = -1;
            if ($row->menu_element_parent_id != NULL)
            {
                $row_parent_id = $row->menu_element_parent_id;
            }
            if( $row_parent_id < $parent_id ) continue;
            if( $row_parent_id > $parent_id ) break;

            $obj = new \stdClass();
            $obj->id 		= $row->menu_element_id;
            $obj->label 	= $row->menu_element_lang_label;
            $obj->type 		= $row->menu_element_type;
            $obj->submenu 	= ( $row->menu_element_has_submenu == 0 ? false : true );

            $sub = $this->getTreeMenu( $rows, $row->menu_element_id ) ;

            $obj->subpages 	= $sub['array'] ;
            $obj->blank     = $row->menu_element_link_blank ;
            $obj->active    = ( $sub['active'] == true ? true : false ) ;

            switch( $row->menu_element_type )
            {
                case "section" :
                    $obj->url = 'javascript:;' ;
                    break;
                case "page" :
                    if ( $this->getDefaultIdPage() == $row->menu_element_value_id )
                    {
                        $obj->url = '' ;
                        if ( $this->Lang()->getActive()->id != $this->Lang()->getDefault()->id )
                        {
                            $obj->url = $this->Lang()->getActive()->url ;
                        }
                    }
                    else
                    {
                        $page = \DB::for_table('page_lang')
                            ->select('page_lang_url')
                            ->where(['page_lang_lang_id' => $this->Lang()->getActive()->id, 'page_lang_page_id' => $row->menu_element_value_id])
                            ->find_one();
                        $obj->url = ( $this->Lang()->count() > 1 ? $this->Lang()->getActive()->url . '/' : '' ) . $page->page_lang_url ;
                    }
                    break;
                case "module" :
                    if ( $row->menu_element_option == 'all' )
                    {
                        if ( $row->menu_element_has_submenu == 0 )
                        {
                            $module = \DB::for_table('module_lang')
                                ->select('module_lang_url')
                                ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $row->menu_element_module_id])
                                ->find_one();

                            $obj->url = ( $this->Lang()->count() > 1 ? $this->Lang()->getActive()->url . '/' : '' ) . $module->module_lang_url ;
                        }
                        else
                        {
                            $obj->url = NULL ;
                            $obj->subpages = $this->getElementModule( $row->menu_element_module_id ) ;
                        }
                    }
                    else
                    {

                    }
                    break;
                case "link" :
                    $obj->url = $row->menu_element_link_href ;
                    break;
            }

            if ( $this->Factory()->Url()->getFullUrl() == '/' . $obj->url )
            {
                $active = true ;
                $obj->active = true ;
            }

            if ( ( $row->menu_element_type == "module" or $row->menu_element_type == "page" ) && $row->url !== NULL )
            {
                $obj->url = \App\Kernel\Http::getInstance()->getUrl() . '/' . $obj->url ;
            }

            if ( $row->url === NULL )
            {
                $row->url = "javascript:;" ;
            }

            $tree[] = $obj;
        }

        return [ 'active' => $active , 'array' => $tree ];
    }
}