<?php

namespace App\Kernel\Back;

class Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_entity = NULL ;
    protected $_entity_name = "" ;
    protected $_entity_id = NULL ;

    protected $_action_name = "" ;
    protected $_action = NULL ;

    protected $_id = NULL ;
    protected $_token = NULL ;
    protected $_lang = NULL ;

    protected $_renderArray = [] ;

    protected $_msg = [] ;
    protected $_options = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $options = [] )
    {
        $this->_options = $options ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setRender( $key , $value )
    {
        $this->_renderArray[ $key ] = $value ;
    }

    public function setId( $var )
    {
        $this->_id = $var ;
    }

    protected function setEntity( $var )
    {
        $this->_entity = $var ;
    }

    protected function setEntityId( $var )
    {
        $this->_entity_id = $var ;
    }

    public function setEntityName( $var )
    {
        $this->_entity_name = $var ;
    }

    public function setActionName( $var )
    {
        $this->_action_name = $var ;
    }

    public function setLang( $var ) {
        $this->_lang = $var ;
    }

    public function setToken( $var )
    {
        $this->_token = $var ;
    }

    public function setOption( $key , $var )
    {
        $this->_options[ $key ] = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getOption( $key )
    {
        if ( array_key_exists( $key , $this->_options ) )   return $this->_options[ $key ] ;
        else                                                return false ;
    }

    protected function getRender()
    {
        return $this->_renderArray ;
    }

    protected function getId()
    {
        return $this->_id ;
    }

    protected function getToken()
    {
        return $this->_token ;
    }

    protected function getActionName()
    {
        return $this->_action_name ;
    }

    protected function getMethodName()
    {
        return $this->_action_name . 'Action' ;
    }

    public function getEntityId()
    {
        return $this->_entity_id ;
    }

    public function getEntityName()
    {
        return $this->_entity_name ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function getLang()
    {
        return $this->_lang ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function Message()
    {
        return \App\Kernel\Message::getInstance() ;
    }

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    protected function getMessage( $key = NULL )
    {
        if ( $key === NULL ) return $this->_msg ;
        else				 return $this->_msg[ $key ] ;
    }

    /* ***************************************************** */
    /* ******************   CONTAINER   ******************** */
    /* ***************************************************** */

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    public function getEntity()
    {
        return $this->Container()->module( $this->getEntityName() )->getEntity() ;
    }

    public function getRepository()
    {
        return $this->Container()->module( $this->getEntityName() )->getRepository( true ) ;
    }

    /* ************************************************** */
    /* *****************     INIT     ******************* */
    /* ************************************************** */

    protected function initRender()
    {
        $this->_renderArray = [] ;
        $this->_renderArray = [] ;
    }

    public function init()
    {
        $this->appendEntityInfo();
        $this->getRepository()->checkIfPatchTable( $this->getEntityId() );
        $this->initRender();
    }

    /* ************************************************** */
    /* *****************   MESSAGES   ******************* */
    /* ************************************************** */

    protected function m( $key )
    {
        if ( array_key_exists( $key , $this->getMessage() ) ) 	return $this->getMessage( $key ) ;
        else													return $this->Message()->get( $key ) ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function execute()
    {
        if ( $this->loadEntity() == true )
        {
            $this->init() ;

            if ( $this->isValidAction() == true )
            {
                $method = $this->getMethodName() ;
                $this->$method();
            }
        }
        else
        {
            $this->Factory()->Response()->error("Impopssible de charger l'entity '" . $this->getEntityName() . "'") ;
        }
    }

    protected function isValidAction()
    {
        if ( $this->getEntity()->hasAction( $this->getActionName() ) )
        {
            if ( method_exists( $this , $this->getMethodName() ) == true )  return true ;
            else			 												return $this->Factory()->Response()->error("La méthode '" . $this->getMethodName() . "' n'est pas disponible dans le controller") ;
        }
        else
        {
            $this->Factory()->Response()->error("L'action '" . $this->getActionName() . "' est impossible") ;
        }
    }

    public function loadEntity()
    {
        $ct = \DB::for_table('module')
            ->where(array('module_class_name' => $this->getEntityName() , 'module_active' => 1))
            ->count();

        if ( $ct == 0 ) return false ;

        if ( ! file_exists( ENTITY_PATH . '/' . $this->getEntityName() . '.php' ) )
        {
            $this->Factory()->Response()->error("Le fichier '" . $this->getEntityName() . "' n'éxiste pas");
            return false ;
        }

        if ( !is_object( $this->getEntity() ) ) return false ;
        else							 		return true ;
    }

    /* ************************************************** */
    /* ******************   PARENT   ******************** */
    /* ************************************************** */

    protected function getParent()
    {
        $target = $this->getEntity()->get( $this->getEntity()->getParentTargetName() ) ;
        $alias  = 'titre' ;
        $array  = [] ;

        $content = $this->getRepository()->findAllForSelect( $target , $alias , $this->getEntity()->getParentName() ) ;

        if ( $content ) $array = $this->getTreeParent( $content , $alias ) ;

        return $array;
    }

    protected function getTreeParent( $rows , $alias , $parent_id = -1 , $level = 0 )
    {

        $tree = [];
        foreach( $rows as $key => $row )
        {
            if ( $row->get( $this->getEntity()->getParentName() ) == $parent_id or ( $row->get( $this->getEntity()->getParentName() ) === NULL && $parent_id == -1 ) )
            {
                $obj = new \stdClass();
                $obj->id 		= $row->get('id');
                $obj->$alias	= $row->get( $alias );
                $obj->level	    = $level;
                if ( $row->get('id') == $this->getId() ) $obj->noview = true ;
                unset( $rows[ $key ]);
                $obj->subpages 	= ( $row->get('id') != $this->getId() ? $this->getTreeParent($rows, $alias, $row->get('id'), $level + 1 ) : [] );
                $tree[] = $obj;
            }
        }

        return $tree;
    }

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function generateForm( $value = false )
    {
        $form = $this->Factory()->Form() ;
        $form->initLib();

        if ( $value == true )
        {
            $content = $this->getRepository()->findOne( $this->getId() );

            if ( ! $content )
            {
                $this->Factory()->Response()->flashAndRedirect( $this->m("have_no_content") ) ;
            }

            if ( $this->getEntity()->hasMultiLang() )
            {
                foreach( $this->Lang()->getAll() as $lang )
                {
                    $contentLang[ $lang->id ] = $this->getRepository()->findOneLang( $this->getId() , $lang->id );
                }
            }

            if ( !empty( $this->getEntity()->getField() ) )
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->hasLang() == true )
                    {
                        $array = [];
                        foreach( $this->Lang()->getAll() as $lang )
                        {
                            if ( is_object( $contentLang[ $lang->id ] ) ) $array[ $lang->url ] = $contentLang[ $lang->id ]->get( $row->getColumn() );
                        }
                        $value = $array ;
                    }
                    else
                    {
                        if ( $row->getType() == 'checkbox' )
                        {
                            $value = $this->getAssocValue( $row->getName() ) ;
                        }
                        else if ( $row->getType() == 'date' )
                        {
                            $date  = new \DateTime( $content->get( $row->getColumn() ) ) ;
                            $value = $date->format('d/m/Y');
                        }
                        else if ( $row->getType() == 'gallery' )
                        {
                            $value = $this->getId();
                        }
                        else
                        {
                            $value = $content->get( $row->getColumn() );
                        }
                    }

                    $this->getEntity()->build( $row->getName() )->field()->setValue( $value ) ;
                }
            }
        }

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( $row->isParent() == true )
                {
                    $opt = $this->getParent();
                    $this->getEntity()->get( $row->getName() )->setData( 'option' , $opt );
                }
                else if ( $row->isAssociated() == true )
                {
                    $opt = $this->getValueAssociated( $row , "array" , true );
                    $this->getEntity()->get( $row->getName() )->setData( 'option' , $opt );
                }
            }
        }

        $arrayField = [] ;
        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
                {
                    $arrayField[] = [
                        "Form_HTML" => $form->genHTML( $row ),
                        "title" 	=> $row->getData('title'),
                        "type" 		=> $row->getType(),
                        "comment"	=> $row->getComment(),
                        "required"	=> $row->isRequired(),
                        "error" 	=> $row->getError()
                    ];
                }
            }
        }

        $this->setRender( 'cdn_css' , $form->getCdnCSS() ) ;
        $this->setRender( 'cdn_js' , $form->getCdnJS() ) ;

        $this->setRender( 'css' , $form->getLibCSS() ) ;
        $this->setRender( 'js' , $form->getLibJS() ) ;

        $this->setRender( 'field' , $arrayField ) ;
    }

    // Systeme de many / one TO many / one
    protected function getValueAssociated( $row , $returnType = NULL , $form = false )
    {
        $Controller = \App\Kernel\Container::getInstance()->module( $row->getObject() )->getController(true, [ 'noAppend' => true ]);

        if ( $row->getData('var') == $Controller->getEntity()->getParentTargetName() )
        {
            $this->getEntity()->build( $row->getName() )->field()->setData( "parent" , true ) ;
            $this->getEntity()->build( $row->getName() )->field()->setData( "target" , 'titre' ) ;
            $this->getEntity()->build( $row->getName() )->field()->setData( "noEmptyValue" , true ) ;

            if ( $form ) $returnType = NULL ;
        }

        if ( $Controller === false )
        {
            $this->Factory()->Response()->error("L'objet '" . $row->getObject() . "' dans le champ '" . $row->getTitle() . "' est impossible à charger");
        }
        else
        {
            $tab = $Controller->getElementForAssociation( $row->getData('var') , $returnType ) ;

            //\App\Kernel\Debug::view( $tab );
            unset( $Controller );
            return $tab ;
        }
    }

    public function getElementForAssociation( $name , $returnType = NULL )
    {
        /*
        $idName     = \DB::getIdName( $this->getEntityName() ) ;
        $table 	    = \DB::getTableName( $this->getEntityName() ) ;

        $tableLang  = \DB::getTableNameLang( $this->getEntityName() ) ;


        if ( ! $target->hasLang() )
        {
            $content = \DB::for_module( $this->getEntityName() )
                ->select( $idName , 'id' )
                ->select( $target->getColumn() , $alias );
        }
        else
        {
            $idNameInLang	= \DB::getIdNameInLang( $this->getEntityName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getEntityName() ) ;

            $content = \DB::for_module( $this->getEntityName() )
                ->select( $tableLang . "." . $target->getColumn() , $alias )
                ->select( $table . "." . $idName , 'id' )
                ->left_outer_join( $tableLang ,[ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ])
                ->where_equal( $tableLang . '.' . $langIdLangName , $this->Lang()->getDefault()->id );
        }

        if ( $this->getEntity()->hasParent() )       $content = $content->select( $table . "." . $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $this->getEntity()->getParentName() );

        if ( $this->getEntity()->hasOrder() )        $content = $content->order_by_asc( $table . "." . $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() )->order_by_asc( $table . "." . $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() );
        else                                         $content = $content->order_by_asc( ( $target->hasLang() ? $tableLang : $table ) . "." . $target->getColumn() );

        // if ( $this->getEntity()->hasValidation() )   $content = $content->where_equal( $table . "." . $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );

        $content = $content->find_many();
        */

        $alias      = 'titre' ;
        $target     = $this->getEntity()->get( $name );
        $content    = $this->getRepository()->findAllForSelect( $target , $alias , $this->getEntity()->getParentName() ) ;

        /*if ( $this->getEntity()->hasParent() )
        {
            $returnType = NULL;
        }*/

        switch( $returnType )
        {
            case "array" :
                $tab = [];

                if ( $content )
                {
                    foreach( $content as $row )
                    {
                        $tab[ $row->id ] = $row->get( $alias );
                    }
                }

                return $tab ;
                break;
            default :
                if ( $this->getEntity()->hasParent() ) $content = $this->getTreeParent( $content , $alias ) ;
                break;
        }

        return $content ;
    }

    // Pour les checkbox dans le même module (systeme de table d'association)
    protected function getAssocValue( $nameField )
    {
        $content = \DB::for_module_assoc( $this->getEntityName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getEntityName() , $nameField ) )
            ->where_equal( \DB::getTableNameAssoc( $this->getEntityName() , $nameField ) . '_' . \DB::getIdName( $this->getEntityName() ) , $this->getId() )
            ->find_many();

        $result  = array() ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $result[] = $row->get( \DB::getTableNameAssocValue( $this->getEntityName() , $nameField ) ) ;
            }
        }

        return $result ;
    }

    /* ************************************************** */
    /* ************         DELETE         ************** */
    /* ************************************************** */

    protected function deleteOnMenu()
    {
        $ct = \DB::for_table('menu_element')
            ->where(['menu_element_value_id' => $this->getId() , 'menu_element_module_id' => $this->getEntityId()])
            ->count();

        if ( $ct > 0 )
        {
            $menus = \DB::for_table('menu')
                ->select('menu_id')
                ->find_many();

            if ( $menus )
            {
                foreach( $menus as $menu )
                {
                    $elms = \DB::for_table('menu_element')
                        ->where(['menu_element_menu_id' => $menu->menu_id , 'menu_element_value_id' => $this->getId() , 'menu_element_module_id' => $this->getEntityId()])
                        ->find_many();

                    if ( $elms )
                    {
                        foreach( $elms as $elm )
                        {
                            $this->deleteElementByParent( $elm->menu_element_id , $menu->menu_id ) ;
                            $lang = \DB::for_table('menu_element_lang')
                                ->where(['menu_element_lang_menu_element_id' => $elm->menu_element_id])
                                ->delete_many();

                            $parent_id = $elm->menu_element_parent_id ;

                            $elm->delete();

                            $brother = \DB::for_table('menu_element') ;
                            if ( is_null( $parent_id ) )   $bother = $brother->where_null('menu_element_parent_id') ;
                            else                           $bother = $brother->where_equal('menu_element_parent_id',$parent_id);

                            $brother = $brother->where(['menu_element_menu_id' => $menu->menu_id])
                                ->order_by_asc('menu_element_order')
                                ->find_many();

                            if ( $brother )
                            {
                                $index = 1;
                                foreach( $brother as $row )
                                {
                                    $row->set('menu_element_order',$index);
                                    $row->save();
                                    $index++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function deleteElementByParent( $idparent , $idmenu )
    {
        $contentRow = \DB::for_table('menu_element')
            ->where(['menu_element_parent_id' => $idparent , 'menu_element_menu_id' => $idmenu])
            ->find_many();

        if ( $contentRow )
        {
            foreach( $contentRow as $row )
            {
                $this->deleteElementByParent( $row->menu_element_id , $idmenu ) ;

                $lang = \DB::for_table('menu_element_lang')
                    ->where(['menu_element_lang_menu_element_id' => $row->menu_element_id])
                    ->delete_many();

                $row->delete();
            }
        }
    }

    protected function delete()
    {
        $content = $this->getRepository()->findOne( $this->getId() );
        $this->deleteOnMenu() ;
        if ( $content )
        {
            if ( $this->getEntity()->hasMultiLang() )
            {
                \DB::for_module_lang( $this->getEntityName() , $this->getId() )
                    ->delete_many();
            }

            if ( !empty( $this->getEntity()->getField() ) )
            {
                foreach( $this->getEntity()->getField() as $name => $row )
                {
                    if ( $row->getType() == 'checkbox' )
                    {
                        \DB::for_module_assoc( $this->getEntityName() , $name )
                            ->where_equal( \DB::getTableNameAssoc( $this->getEntityName() , $name ) . '_' . \DB::getIdName( $this->getEntityName() ) , $this->getId() )
                            ->delete_many();
                    }
                    else if ( $row->getType() == 'gallery' )
                    {
                        $Gallery = new \App\Kernel\Back\Gallery;
                        $Gallery->setElementId( $this->getId() );
                        $Gallery->setField( $row->getName() );
                        $Gallery->setModuleId( $this->getEntityId() );
                        if ( $row->hasThumb() )
                        {
                            foreach( $row->getThumb() as $thumb )
                            {
                                // width, height
                                $Gallery->setThumb( $thumb[0] , $thumb[1] );
                            }
                        }
                        $Gallery->deleteElement();
                    }
                }
            }

            if ( $this->getEntity()->hasUrl() )
            {
                $seo = new \App\Kernel\Back\Seo;
                $seo->setElementId( $this->getId() );
                $seo->setModuleId( $this->getEntityId() );
                $seo->delete();
            }

            if ( $this->getEntity()->hasParent() )
            {
                $parent_id = $content->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ;
                if ( $this->getEntity()->hasOrder() )
                {
                    $max = \DB::for_module( $this->getEntityName() );

                    if ( is_null( $parent_id ) )    $max = $max->where_null( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() );
                    else                            $max = $max->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $parent_id );

                    $max = $max->count();
                }

                $rst = \DB::for_module( $this->getEntityName() )->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $this->getId() ) ;
                if ( $this->getEntity()->hasOrder() ) $rst = $rst->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
                $rst = $rst->find_many();

                if ( $rst )
                {
                    $order = $max + 1;
                    foreach( $rst as $rep )
                    {
                        $rep->set( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $parent_id ) ;
                        if ( $this->getEntity()->hasOrder() ) $rep->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order );
                        $rep->save();
                        $order++;
                    }
                }
            }

            if ( $this->getEntity()->hasOrder() )
            {
                $order = $content->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                $rst = \DB::for_module( $this->getEntityName() )
                    ->select( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() )
                    ->select( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                if ( $this->getEntity()->hasParent() ) $rst = $rst->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $content->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ) ;
                $rst = $rst->where_gt( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order )->find_many();

                if ( $rst )
                {
                    foreach( $rst as $row )
                    {
                        $order = $row->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) - 1 ;
                        $row->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order );
                        $row->save();
                    }
                }
            }

            $content->delete();
            \App\Kernel\Back\Log::getInstance()->warning( 102 , "#" . $this->getId() . " - " . $this->getEntityName() ) ;

            $this->Factory()->Response()->returnJSON("delete_success", true ) ;
        }
        else
        {
            $this->Factory()->Response()->returnJSON("delete_failed") ;
        }
    }

    /* ************************************************** */
    /* ************   TABLEAU DE GESTION   ************** */
    /* ************************************************** */

    protected function generateTable()
    {
        $rightArray     = [] ;
        $thArray 	    = [] ;
        $tdArray 	    = [] ;
        $typeArray      = [] ;

        $order = ( $this->getApp()->request->get('order') != '' ? $this->getApp()->request->get('order') : NULL ) ;
        $by    = ( $this->getApp()->request->get('by') != '' ? $this->getApp()->request->get('by') : NULL ) ;

        if ( $order === NULL && $by === NULL )
        {
            if ( $this->getEntity()->hasOrder() )
            {
                $order = $this->getEntity()->getOrderName() ;
                $by    = 'asc' ;
            }
            else
            {
                $order = $this->getEntity()->getIdName() ;
                $by    = 'desc' ;
            }
        }

        $Guard = new \App\Kernel\Back\Acl;
        $Guard->setModule( $this->getEntityName() );
        $Guard->load();

        $rightArray['add']   		= $Guard->checkAdd() ;
        $rightArray['edit']  		= $Guard->checkUpdate() ;
        $rightArray['delete'] 		= $Guard->checkDelete() ;
        $rightArray['validation']  	= $Guard->checkValidation() ;
        $rightArray['config'] 		= $Guard->checkConfig() ;

        if ( !empty( $this->getEntity()->getField() ) )
        {
            // TH
            foreach( $this->getEntity()->getField() as $field )
            {
                if ( $field->getData('index') == true )
                {
                    $thArray[ $field->getName() ] = [
                        'name' => $field->getName(),
                        'title' => $field->getTitle(),
                        'value' => $this->getApp()->request->get( $field->getName() ),
                        'value_start' => $this->getApp()->request->get( $field->getName() . "_start" ),
                        'value_end' => $this->getApp()->request->get( $field->getName() . "_end" ),
                        'value_convert_start' => ( $field->getType() == 'date' ? $this->Factory()->Date()->convertUs( $this->getApp()->request->get( $field->getName() . "_start" ) ) : NULL ),
                        'value_convert_end' => ( $field->getType() == 'date' ? $this->Factory()->Date()->convertUs( $this->getApp()->request->get( $field->getName() . "_end" ) ) : NULL ),
                        'type' => $field->getType(),
                        'options' => ( $field->isAssociated() == true ? $this->getValueAssociated( $field , "array" , true ) : NULL )
                    ];

                    if ( $field->getType() == "select" && $field->isAssociated() == true )
                    {
                        $option = $this->getValueAssociated( $field , 'array' );
                        $this->getEntity()->get( $field->getName() )->setData('option',$option);
                    }
                }
            }

            $content = $this->getRepository()->getAllTableIndex( $order , $by , $thArray ) ;

            if ( $content )
            {
                // TD
                $i = 0;
                foreach( $content as $row )
                {
                    foreach( $this->getEntity()->getField() as $field )
                    {
                        if ( $field->getData('index') == true )
                        {
                            if ( $field->getType() == "select" && $field->getData('option') !== NULL )
                            {
                                $opt = $field->getData('option') ;
                                $value = $opt[ $row->get( $field->getColumn() ) ] ;
                            }
                            else if ( $field->getType() == "radio" && $field->getData('isBoolean') == true )
                            {
                                $value = ( $row->get( $field->getColumn() ) == 1 ? "Oui" : "Non" ) ;
                            }
                            else if ( $field->getType() == "date" )
                            {
                                $date = new \DateTime( $row->get( $field->getColumn() ) );
                                $value = $date->format('d/m/Y') ;
                            }
                            else if ( $field->getType() == "image" )
                            {
                                $Media = new \App\Kernel\Back\Media;
                                $Media->setModuleId( $this->getEntityId() ) ;
                                $Media->setImageId( $row->get( $field->getColumn() ) );
                                $Media->getNameById();

                                $img = str_replace( WEB_PATH , '' , IMAGE_PATH ) . '/' . $this->getEntity()->getFolder() . "/" . $Media->getMini( $Media->getImageName() , 't' , 100 , 100 ) ;
                                $value = $this->Factory()->Url()->get( $img , true ) ;
                            }
                            else
                            {
                                $value = '' ;
                                if ( $field->getData('unit') !== NULL && $field->getData('whereUnit') == "before" ) $value.= $field->getData('unit') . ' ' ;
                                $value.= $row->get( $field->getColumn() ) ;
                                if ( $field->getData('unit') !== NULL && $field->getData('whereUnit') == "after" ) $value.= ' ' . $field->getData('unit') ;

                            }

                            $typeArray[ $field->getName() ] = $field->getType() ;
                            $tdArray[ $i ]['td'][ $field->getName() ] = $value ;
                        }
                    }

                    $tdArray[ $i ]['id'] = $row->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ;

                    if ( $this->getEntity()->hasParent() ) 		$tdArray[ $i ][ $this->getEntity()->getParentName() ] = $row->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasOrder() ) 		$tdArray[ $i ]['order'] = $row->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasValidation() ) 	$tdArray[ $i ]['validation'] = $row->get( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() ) ;

                    $i++;
                }
            }
        }

        if ( $this->getEntity()->hasParent() )
        {
            $tdArray = $this->getTreeTableParent( $tdArray ) ;
        }

        $this->setRender( 'right' , $rightArray ) ;
        $this->setRender( 'hasOrder' , $this->getEntity()->hasOrder() ) ;
        $this->setRender( 'hasValidation' , $this->getEntity()->hasValidation() ) ;
        $this->setRender( 'th' , $thArray ) ;
        $this->setRender( 'td' , $tdArray ) ;
        $this->setRender( 'order' , $order ) ;
        $this->setRender( 'by' , $by ) ;
        $this->setRender( 'type' , $typeArray ) ;
        $this->setRender( 'search' , $this->getApp()->request->get('search') == 1 ? 1 : 0 ) ;
    }

    protected function getTreeTableParent( $rows, $parent_id = -1 )
    {
        $tree = [];

        foreach ( $rows as $key => $row )
        {
            if ( $row[ $this->getEntity()->getParentName() ] == $parent_id or ( $row[ $this->getEntity()->getParentName() ] === NULL && $parent_id == -1 ) )
            {
                $array = $row;
                unset( $rows[ $key ]);
                $array['subpages'] = $this->getTreeTableParent($rows, $row['id']);
                $tree[] = $array;
            }
        }

        return $tree;
    }

    /* ************************************************** */
    /* ******************  FUNCTIONS  ******************* */
    /* ************************************************** */

    protected function appendEntityInfo()
    {
        $rst = \DB::for_table('module')
            ->select('module_name')
            ->select('module_id')
            ->where(array('module_class_name' => $this->getEntityName() , 'module_active' => 1))
            ->find_one();

        if ( $this->getOption('noAppend') == false )
        {
            $this->getApp()->view()->appendData(array(
                'mod' => array(
                    'name'  => $this->getEntityName(),
                    'title' => $rst->module_name
                ),
                'action' => $this->getActionName()
            ));
        }

        $this->setEntityId( $rst->module_id ) ;
    }

    protected function checkToken()
    {
        if ( $this->Factory()->Token()->check( $this->getToken() ) == false )
        {
            $this->Factory()->Response()->returnJSON( $this->m("token_is_bad") ) ;
        }
    }

    /* Fonction appelée par "add" & "update" */
    protected function pushData( $add = true )
    {
        if ( $this->checkForm() )
        {
            if ( !empty( $this->getEntity()->getField() ) )
            {
                if ( $this->getId() !== NULL )
                {
                    $content = $this->getRepository()->findOne( $this->getId() );
                    if ( ! $content )
                    {
                        $this->Factory()->Response()->flashAndRedirect( $this->m("have_no_content") ) ;
                    }
                }
                else
                {
                    $content = $this->getRepository()->create();
                }

                foreach( $this->Lang()->getAll() as $lang )
                {
                    if ( $this->getId() !== NULL )
                    {
                        $contentLang[ $lang->url ] = \DB::for_module_lang( $this->getEntityName() , $this->getId() , $lang->id )->find_one();

                        if ( ! $contentLang[ $lang->url ] )
                        {
                            $contentLang[ $lang->url ] = $this->getRepository()->createLang();
                            $contentLang[ $lang->url ]->set( \DB::getLangIdLangName( $this->getEntityName() ) , $lang->id ) ;
                        }
                    }
                    else
                    {
                        $contentLang[ $lang->url ] = $this->getRepository()->createLang();
                        $contentLang[ $lang->url ]->set( \DB::getLangIdLangName( $this->getEntityName() ) , $lang->id ) ;
                    }
                }

                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( ! $row->hasLang() )
                    {
                        if ( $row->isOrder() == true && $add == true )
                        {
                            $content->set( $row->getColumn() , $row->getDefault() ) ;
                        }
                        else if ( $row->getType() != "checkbox" && $row->canUpdate() == true )
                        {
                            $content->set( $row->getColumn() , $row->getValue() ) ;
                        }
                    }
                    else
                    {
                        foreach( $this->Lang()->getAll() as $lang )
                        {
                            $contentLang[ $lang->url ]->set( $row->getColumn() , $row->getValue( $lang->url ) ) ;
                        }
                    }
                }

                $date = new \DateTime();

                if ( $add == true )
                {
                    $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
                    $content->set( $this->getEntity()->get('date_created')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
                }
                else
                {
                    $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $content->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
                }
                $content->set( $this->getEntity()->get('date_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;

                // On ajoute les infos sans multi-langue
                $content->save() ;

                if ( $this->getId() === NULL ) $this->setId( $content->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;

                foreach( $this->getEntity()->getField() as $nameField => $field )
                {
                    if ( $field->getType() == "checkbox" )
                    {
                        $this->getRepository()->pushDataAssoc( $nameField , $field , $this->getId() ) ;
                    }
                    else if ( $field->getType() == "gallery" && $add == true )
                    {
                        // On met a jour les 0
                        $Gallery = new \App\Kernel\Back\Gallery;
                        $Gallery->setElementId( $this->getId() );
                        $Gallery->setField( $field->getName() );
                        $Gallery->setModuleId( $this->getEntityId() );
                        $Gallery->updateZero();
                    }
                }

                if ( $this->getEntity()->hasUrl() && $add == true )
                {
                    if ( $this->getEntity()->hasMultilang() )
                    {
                        $field = $this->getEntity()->build( $this->getEntity()->getUrlName() )->field() ;

                        foreach( $this->Lang()->getAll() as $lang )
                        {
                            $seo = new \App\Kernel\Back\Seo;
                            $seo->setElementId( $this->getId() );
                            $seo->setModuleId( $this->getEntityId() );
                            $seo->setTitle( $field->getValue( $lang->url ) );
                            $seo->setLangId( $lang->id );
                            $seo->save();
                        }
                    }
                    else
                    {
                        $seo = new \App\Kernel\Back\Seo;
                        $seo->setElementId( $this->getId() );
                        $seo->setModuleId( $this->getEntityId() );
                        $seo->setTitle( $this->getEntity()->build( $this->getEntity()->getUrlName() )->field()->getValue() );
                        $seo->setLangId( $this->Lang()->getDefault()->id );
                        $seo->save();
                    }
                }
            }

            // On ajoute les infos avec multi-langue
            foreach( $this->Lang()->getAll() as $lang )
            {
                $contentLang[ $lang->url ]->set( \DB::getIdNameInLang( $this->getEntityName() ) , $this->getId() ) ;
                $contentLang[ $lang->url ]->save() ;
            }

            if ( $this->getApp()->request->post('submit') == "stay" ) 	$url = 'module/' . $this->getEntityName() . '/edit/' . $this->getId() ;
            else 														$url = 'module/' . $this->getEntityName() ;

            if ( $add ) $msg = $this->m("add_success") ;
            else		$msg = $this->m("edit_success") ;

            \App\Kernel\Back\Log::getInstance()->info( ( $add ? 100 : 101 ) , "#" . $this->getId() . " - " . $this->getEntityName() ) ;
            $this->Factory()->Response()->flashAndRedirect( $msg , true , $url ) ;
        }
    }

    // Check si tous les champs sont corrects
    // Appelée dans la fonction "pushData"
    protected function checkForm()
    {
        $this->_return = true ;

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                $rst = $this->getEntity()->build( $row->getName() )->field()->checkEmpty() ;
                if ( $rst == false ) $this->_return = false ;
            }

            if ( $this->_return == true )
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    $this->getEntity()->build( $row->getName() )->field()->getFormatValue() ;
                }
            }
        }

        return $this->_return ;
    }

    /* ************************************************** */
    /* ******************  UP / DOWN  ******************* */
    /* ************************************************** */

    protected function order()
    {
        if ( $this->getEntity()->hasOrder() )
        {
            $table = $this->getApp()->request()->get('table-' . $this->getEntityName() );
            if ( $table )
            {
                $position = 1;
                foreach( $table as $row )
                {
                    $elmt = \DB::find( $this->getEntityName() , $row );
                    $elmt->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $position );
                    $elmt->save();
                    $position++;
                }
            }

            \App\Kernel\Back\Log::getInstance()->info( 105 , $this->getEntityName() ) ;

            return true;
        }
        else
        {
            return false ;
        }
    }

    /* ************************************************** */
    /* *****************  VALIDATION  ******************* */
    /* ************************************************** */

    protected function updatePublication( $value )
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( ! $content ) return false ;

        $date = new \DateTime();
        $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $content->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
        $content->set( $this->getEntity()->get('date_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
        $content->set( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , $value ) ;
        $content->save();

        \App\Kernel\Back\Log::getInstance()->info( ( $value == 0 ? 104 : 103 ) , "#" . $this->getId() . " - " . $this->getEntityName() ) ;

        return true ;
    }

    /* ********************************************************* */
    /* ******************   FETCH / RENDER   ******************* */
    /* ********************************************************* */

    protected function render( $template )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        $View->render( 'module/' . $template , $this->getRender() );
    }

    protected function fetch( $template )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/' . $template , $this->getRender() );
    }

    /* ************************************************** */
    /* ******************   ACTIONS   ******************* */
    /* ************************************************** */

    protected function indexAction()
    {
        $this->generateTable() ;

        if ( $this->getEntity()->hasParent() )  $template = 'table_parent' ;
        else                                    $template = 'table' ;

        $table = $this->fetch( $template . '.twig.html' );
        $this->setRender( 'table' , $table ) ;
        $this->render('index.twig.html');
    }

    protected function tableAction()
    {
        $this->generateTable() ;
        if ( $this->getEntity()->hasParent() )  $template = 'table_parent' ;
        else                                    $template = 'table' ;

        $this->render( $template . '.twig.html');
    }

    protected function addAction()
    {
        if ( $this->getApp()->request->isPost() ) $this->pushData() ;

        $this->generateForm() ;
        $this->render('add.twig.html') ;
    }

    protected function editAction()
    {
        if ( $this->getApp()->request->isPost() ) $this->pushData( false ) ;

        $this->setRender( 'id' , $this->getId() ) ;

        if ( $this->getEntity()->hasUrl() )
        {
            $this->setRender( 'lang' , $this->Lang()->getAll() ) ;
            $this->setRender( 'tabs' , true ) ;
            $this->setRender( 'seo' , $this->getEntity()->hasUrl() ) ;
        }

        $this->generateForm( true ) ;
        $this->render('edit.twig.html') ;
    }

    protected function contentAction()
    {
        if ( $this->getApp()->request->isPost() )
        {
            $Content = new \App\Kernel\Back\Content;
            $Content->setId( $this->getId() ) ;
            $Content->setLangId( $this->getLang() ) ;
            $Content->setModuleId( $this->getEntityId() ) ;
            $Content->save() ;

            if ( $this->getApp()->request->post('submit') == "stay" ) 	$url = 'module/' . $this->getEntityName() . '/content/' . $this->getId() . '/' . $this->getToken() . '/' . $this->getLang() ;
            else 														$url = 'module/' . $this->getEntityName() ;

            $this->Factory()->Response()->flashAndRedirect( $this->m("edit_success") , true , $url ) ;
        }

        $this->setRender( 'id' , $this->getId() ) ;
        $this->setRender( 'lang' , $this->Lang()->getAll() ) ;
        $this->setRender( 'lang_id' , $this->getLang() ) ;
        $this->setRender( 'tabs' , true ) ;
        $this->setRender( 'content' , true ) ;
        $this->setRender( 'seo' , $this->getEntity()->hasUrl() ) ;

        $this->render('content.twig.html') ;
    }

    protected function paragraphlistAction()
    {
        $Content = new \App\Kernel\Back\Content;
        $Content->setId( $this->getId() ) ;
        $Content->setLangId( $this->getLang() ) ;
        $Content->setModuleId( $this->getEntityId() ) ;

        $array = $Content->generic_paragraph_list() ;
        $this->Factory()->Response()->printJSON( $array ) ;
    }

    protected function orderAction()
    {
        $this->checkToken() ;

        if ( $this->order() )	$this->Factory()->Response()->returnJSON( $this->m("order_success") , true ) ;
        else					$this->Factory()->Response()->returnJSON( $this->m("order_failed") ) ;
    }

    protected function enableAction()
    {
        $this->checkToken() ;

        if ( $this->updatePublication(1) )	$this->Factory()->Response()->returnJSON( $this->m("enable_success") , true ) ;
        else								$this->Factory()->Response()->returnJSON( $this->m("enable_failed") ) ;
    }

    protected function disableAction()
    {
        $this->checkToken() ;

        if ( $this->updatePublication(0) )	$this->Factory()->Response()->returnJSON( $this->m("disable_success") , true ) ;
        else								$this->Factory()->Response()->returnJSON( $this->m("disable_failed") ) ;
    }

    protected function deleteAction()
    {
        $this->setToken( $this->getApp()->request->post( $this->getApp()->config('token') ) ) ;
        $this->checkToken() ;
        $this->delete();
    }

    protected function seoAction()
    {
        $seo = new \App\Kernel\Back\Seo;
        $seo->setElementId( $this->getId() );
        $seo->setModuleId( $this->getEntityId() );

        $one = $this->getRepository()->findOne( $this->getId() );


        if ( $this->getApp()->request->isPost() )
        {
            $seo->update() ;
        }

        $this->setRender( 'content' , $seo->getAll() ) ;
        $this->setRender( 'hasParagraph' , $this->getEntity()->hasParagraph() ) ;
        $this->setRender( 'id' , $this->getId() ) ;
        $this->setRender( 'lang' , $this->Lang()->getAll() ) ;
        $this->setRender( 'index' , 0 ) ;
        $this->render('seo.twig.html') ;
    }

    /* ************************************************** */
    /* ******************    MEDIA    ******************* */
    /* ************************************************** */

    protected function uploadAction()
    {
        $Media = new \App\Kernel\Back\Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->upload( UPLOAD_PATH ) ;
    }

    protected function newuploadAction()
    {
        $this->render('media/upload.twig.html') ;
    }

    protected function mediaAction()
    {
        $Media = new \App\Kernel\Back\Media;
        $Media->setModuleId( $this->getEntityId() ) ;

        $images = $Media->getAll() ;
        if ( $images )
        {
            $rqt = \DB::for_module( $this->getEntityName() );
            $fieldImage = $this->getEntity()->getImageField() ;
            foreach( $fieldImage as $field )
            {
                $rqt = $rqt->select( $field );
            }
            $rqt = $rqt->find_many();

            if ( $rqt )
            {
                foreach( $rqt as $row )
                {
                    foreach( $fieldImage as $field )
                    {
                        if ( array_key_exists( $row->get( $field ) , $images ) ) $images[ $row->get( $field ) ]->media_delete = false ;
                    }
                }
            }

            $this->setRender( 'images' , $images ) ;
            $this->setRender( 'path' , $this->getEntity()->getPathImage(false) ) ;
            $this->render('media/index.twig.html') ;
        }
        else
        {
            $this->setRender( 'noImage' , true ) ;
            $this->newuploadAction() ;
        }
    }

    protected function postuploadAction()
    {
        $field = $this->getEntity()->build( $this->getApp()->request->post('field') )->field();
        $result = json_decode( $this->getApp()->request->post('data') ) ;

        if ( $result )
        {
            foreach( $result as $row )
            {
                $this->parsePostMedia( $row->id , $this->getApp()->request->post('field') ) ;
            }
        }
    }

    protected function postclickAction()
    {
        $Media = new \App\Kernel\Back\Media;
        $Media->setImageId( $this->getApp()->request->post('dataid') );
        $Media->getNameById();

        $this->parsePostMedia( $Media->getImageId() , $this->getApp()->request->post('field') , false ) ;
    }

    protected function cropAction()
    {
        $Media = new \App\Kernel\Back\Media;
        $Media->setImageId( $this->getId() );
        $Media->getNameById();
        $this->setRender( 'imageName' , $Media->getImageName() ) ;
        $this->setRender( 'path' , $this->getEntity()->getPathImage(false) ) ;
        $this->render('media/crop.twig.html') ;
    }

    protected function deletemediaAction()
    {
        $this->checkToken() ;

        $Media = new \App\Kernel\Back\Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->setImageId( $this->getId() );
        $Media->setFolder( $this->getEntity()->getFolder() ) ;
        $images = $Media->getAll() ;

        if ( $images )
        {
            if ( array_key_exists( $this->getId() , $images ) )
            {
                if ( $images[ $this->getId() ]->media_delete == true )
                {
                    $rst = $Media->delete();

                    if ( $rst ) $this->Factory()->Response()->returnJSON( $this->m("deletemedia_success") , true ) ;
                    else		$this->Factory()->Response()->returnJSON( $this->m("deletemedia_failed") ) ;
                }
                else
                {
                    $this->Factory()->Response()->returnJSON( $this->m("deletemedia_delete_is_impossible") ) ;
                }
            }
            else
            {
                $this->Factory()->Response()->returnJSON( $this->m("deletemedia_media_not_found") ) ;
            }
        }
        else
        {
            $this->Factory()->Response()->returnJSON( $this->m("deletemedia_no_ressource") ) ;
        }
    }

    protected function cropimageAction()
    {
        $Media = new \App\Kernel\Back\Media;
        $Media->setFolder( $this->getEntity()->getPathImage() ) ;
        $Media->crop() ;

        $this->Factory()->Response()->returnJSON( $this->m("crop_image") , true ) ;
    }

    protected function parsePostMedia( $idFile , $fieldName , $upload = true )
    {
        $field = $this->getEntity()->build( $fieldName )->field();
        $Media = new \App\Kernel\Back\Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->setFolder( $this->getEntity()->getFolder() ) ;
        $Media->setImageId( $idFile ) ;
        $Media->getNameById() ;

        $json = [] ;

        if ( $upload )
        {
            $source = $Media->rename();
        }
        else
        {
            $source = $Media->genThumb( 100 , 100 ) ;
        }

        $json[] = [
            'key' => "source_" . $field->getName(),
            'file' => $this->Factory()->Url()->get( $this->getEntity()->getPathImage( false ) . '/t/' . $source , true )
        ];

        if ( $field->hasThumb() )
        {
            foreach( $field->getThumb() as $thumb )
            {
                // width, height
                $file = $Media->genThumb( $thumb[0] , $thumb[1] ) ;

                $json[] = [
                    'key' => "t_" . $field->getName() . "_" . $thumb[0] . 'x' .  $thumb[1] ,
                    'file' => $this->Factory()->Url()->get( $this->getEntity()->getPathImage( false ) . '/t/' . $file , true )
                ];
            }
        }

        if ( $field->hasCrop() )
        {
            foreach( $field->getCrop() as $crop )
            {
                // width, height
                $file = $Media->genCropDefaut( $crop[0] , $crop[1] ) ;

                $json[] = [
                    'key' => "c_" . $field->getName() . "_" . $crop[0] . 'x' .  $crop[1] ,
                    'file' => $this->Factory()->Url()->get( $this->getEntity()->getPathImage( false ) . '/c/' . $file , true )
                ];
            }
        }

        $this->getApp()->contentType('application/json');
        echo json_encode([
            'id' => [
                'key' => 'id_' . $field->getColumn() ,
                'value' => $Media->getImageId() ,
                'linkCrop' => $this->Factory()->Url()->get( 'module/' . $this->getEntityName() . '/crop/' . $Media->getImageId() )
            ],
            'files' => $json
        ]) ;
    }

    /* ************************************************** */
    /* *****************   DOCUMENT   ******************* */
    /* ************************************************** */

    protected function documentAction()
    {
        $Doc = new \App\Kernel\Back\Document;
        $Doc->setModuleId( $this->getEntityId() ) ;

        $documents = $Doc->getAll() ;
        if ( $documents )
        {
            $rqt = \DB::for_module( $this->getEntityName() );
            $fieldDoc = $this->getEntity()->getDocumentField() ;
            foreach( $fieldDoc as $field )
            {
                $rqt = $rqt->select( $field );
            }
            $rqt = $rqt->find_many();

            if ( $rqt )
            {
                foreach( $rqt as $row )
                {
                    foreach( $fieldDoc as $field )
                    {
                        if ( array_key_exists( $row->get( $field ) , $documents ) ) $documents[ $row->get( $field ) ]->document_delete = false ;
                    }
                }
            }

            $this->setRender( 'documents' , $documents ) ;
            $this->setRender( 'path' , $this->getEntity()->getPathDocument(false) ) ;
            $this->render('document/index.twig.html') ;
        }
        else
        {
            $this->setRender( 'noDocument' , true ) ;
            $this->doc_newuploadAction() ;
        }
    }

    protected function deletedocumentAction()
    {
        $this->checkToken() ;

        $Doc = new \App\Kernel\Back\Document;
        $Doc->setModuleId( $this->getEntityId() ) ;
        $Doc->setDocumentId( $this->getId() );
        $Doc->setFolder( $this->getEntity()->getFolder() ) ;
        $docs = $Doc->getAll() ;

        if ( $docs )
        {
            if ( array_key_exists( $this->getId() , $docs ) )
            {
                if ( $docs[ $this->getId() ]->document_delete == true )
                {
                    $rst = $Doc->delete();

                    if ( $rst ) $this->Factory()->Response()->returnJSON( $this->m("deletedocument_success") , true ) ;
                    else		$this->Factory()->Response()->returnJSON( $this->m("deletedocument_failed") ) ;
                }
                else
                {
                    $this->Factory()->Response()->returnJSON( $this->m("deletedocument_delete_is_impossible") ) ;
                }
            }
            else
            {
                $this->Factory()->Response()->returnJSON( $this->m("deletedocument_media_not_found") ) ;
            }
        }
        else
        {
            $this->Factory()->Response()->returnJSON( $this->m("deletedocument_no_ressource") ) ;
        }
    }

    protected function doc_uploadAction()
    {
        $Doc = new \App\Kernel\Back\Document;
        $Doc->setModuleId( $this->getEntityId() ) ;
        $Doc->upload( UPLOAD_PATH ) ;
    }

    protected function doc_newuploadAction()
    {
        $this->render('document/upload.twig.html') ;
    }

    protected function doc_postuploadAction()
    {
        $field = $this->getEntity()->build( $this->getApp()->request->post('field') )->field();
        $result = json_decode( $this->getApp()->request->post('data') ) ;

        if ( $result )
        {
            foreach( $result as $row )
            {
                $this->parsePostDocument( $row->id , $this->getApp()->request->post('field') ) ;
            }
        }
    }

    protected function doc_postclickAction()
    {
        $Doc = new \App\Kernel\Back\Document;
        $Doc->setDocumentId( $this->getApp()->request->post('dataid') );
        $Doc->getNameById();

        $this->parsePostDocument( $Doc->getDocumentId() , $this->getApp()->request->post('field') , false ) ;
    }

    protected function parsePostDocument( $idFile , $fieldName , $upload = true )
    {
        $field = $this->getEntity()->build( $fieldName )->field();
        $Doc = new \App\Kernel\Back\Document;
        $Doc->setModuleId( $this->getEntityId() ) ;
        $Doc->setFolder( $this->getEntity()->getFolder() ) ;
        $Doc->setDocumentId( $idFile ) ;
        $Doc->getNameById() ;
        $Doc->rename();

        $json[] = [
            'key' => "source_" . $field->getName(),
            'name' => $Doc->getDocumentName(),
            'ico' => $Doc->getIcon( $Doc->getDocumentName() ),
        ];

        $this->getApp()->contentType('application/json');
        echo json_encode([
            'id' => [
                'key' => 'id_' . $field->getColumn() ,
                'value' => $Doc->getDocumentId()
            ],
            'files' => $json
        ]) ;
    }

    /* ************************************************** */
    /* *****************   GALLERY    ******************* */
    /* ************************************************** */

    protected function jgalleryAction()
    {
        $this->render('jgallery/index.twig.html') ;
    }

    protected function jgallery_uploadAction()
    {
        $field = $this->getEntity()->get( $this->getApp()->request->post('field') );

        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setElementId( $this->getApp()->request->post('id') );
        $Gallery->setModuleId( $this->getEntityId() );
        $Gallery->setField( $this->getApp()->request->post('field') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );

        if ( $field->hasThumb() )
        {
            foreach( $field->getThumb() as $thumb )
            {
                // width, height
                $Gallery->setThumb( $thumb[0] , $thumb[1] );
            }
        }

        if ( $field->hasCrop() )
        {
            foreach( $field->getCrop() as $crop )
            {
                // width, height
                $Gallery->setCrop( $crop[0] , $crop[1] );
            }
        }

        $Gallery->add();

        $this->Factory()->Response()->printJSON([
            'file'        => $Gallery->getImageName(),
            'id'          => $Gallery->getImageId(),
            'field'       => $Gallery->getField(),
            'filesize'    => $Gallery->getSize(),
            'mini'        => str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . '/' . $this->getEntity()->getFolder() . '/' . $Gallery->getMini( $Gallery->getImageName() , 100 , 100 ),
            'delete_url'  => \App\Kernel\Http::getInstance()->getUrl() . $this->getApp()->config('admin.url') . '/module/' . $this->getEntityName() . '/jgallery_delete',
            'delete_crop' => \App\Kernel\Http::getInstance()->getUrl() . $this->getApp()->config('admin.url') . '/module/' . $this->getEntityName() . '/jgallery_crop'
        ]) ;
    }

    protected function jgallery_deleteAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setImageId( $this->getApp()->request->post('id') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );
        $Gallery->delete();
    }

    protected function jgallery_orderAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setElementId( $this->getApp()->request->post('id') );
        $Gallery->setField( $this->getApp()->request->post('field') );
        $Gallery->setOrder( $this->getApp()->request->post('order') );
        $Gallery->setModuleId( $this->getEntityId() );
        $Gallery->setFolder( $this->getEntity()->getFolder() );
        $Gallery->order();
    }

    protected function jgallery_cropAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setImageId( $this->getApp()->request->get('id') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );

        $field = $this->getEntity()->get( $this->getApp()->request->get('field') ) ;

        $crop   = $field->getCrop();
        $width  = $crop[0][0];
        $height = $crop[0][1];

        $img = $Gallery->getById() ;
        $img = str_replace( WEB_PATH , '' , $img['source'] );

        $this->setRender('crop_width' , $width ) ;
        $this->setRender('crop_height' , $height ) ;
        $this->setRender('crop_ratio' , ( $width / $height) ) ;
        $this->setRender('image_src' , $img ) ;
        $this->setRender('field' , $field->getName() ) ;
        $this->setRender('image_id' , $this->getApp()->request->get('id') ) ;
        $this->render('jgallery/crop.twig.html') ;
    }

    protected function jgallery_cropostAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setImageId( $this->getApp()->request->post('id') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );
        $Gallery->crop() ;

        $this->Factory()->Response()->returnJSON( $this->m("crop_image_gallery") , true ) ;
    }
}