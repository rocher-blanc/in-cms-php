<?php

namespace App\Kernel\Front;

class Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_url = [] ;
    protected $_url_module = "" ;
    protected $_elm = false ;
    protected $_id = NULL ;
    protected $_entity = NULL ;
    protected $_entity_id = NULL ;
    protected $_entity_name = '' ;
    protected $_action_name = '' ;
    protected $_var = [];
    protected $_limit_get_all = 10;
    protected $_main = false;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
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

    private function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function setId( $var )
    {
        $this->_id = $var ;
    }

    public function setUrl( $var )
    {
        if ( $var[0] === '' )    $this->_url = [] ;
        else                     $this->_url = $var ;
    }

    public function setModuleUrl( $var )
    {
        $this->_url_module = $var ;
    }

    public function setEntityName( $var )
    {
        $this->_entity_name = ucfirst( $var ) ;
    }

    protected function setEntity( $var )
    {
        $this->_entity = $var ;
    }

    protected function setEntityId( $var )
    {
        $this->_entity_id = $var ;
    }

    protected function setActionName( $var )
    {
        $this->_action_name = $var ;
    }

    public function setElement()
    {
        $this->_elm = true ;
    }

    public function setMain()
    {
        $this->_main = true ;
    }

    protected function setVar( $key , $elt )
    {
        $this->_var[ $key ] = $elt ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getVar()
    {
        return $this->_var ;
    }

    public function getEntity()
    {
        return $this->_entity ;
    }

    public function getId()
    {
        return $this->_id ;
    }

    public function getEntityName()
    {
        return $this->_entity_name ;
    }

    protected function getEntityId()
    {
        return $this->_entity_id ;
    }

    public function getUrl( $key = NULL )
    {
        if ( $key === NULL ) 	return $this->_url ;
        else                 	return $this->_url[ $key ] ;
    }

    public function getModuleUrl()
    {
        return $this->_url_module . '/' ;
    }

    protected function getMethodName()
    {
        return $this->_action_name . 'Action' ;
    }

    public function getActionName()
    {
        return $this->_action_name ;
    }

    public function getLimitGetAll()
    {
        return $this->_limit_get_all ;
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isElement()
    {
        return $this->_elm ;
    }

    protected function isMain()
    {
        return $this->_main ;
    }

    /* ************************************************** */
    /* *****************     INIT     ******************* */
    /* ************************************************** */

    public function execute()
    {
        if ( $this->loadEntity() == true )
        {
            $this->init() ;
            $ct = count( $this->getUrl() ) ;

            if ( $ct == 0 )
            {
                $this->setActionName('getall');
            }
            if ( $ct > 0 && $this->isElement() == true )
            {
                $this->setActionName('getone');
            }
            else
            {
                $this->setActionName('getall');
            }

            if ( $this->getEntity()->hasAction( $this->getActionName() ) )
            {
                if ( method_exists( $this , $this->getMethodName() ) == true )
                {
                    $method = $this->getMethodName() ;
                    $this->$method();
                }
                else
                {
                    return $this->Factory()->Response()->error("La méthode '" . $this->getMethodName() . "' n'est pas disponible dans le controller") ;
                }
            }
            else
            {
                return $this->Factory()->Response()->error("Aucune action possible ...") ;
            }
        }
        else
        {
            return $this->Factory()->Response()->error("Impossible de charger l'entity") ;
        }
    }

    public function init()
    {
        $this->getEntity()->removeAction('index');
        $this->getEntity()->removeAction('table');
        $this->getEntity()->removeAction('add');
        $this->getEntity()->removeAction('edit');
        $this->getEntity()->removeAction('delete');

        if ( $this->getEntity()->hasUrl() )
        {
            $this->getEntity()->addAction('getall');
            $this->getEntity()->addAction('getone');
        }
    }

    public function loadEntity()
    {
        if ( !is_null( $this->getEntity() ) && is_object( $this->getEntity() ) )
        {
            return true ;
        }

        $result = \DB::for_table('module')
            ->select('module_id')
            ->select('module_default')
            ->where(array('module_class_name' => $this->getEntityName() , 'module_active' => 1))
            ->find_one();

        if ( ! $result ) return false ;

        if ( $result->module_default == 1 ) $this->setMain();

        if ( ! file_exists( ENTITY_PATH . '/' . $this->getEntityName() . '.php' ) )
        {
            $this->Factory()->Response()->error("Le fichier '" . $this->getEntityName() . "' n'éxiste pas");
            return false ;
        }

        $name = "\Project\Module\Entity\\" . ucfirst( $this->getEntityName() ) ;
        $this->setEntity( new $name ) ;
        $this->setEntityId( $result->module_id ) ;
        $this->loadId();

        if ( !is_object( $this->getEntity() ) ) return false ;
        else							 		return true ;
    }

    protected function loadId()
    {
        $ct = count( $this->getUrl() ) ;

        if ( $ct == 0 )
        {
            return false ;
        }
        else if ( $ct <= 1 )
        {
            $url = $this->getUrl(0);
        }
        else
        {
            $url = $this->getUrl( ($ct - 1) );
        }

        $result = \DB::for_table('seo')
            ->select('seo_element_id')
            ->where(['seo_lang_id' => $this->Lang()->getActive()->id, 'seo_module_id' => $this->getEntityId(), 'seo_url' => $url])
            ->find_one();

        if ( $result ) $this->setId( $result->seo_element_id );
    }

    protected function loadModuleUrl()
    {
        if ( ! $this->isMain() )
        {
            $row = \DB::for_table('module_lang')
                ->select('module_lang_url')
                ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $this->getEntityId()])
                ->find_one();

            $this->setModuleUrl( $row->module_lang_url ) ;
        }
    }

    /* ************************************************** */
    /* *****************     META     ******************* */
    /* ************************************************** */

    protected function loadMeta()
    {
        $result = \DB::for_table('seo')
            ->select('seo_url')
            ->select('seo_title')
            ->select('seo_description')
            ->select('seo_keyword')
            ->where(['seo_lang_id' => $this->Lang()->getActive()->id, 'seo_module_id' => $this->getEntityId(), 'seo_element_id' => $this->getId() ])
            ->find_one();

        if ( $result )
        {
            $meta = [
                'url' => \Slim\Slim::getInstance()->request()->getUrl() . '/' . $result->seo_url,
                'title' => $result->seo_title,
                'description' => $result->seo_description,
                'keyword' => $result->seo_keyword
            ];
            $meta = array_merge($this->getApp()->view()->getData('meta'), $meta);
            $this->setVar('meta',$meta);

            $og = [
                'type' => "article",
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description
            ];

            $og = array_merge( $this->getApp()->view()->getData('og') , $og ) ;
            $this->setVar('og',$og);
        }
    }

    protected function loadMetaModule()
    {
        $result = \DB::for_table('module_lang')
            ->select('module_lang_url')
            ->select('module_lang_title')
            ->select('module_lang_description')
            ->select('module_lang_keyword')
            ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $this->getEntityId() ])
            ->find_one();

        if ( $result )
        {
            $meta = [
                'url' => \Slim\Slim::getInstance()->request()->getUrl() . '/' . $result->module_lang_url,
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description,
                'keyword' => $result->module_lang_keyword
            ];
            $meta = array_merge($this->getApp()->view()->getData('meta'), $meta);
            $this->setVar('meta',$meta);

            $og = [
                'type' => "article",
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description
            ];

            $og = array_merge( $this->getApp()->view()->getData('og') , $og ) ;
            $this->setVar('og',$og);
        }
    }

    /* ************************************************** */
    /* *****************     VIEW     ******************* */
    /* ************************************************** */

    protected function render( $template )
    {
        $this->checkTemplate( $template ) ;
        $this->getApp()->render( 'module/' . $this->getEntityName() . '/' . $template . ".twig.html" , $this->getVar() );
    }

    protected function checkTemplate( $template )
    {
        $folder = 'module/' . $this->getEntityName() ;
        if ( ! is_dir( VIEW_PROJECT_PATH . '/' . $folder ) )
        {
            mkdir( VIEW_PROJECT_PATH . '/' . $folder , 0755 );
        }

        $file = $folder . '/' . $template . ".twig.html" ;
        if ( ! file_exists( VIEW_PROJECT_PATH . '/' . $file ) )
        {
            // On créer le fichier avec un template de base dedans (on met les variables dans le templates + extends layout)
        }
    }

    /* ************************************************** */
    /* *****************   CHECKBOX   ******************* */
    /* ************************************************** */

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
    /* ****************      URL      ******************* */
    /* ************************************************** */

    public function getSiteMap()
    {
        if ( $this->getEntity()->hasUrl() )
        {
            if ( $this->count() > 0 )
            {
                $langArray = [];

                foreach( $this->Lang()->getAll() as $lang )
                {
                    $langArray[] = $lang->id ;
                }

                $table 	= \DB::getTableName( $this->getEntityName() ) ;
                $idName = \DB::getIdName( $this->getEntityName() ) ;
                $return = \DB::for_module( $this->getEntityName() )
                    ->select( $table . '.' . $this->getEntity()->get('date_updated')->getColumn() , 'date_updated' )
                    ->select('seo.seo_url')
                    ->select('seo.seo_lang_id')
                    ->left_outer_join('seo', [ $table . '.' . $idName, '=', 'seo.seo_element_id' ])
                    ->where(['seo.seo_module_id' => $this->getEntityId()])
                    ->where_in('seo_lang_id', $langArray );

                if ( $this->getEntity()->hasValidation() )
                {
                    $return = $return->where_equal( $table . '.' . $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );
                }

                $fieldImage = [] ;
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->getType() == 'image' )
                    {
                        $fieldImage[] = $row->getColumn();
                        $return = $return->select( $table . '.' . $row->getColumn() );
                    }
                }

                $content = $return->find_many();

                return [
                    'content' => $content,
                    'pathImage' => $this->getEntity()->getPathImage(false),
                    'fieldImage' => $fieldImage
                ];
            }
        }

        return false;
    }

    protected function count()
    {
        return \DB::for_module( $this->getEntityName() )->count();
    }

    /* ************************************************** */
    /* *****************   REQUEST    ******************* */
    /* ************************************************** */

    protected function getoneRequest()
    {
        $table = \DB::getTableName( $this->getEntityName() ) ;

        /* Requete pour aller chercher les données */
        $result = \DB::for_module( $this->getEntityName() )->where_id_is( $this->getId() );
        if ( $this->getEntity()->hasValidation() ) $result = $result->where_equal( $table . "." . $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );
        if ( $this->getEntity()->hasMultiLang() )
        {
            $tableLang  	= \DB::getTableNameLang( $this->getEntityName() ) ;
            $idName			= \DB::getIdName( $this->getEntityName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getEntityName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getEntityName() ) ;

            $result = $result->left_outer_join( $tableLang , [ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ] )
                ->where_equal( $tableLang . '.' . $langIdLangName , $this->Lang()->getActive()->id );
        }

        return $result->find_one();
    }

    protected function getallRequest()
    {
        $table = \DB::getTableName( $this->getEntityName() ) ;

        $all = \DB::for_module( $this->getEntityName() )->limit( $this->getLimitGetAll() );

        if ( $this->getEntity()->hasValidation() ) 	$all = $all->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $tableLang  	= \DB::getTableNameLang( $this->getEntityName() ) ;
            $idName			= \DB::getIdName( $this->getEntityName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getEntityName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getEntityName() ) ;

            $all = $all->left_outer_join( $tableLang , [ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ] )
                ->where_equal( $tableLang . '.' . $langIdLangName , $this->Lang()->getActive()->id );
        }

        if ( $this->getEntity()->hasOrder() ) 	$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
        else									$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

        return $all->find_many();
    }

    protected function getKitRequest()
    {
        $table = \DB::getTableName( $this->getEntityName() ) ;

        $all = \DB::for_module( $this->getEntityName() )->limit( $this->getLimitGetAll() );

        if ( $this->getEntity()->hasValidation() ) 	$all = $all->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $tableLang  	= \DB::getTableNameLang( $this->getEntityName() ) ;
            $idName			= \DB::getIdName( $this->getEntityName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getEntityName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getEntityName() ) ;

            $all = $all->left_outer_join( $tableLang , [ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ] )
                ->where_equal( $tableLang . '.' . $langIdLangName , $this->Lang()->getActive()->id );
        }

        if ( $this->getEntity()->hasOrder() ) 	$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
        else									$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

        return $all;
    }

    protected function getParseRequest( $request )
    {
        if ( is_object( $request ) )
        {
            $rst = $request->find_many() ;

            if ( $rst )
            {
                $elmts = [] ;

                foreach( $rst as $row )
                {
                    $elmts[] = $this->parseValue( $row );
                }

                return $elmts;
            }
        }
        else
        {
            \App\Kernel\Factory::getInstance()->Response()->error("Une erreur est survenue lors du chargement de la page");
        }
    }

    /* ************************************************** */
    /* *****************    ACTION    ******************* */
    /* ************************************************** */

    protected function getoneAction()
    {
        $result = $this->getoneRequest();

        /* Si pas de retour, 404 */
        if ( ! $result ) $this->getApp()->pass();

        /* Date de dernière modification */
        $date = new \DateTime( $result->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
        $this->getApp()->lastModified( intval( $date->format('U') ) );

        if ( $this->getEntity()->hasUrl() ) $this->loadMeta();

        $this->setVar('element', $this->parseValue( $result ) );
        $this->render('getone');
    }

    protected function getallAction()
    {
        $result = \DB::for_module( $this->getEntityName() )
            ->select( $this->getEntity()->get('date_updated')->getColumn() )
            ->limit(1);

        if ( $this->getEntity()->hasValidation() ) $result = $result->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );

        if ( $this->getEntity()->hasOrder() ) 	$result = $result->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
        else									$result = $result->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() );

        $result = $result->find_one();

        if ( $result )
        {
            $date = new \DateTime( $result->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
            $this->getApp()->lastModified( intval( $date->format('U') ) );

            $all = $this->getallRequest();

            $elmts = [] ;
            foreach( $all as $row )
            {
                $elmts[] = $this->parseValue( $row );
            }

            $this->setVar('arrayGetAll', $elmts );
        }
        else
        {
            $this->setVar('arrayGetAll', [] );
        }

        $this->loadMetaModule() ;
        $this->render('getall') ;
    }

    /* ************************************************** */
    /* ***************   PARSE VALUE   ****************** */
    /* ************************************************** */

    protected function parseValue( $result )
    {
        if ( $this->getEntity()->hasUrl() ) $this->loadModuleUrl();

        $arrayElement = [];
        foreach( $this->getEntity()->getField() as $row )
        {
            if ( $row->getType() == 'image' )
            {
                if ( $result->get( $row->getColumn() ) == 0 )
                {
                    $arrayElement[ $row->getName() ] = [];
                }
                else
                {
                    $media = new \App\Kernel\Front\Media;
                    $media->setImageId( $result->get( $row->getColumn() ) );
                    $media->getNameById();

                    $tab = [];

                    $tab['source'] = $this->getApp()->request()->getUrl() . $this->getEntity()->getPathImage(false) . '/' . $media->getImageName();

                    if ( $row->hasThumb() )
                    {
                        foreach( $row->getThumb() as $thumb )
                        {
                            $mini = $media->getMini( $media->getImageName() , 't' , $thumb[0] , $thumb[1] ) ;
                            if ( $mini !== false )
                            {
                                $img  = $this->getEntity()->getPathImage(false) . '/' . $mini ;
                                $mini = $this->getApp()->request()->getUrl() . $img ;
                            }

                            $tab['thumb'][$thumb[0].'x'.$thumb[1]] = $mini ;
                        }
                    }

                    if ( $row->hasCrop() )
                    {
                        foreach( $row->getCrop() as $crop )
                        {
                            $mini = $media->getMini( $media->getImageName() , 'c' , $crop[0] , $crop[1] ) ;
                            if ( $mini !== false )
                            {
                                $img  = $this->getEntity()->getPathImage(false) . '/' . $mini ;
                                $mini = $this->getApp()->request()->getUrl() . $img ;
                            }

                            $tab['crop'][$crop[0].'x'.$crop[1]] = $mini ;
                        }
                    }

                    $arrayElement[ $row->getName() ] = $tab;
                }
            }
            else if ( $row->getType() == 'checkbox' )
            {
                $arrayElement[ $row->getName() ] = $this->getAssocValue( $row->getName() ) ;
            }
            else if ( $row->getType() == 'date' )
            {
                if ( $row->hasFormat() )
                {
                    $arrayElement[ $row->getName() ]['source'] = $result->get( $row->getColumn() );

                    foreach( $row->getFormat() as $name => $format )
                    {
                        $arrayElement[ $row->getName() ][ $name ] = strftime( $format , strtotime( $result->get( $row->getColumn() ) ) ) ;
                    }
                }
                else
                {
                    $arrayElement[ $row->getName() ] = $this->getAssocValue( $row->getName() ) ;
                }
            }
            else
            {
                $arrayElement[ $row->getName() ] = $result->get( $row->getColumn() );
            }

            if ( $row->isUrl() == true )
            {
                $Seo = new \App\Kernel\Front\Seo;
                $Seo->setElementId( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;
                $Seo->setModuleId( $this->getEntityId() ) ;
                $arrayElement['url'] = \App\Kernel\Http::getInstance()->getUrl() . '/' ;
                if ( \App\Kernel\Lang::getInstance()->count() > 1 )
                {
                    $arrayElement['url'].= \App\Kernel\Lang::getInstance()->getActive()->url . "/" ;
                }
                $arrayElement['url'].= $this->getModuleUrl() . $Seo->getUrl() ;
            }
        }

        return $arrayElement ;
    }
}