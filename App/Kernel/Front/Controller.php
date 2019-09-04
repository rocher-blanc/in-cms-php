<?php

namespace App\Kernel\Front;

use App\Kernel\Back\Seo;
use App\Kernel\Exception;
use App\Kernel\Http;
use JasonGrimes\Paginator;
use App\Kernel\Front\Translate;

class Controller extends \App\Kernel\Common\Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_url = [] ;
    protected $_elm = false ;
    protected $_component_name = '' ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function User()
    {
        return User::getInstance() ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setUrl( $var )
    {
        if ( $var[0] === '' )    $this->_url = [] ;
        else                     $this->_url = $var ;
    }

    public function setElement()
    {
        $this->_elm = true ;
    }

    /**
     * @param string $component_name
     */
    public function setComponentName($component_name)
    {
        $this->_component_name = $component_name;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getIdParent()
    {
        return [ $this->_get('parent') ] ;
    }

    public function getRepository()
    {
        return $this->Container()->module( $this->getEntityName() )->getRepository() ;
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return $this->_component_name;
    }

    public function getUrl( $key = NULL )
    {
        if ( $key === NULL ) 	return $this->_url ;
        else                 	return $this->_url[ $key ] ;
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isElement()
    {
        return $this->_elm ;
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

            if ( $this->isValidAction() == true )
            {
                $method = $this->getMethodName() ;
                $this->$method();
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
        $this->getEntity()->removeAction('import');
        $this->getEntity()->removeAction('export');
        $this->getEntity()->removeAction('show');
        $this->getEntity()->removeAction('customization');

        if ( $this->getEntity()->hasUrl() )
        {
            $this->getEntity()->addAction('getall');
            $this->getEntity()->addAction('getone');
        }
    }

    protected function loadId()
    {
        $ct = count( $this->getUrl() ) ;

        if ( $ct == 0 )
        {
            return false ;
        }
        else
        {
            $url = $this->getUrl(0);
        }

		$result = \DB::for_table('seo')
            ->select('seo_element_id')
			->where(['seo_lang_id' => $this->Lang()->getActive()->id, 'seo_module_id' => $this->getEntityId(), 'seo_url' => $url])
            ->find_one();

        if ( $result ) $this->setId( $result->seo_element_id );
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
            ->select('seo_index')
            ->where(['seo_lang_id' => $this->Lang()->getActive()->id, 'seo_module_id' => $this->getEntityId(), 'seo_element_id' => $this->getId() ])
            ->find_one();

        $module = \DB::for_table('module')
            ->select('module_index_elmt')
            ->where(['module_id' => $this->getEntityId() ])
            ->find_one();

        if ( $result )
        {
            $lastTab = $this->getApp()->view()->getData('meta') ;
            $robots = $lastTab['robots'] ;

            if ( ( $module->module_index_elmt == 0 or $result->seo_index == 0 ) && substr( $robots , 0 , 5 ) == 'index' )
            {
                $robots = "no" . $robots ;
            }

            $meta = [
                'url' => \Slim\Slim::getInstance()->request()->getUrl() . '/' . $result->seo_url,
                'title' => $result->seo_title,
                'description' => $result->seo_description,
                'robots' => $robots
            ];

            $meta = array_merge( $lastTab , $meta );
            $this->setRender( 'meta' , $meta );

            $og = [
                'type' => "article",
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description
            ];

            $og = array_merge( $this->getApp()->view()->getData('og') , $og ) ;
            $this->setRender('og',$og);
        }
    }

    protected function loadMetaModule()
    {
        $result = \DB::for_table('module')
            ->select('module.module_index')
            ->select('module_lang.module_lang_url')
            ->select('module_lang.module_lang_title')
            ->select('module_lang.module_lang_description')
            ->left_outer_join('module_lang', [ 'module_lang.module_lang_module_id', '=', 'module.module_id' ])
            ->where(['module_lang.module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang.module_lang_module_id' => $this->getEntityId() ])
            ->find_one();

        if ( $result )
        {
            $lastTab = $this->getApp()->view()->getData('meta') ;
            $robots = $lastTab['robots'] ;

            if ( $result->module_index == 0 && substr( $robots , 0 , 5 ) == 'index' )
            {
                $robots = "no" . $robots ;
            }

            $meta = [
                'url' => \Slim\Slim::getInstance()->request()->getUrl() . '/' . $result->module_lang_url,
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description,
                'robots' => $robots
            ];

            $meta = array_merge($lastTab, $meta);
            $this->setRender('meta',$meta);

            $og = [
                'type' => "article",
                'title' => $result->module_lang_title,
                'description' => $result->module_lang_description
            ];

            $og = array_merge( $this->getApp()->view()->getData('og') , $og ) ;
            $this->setRender('og',$og);
        }
    }

    /* ************************************************** */
    /* *****************     VIEW     ******************* */
    /* ************************************************** */

    protected function render( $template , $force = false )
    {
        $this->checkTemplate( $template ) ;
        return parent::render( $this->getEntityName() . '/' . $template , $force ) ;
    }

    protected function checkTemplate( $template )
    {
        $folder = 'module/' . $this->getEntityName() ;
        if ( ! is_dir( VIEW_PROJECT_PATH . '/' . $folder ) )
        {
            mkdir( VIEW_PROJECT_PATH . '/' . $folder , 0755 );
        }

        $file = $folder . '/' . $template . ".twig" ;
        if ( ! file_exists( VIEW_PROJECT_PATH . '/' . $file ) )
        {
            // On créer le fichier avec un template de base dedans (on met les variables dans le templates + extends layout)
        }
    }

    /* ************************************************** */
    /* *****************   CHECKBOX   ******************* */
    /* ************************************************** */

    protected function getAssocValue( $module , $fieldName , $id , $modAssoc )
    {
        $content = $this->getRepository()->getAssocValue( $fieldName , $id );
        $result  = [] ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $result[] = $row->get('value') ;
            }
        }

        if ( $result )
        {
            $rows = $this->Container()->module( $modAssoc )->getRepository()->findIn( $result );
            if ( $rows )
            {
                $elmts = [] ;

                foreach( $rows as $row )
                {
                    $elmts[] = $this->Container()->module( $modAssoc )->getController()->parseValue( $row );
                }

                return $elmts;
            }
        }
        else
        {
            return [];
        }
    }

    /* ************************************************** */
    /* ****************      URL      ******************* */
    /* ************************************************** */

    public function getSiteMap()
    {
        if ( $this->getEntity()->hasUrl() )
        {
            if ( $this->getRepository()->count() > 0 )
            {
                $fieldImage = [] ;
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->getType() == 'image' )
                    {
                        $fieldImage[] = $row;
                    }
                    else if ( $row->getType() == 'gallery' )
                    {
                        $fieldGallery[] = $row;
                    }
                }

                return [
                    'content' => $this->getRepository()->findSiteMap( $fieldImage , $this->getEntityId() ),
                    'pathImage' => $this->getEntity()->getPathImage(false),
                    'fieldImage' => $fieldImage,
                    'fieldGallery' => $fieldGallery
                ];
            }
        }

        return false;
    }

    /* ************************************************** */
    /* *****************   REQUEST    ******************* */
    /* ************************************************** */

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
	/* *****************  PAGINATION  ******************* */
	/* ************************************************** */

	protected function parsePagination(Paginator $p)
	{
		return [
			'all' => $p->getPages(),
			'url' => [
				'previous' => $p->getPrevUrl(),
				'next'     => $p->getNextUrl(),
			],
			'total' => $p->getTotalItems(),
			'first' => $p->getPages()[0],
			'last'  => end( $p->getPages() )
		];
	}

	/* ************************************************** */
	/* *****************    ACTION    ******************* */
	/* ************************************************** */

    protected function getoneAction()
    {
    	$this->loadId() ;

        $result = $this->getRepository()->findOne( $this->getId() );

        /* Si pas de retour, 404 */
        if ( ! $result ) $this->getApp()->pass();

        /* Date de dernière modification */
        if ( ! DEBUG )
        {
            $date = new \DateTime( $result->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
            $this->getApp()->lastModified( intval( $date->format('U') ) );
        }

        if ( $this->getEntity()->hasUrl() ) $this->loadMeta();

        $this->setRender('element', $this->parseValue( $result ) );
        $this->render('getone.twig');
    }

    protected function getallAction()
    {
		$currentPage = NULL ;

		if ( $this->getEntity()->getPagination() !== NULL )
		{
            $url = $this->getUrl();

            if ( count( $url ) == 1 )	$currentPage = 1 ;
            else						$currentPage = end( $url );
            if ( $currentPage < 1 )		$currentPage = 1;


			$totalItems     = $this->getRepository()->count();
			$itemsPerPage   = $this->getEntity()->getPagination();
			$urlPattern     = $this->Factory()->Url()->module( $this->getEntityId() ) . '/(:num)';
			$paginator      = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

			$this->setRender('pagination', $this->parsePagination( $paginator ) );
		}

		$all = $this->getRepository()->findAll( $currentPage );

        if ( $all )
        {
            $elmts = [] ;
            foreach( $all as $row )
            {
                $elmts[] = $this->parseValue( $row );
            }

			$this->setRender('arrayGetAll', $elmts );
        }
        else
        {
            $this->setRender('arrayGetAll', [] );
        }

        $this->loadMetaModule() ;
        $this->render('getall.twig') ;
    }

    /* ************************************************** */
    /* ***************   PARSE VALUE   ****************** */
    /* ************************************************** */

    protected function getObject( $id , $value , $field , $module = NULL )
    {
        return [
            'id'     => $id,
            'type'   => $field->getType(),
            'name'   => $field->getName(),
            'value'  => $value,
            'module' => ( $module !== NULL ? $module : $field->getObject() ),
            'moduleAssoc' => ( $field->getType() !== 'checkbox' ? NULL : $field->getObject() )
        ];
    }

    protected function getSelectValue( $module , $value )
    {
        if ( ! empty( $module ) )
        {
            $result = $this->Container()->module( $module )->getRepository()->findOne( $value );

            if ( $result )  return $this->Container()->module( $module )->getController()->parseValue( $result );
            else            return NULL ;
        }
        else
        {
            throw new Exception("Module is undefined") ;
        }
    }

    public function subParse( $object )
    {
        switch( $object['type'] )
        {
            case 'hidden' :
            case 'select' :
                return $this->getSelectValue( $object['module'] , $object['value'] ) ;
            break;
            case 'checkbox' :
                return $this->getAssocValue( $object['module'] , $object['name'] , $object['id'] , $object['moduleAssoc'] ) ;
            break;
        }
    }

    public function parseValue( $result )
    {
        if ( $this->getEntity()->hasUrl() ) $this->loadModuleUrl();

        if ( $this->getId() === NULL )
        {
            if ( $result ) $this->setId( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) );
        }

        $arrayElement = [];
        foreach( $this->getEntity()->getField() as $row )
        {
            if ( $result->offsetExists( $this->getEntity()->get( $row->getName() )->getColumn() ) == true or $row->hasOffeset() == false )
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

                        if ( $row->getData('hasAltText') == true )
                        {
                            $Alt = new \App\Kernel\Front\Alt;
                            $Alt->setElementId( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) );
                            $Alt->setModuleId( $this->getEntityId() );
                            $Alt->setFieldName( $row->getName() );
                            $Alt->setLangId( $this->Lang()->getActive()->id );
                            $Alt->getOne();

                            $tab['alt'] = $Alt->getValue();
                        }

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
                else if ( $row->getType() == 'gallery' )
                {
                    $Gal = new \App\Kernel\Front\Gallery;
                    $Gal->setElementId( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) );
                    $Gal->setModuleId( $this->getEntityId() );
                    $Gal->setModuleName( $this->getEntityName() );
                    $Gal->setField( $row->getName() );
                    $Gal->setFolder( $this->getEntity()->getFolder() );
                    $tab = $Gal->getAllByField();

                    $arrayElement[ $row->getName() ] = $tab;
                }
                else if ( $row->getType() == 'document' )
                {
                    if ( $result->get( $row->getColumn() ) == '' )
                    {
                        $arrayElement[ $row->getName() ] = [];
                    }
                    else
                    {
                        $exp = explode(',' , $result->get( $row->getColumn() ) );

                        foreach( $exp as $rep )
                        {
                            $Doc = new \App\Kernel\Front\Document;
                            $Doc->setDocumentId( $rep );
                            $Doc->getNameById();

                            $tab = [];

                            $tab['url']   = $this->getApp()->request()->getUrl() . $this->getEntity()->getPathDocument(false) . '/' . $Doc->getDocumentName();
                            $tab['icon']  = $Doc->getIcon( $Doc->getDocumentName() );
                            $tab['name']  = $Doc->getDocumentName();
                            $tab['title'] = $Doc->getAltText();

                            $arrayElement[ $row->getName() ][] = $tab;
                        }
                    }
                }
				else if ( $row->getName() == $this->getEntity()->getModuleParentIdName() )
				{
					$arrayElement['parent'] = $this->getObject( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) , $result->get( $row->getColumn() ) , $row ) ;
				}
                else if ( $row->getType() == 'checkbox' )
                {
                    $arrayElement[ $row->getName() ] = $this->getObject( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) , $result->get( $row->getColumn() ) , $row , $this->getEntityName() ) ;
                }
                else if ( $row->isAssociated() && $row->getType() == 'select' )
                {
                    $arrayElement[ $row->getName() ] = $this->getObject( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) , $result->get( $row->getColumn() ) , $row , $row->getObject() ) ;
                }
				else if ( ! $row->isAssociated() && $row->getType() == 'select' )
                {
                    $arrayElement[ $row->getName() ] = $row->getOption( $result->get( $row->getColumn() ) );
                }
                else if ( $row->getType() == 'date' )
                {
                    $arrayElement[ $row->getName() ]['source'] = $result->get( $row->getColumn() );

                    if ( $row->hasFormat() )
                    {
                        foreach( $row->getFormat() as $name => $format )
                        {
                            $arrayElement[ $row->getName() ][ $name ] = strftime( $format , strtotime( $result->get( $row->getColumn() ) ) ) ;
                        }
                    }
                }
                else
                {
                    $arrayElement[ $row->getName() ] = $result->get( $row->getColumn() );
                }
            }

            if ( $row->isUrl() == true )
            {
                $Seo = new \App\Kernel\Front\Seo;
                $Seo->setElementId( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;
                $Seo->setModuleId( $this->getEntityId() ) ;
                $arrayElement['url'] = Http::getInstance()->getUrl() . '/' ;
                if ( $this->Lang()->count() > 1 )
                {
                    $arrayElement['url'].= \App\Kernel\Lang::getInstance()->getActive()->url . "/" ;
                }
                $arrayElement['url'].= $this->getModuleUrl() . $Seo->getUrl() ;
            }
        }

        if ( $this->getEntity()->itsDepedency() )
        {
            $module = \DB::for_table('module')
                ->select('module_class_name')
                ->where(['module_id' => $result->get( $this->getEntity()->get( $this->getEntity()->getModuleIdName() )->getColumn() ) ])
                ->find_one();

            $arrayElement['depedency'] = $this->getObject( $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) , $result->get( $this->getEntity()->get( $this->getEntity()->getElementIdName() )->getColumn() ) , $this->getEntity()->get( $this->getEntity()->getElementIdName() ) , $module->module_class_name );
        }

        return $arrayElement ;
    }

    public function parseAll( $rows, $callback = NULL )
    {
        $rst = [];
        if( $rows )
        {
            foreach( $rows as $row )
            {
                $parsed = $this->parseValue( $row );
                if( is_callable( $callback ) )
                {
                    $parsed = $callback($parsed);
                }
                $rst[] = $parsed;
            }
        }
        return $rst;
    }

    /* ***************************************************** */
    /* ******************    MODULE     ******************** */
    /* ***************************************************** */

    public function getElement( $type , $request )
    {
        $elmts 		= [] ;
        $pagination = false ;

        switch( $type )
        {
            case "count" :
                return $this->getRepository()->requestCount( $request );
            break;
            case "one" :
                $result = $this->getRepository()->requestOne( $request );
            break;
            case "all" :
                $currentPage = NULL ;

                if ( $this->getEntity()->getPagination() !== NULL && array_key_exists("limit" , $request ) == false )
                {
                    $url = $this->getUrl();

                    if ( count( $url ) == 1 )	$currentPage = 1 ;
                    else						$currentPage = end( $url );
                    if ( $currentPage < 1 )		$currentPage = 1;

                    if ( ( count( $url ) >= 2 ) && is_numeric( end( $url ) ) )
                    {
                        $len = strlen( '/' . end( $url ) ) * -1 ;
                        $urlPattern = substr( $this->Factory()->Url()->getFullUrl() , 0 , $len ) . '/(:num)';
                    }
                    else
                    {
                        $urlPattern = $this->Factory()->Url()->getFullUrl() . '/page/(:num)';
                    }

                    $totalItems = $this->getRepository()->requestCount( $request );

                    if ( array_key_exists('pagination' , $request ) )	$itemsPerPage = $request['pagination'];
                    else													$itemsPerPage = $this->getEntity()->getPagination();

                    $paginator  = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
                    $pagination = $this->parsePagination( $paginator );
                }

                $result = $this->getRepository()->requestAll( $request , $currentPage );
                break;
        }

        $ct = 0;

        if ( $result )
        {
            switch( $type )
            {
                case "one" :
                    if ( $result )
                    {
                        $elmts = $this->parseValue( $result );
                        $ct = 1;
                    }
                break;
                case "all" :
                    $i = 0;
                    foreach( $result as $row )
                    {
                        $elmts[ $i ] = $this->parseValue( $row );
                        $i++;
                    }

                    $ct = count( $elmts );
                break;
            }
        }

        return [
            'object' => $elmts,
            'totalRows' => $ct,
            'pagination' => $pagination
        ];
    }

    /* ***************************************************** */
    /* ******************   COMPONENT   ******************** */
    /* ***************************************************** */

    public function getElementComponent( $type , $request )
    {
        $elmts 		= [] ;
        $result     = [] ;
        $pagination = false ;

        switch( $type )
        {
            case "one" :
                $result = $this->getRepository()->requestOne( $request );
            break;
            case "all" :
                $currentPage = NULL ;

                if ( $this->getEntity()->getPagination() !== NULL && array_key_exists("limit" , $request ) == false )
                {
                    $url = $this->getUrl();

                    if ( count( $url ) == 1 )	$currentPage = 1 ;
                    else						$currentPage = end( $url );
                    if ( $currentPage < 1 )		$currentPage = 1;

                    if ( ( count( $url ) >= 2 ) && is_numeric( end( $url ) ) )
                    {
                        $len = strlen( '/' . end( $url ) ) * -1 ;
                        $urlPattern = substr( $this->Factory()->Url()->getFullUrl() , 0 , $len ) . '/(:num)';
                    }
                    else
                    {
                        $urlPattern = $this->Factory()->Url()->getFullUrl() . '/page/(:num)';
                    }

                    $totalItems = $this->getRepository()->requestCount( $request );

                    if ( array_key_exists('pagination' , $request ) )	$itemsPerPage = $request['pagination'];
                    else												$itemsPerPage = $this->getEntity()->getPagination();

                    $paginator  = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
                    $pagination = $this->parsePagination( $paginator );
                }

                $result = $this->getRepository()->requestAll( $request , $currentPage );
            break;
        }

        if ( $result )
        {
            switch( $type )
            {
                case "one" :
                    $parse = $this->parseValue( $result );

                    foreach( $this->getEntity()->getField() as $field )
                    {
                        if ( $field->hasTwigKey() )
                        {
                            if ( $field->getName() == $this->getEntity()->getModuleParentIdName() ) $elmts[ $field->getTwigKey() ] = $parse['parent'];
                            else																	$elmts[ $field->getTwigKey() ] = $parse[ $field->getName() ];
                        }
                    }

                    if ( array_key_exists( 'url' , $parse ) ) $elmts['url'] = $parse['url'];
                break;

                case "all" :
                    $i = 0;
                    foreach( $result as $row )
                    {
                        $parse = $this->parseValue( $row );

                        foreach( $this->getEntity()->getField() as $field )
                        {
                            if ( $field->hasTwigKey() )
                            {
                                if ( $field->getName() == $this->getEntity()->getModuleParentIdName() ) $elmts[ $i ][ $field->getTwigKey() ] = $parse['parent'];
                                else																	$elmts[ $i ][ $field->getTwigKey() ] = $parse[ $field->getName() ];
                            }
                        }

                        if ( array_key_exists( 'url' , $parse ) ) $elmts[ $i ]['url'] = $parse['url'];
                        if ( array_key_exists( 'depedency' , $parse ) ) $elmts[ $i ]['depedency'] = $parse['depedency'];
                        $i++;
                    }
                break;
            }
        }

        return [
            'object' => $elmts,
            'pagination' => $pagination
        ];
    }

	public function getComponent( $type , $request , $vars )
	{
		return $this->Container()->newClass('App\Kernel\View')->fetch( 'component/' . $this->getComponentName() . ".twig" , array_merge( $this->getElementComponent( $type , $request ) , $vars ));
	}

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function getTitleField( $field )
    {
        if ( $this->Lang()->count() > 1 )
        {
            return Translate::getInstance()->getText(strtolower( 'field_' . $this->getEntityName() . '_' . $field->getName() ) );
        }
        else
        {
            return $field->getData('title') ;
        }
    }

    protected function generateForm( $value = false , $url = '' , $timer = '' , $data = [] )
    {
        $form = parent::generateForm( $value , $data );

        if ( $form === false )
        {
            return false ;
        }

        return array_merge( $form , [
            'form' => $this->renderForm([
                'field'                 => $form['field'],
                'tabs'                  => $form['tabs'],
                'condition'             => $form['condition'],
                'route'                 => Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl(),
                'id'                    => $form['id'],
                'module'                => $this->getEntityName(),
                'recaptcha'             => $this->getEntity()->reCAPTCHA(),
                'recaptcha_public_key'  => RECAPTCHA_PUBLIC,
                'redirect'              => $url,
                'timer'                 => $timer,
                'keyControl'            => md5( $this->getEntityName() . ( $form['id'] === NULL ? '-1' : $form['id'] ) ),
                'result'                => $this->result_form,
            ])
        ]);
    }

    protected function getDataView()
    {
        return "front" ;
    }

    public function getForm( $id , $url , $timer , $data = [] )
    {
        $this->setId( NULL );
        if ( $id !== NULL )
        {
            $this->setId( $id );
        }

        return $this->generateForm( $id === NULL ? false : true , $url , $timer , $data ) ;
    }

    public function getCustomForm( $id , $url , $timer , $data = [] )
    {
        $this->setId( NULL );
        if ( $id !== NULL )
        {
            $this->setId( $id );
        }

        if ( $this->getEntityName() == MODULE_USER )
        {
            $this->field('user_action')->setData( "front" , false ) ;
        }

        $form = parent::generateForm( $id === NULL ? false : true , $data );
        $View = $this->Container()->newClass('App\Kernel\View');

        $start = $View->fetch( 'module/widget/form/start.twig' , [
            'field'      => $form['field'],
            'condition'  => $form['condition'],
            'route'      => Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl(),
            'id'         => $form['id'],
            'module'     => $this->getEntityName(),
            'redirect'   => $url,
            'keyControl' => md5( $this->getEntityName() . ( $form['id'] === NULL ? '-1' : $form['id'] ) ),
            'result'     => $this->result_form,
        ]);

        $end = $View->fetch( 'module/widget/form/end.twig' );

        $fields = [];

        if ( $form['field'] )
        {
            foreach( $form['field'] as $field )
            {
                $fields[ $field['name'] ] = [
                   'name' => $field['fieldname'],
                   'value' => $field['value'],
                   'label' => $field['title'],
                   'error' => $field['error'],
                   'help' => $field['comment'],
                   'row' => $View->fetch( 'module/widget/form/field.twig', [ 'field' => $field ] ),
                   'widget' => $field['Form_HTML'],
               ];
            }
        }

        return [
            'css' => $form['cdn_css'] . $form['css'],
            'js' => $form['cdn_js'] . $form['js'],
            'start' => $start,
            'end' => $end,
            'field' => $fields,
        ] ;
    }

    public function getFormDelete( $id , $var , $url )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/formDelete.twig' , [
            'route' => Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl(),
            'id' => $id,
            'module' => $this->getEntityName(),
            'redirect' => $url,
            'button_text' => $var['text'],
            'button_class' => $var['class'],
            'form_class' => $var['fclass'],
            'redirect' => $url,
            'keyControl' => md5( $this->getEntityName() . $id ),
            'result' => $this->result_form,
        ] );
    }

    public function listenForm( $add = true )
    {
		if ( $add ) $hookBeforeCheck = 'hookAddCheckBefore' ;
		else        $hookBeforeCheck = 'hookUpdateCheckBefore' ;

        $result = $this->$hookBeforeCheck();

        if ( $result === true )
        {
            $result = [
                'msg' => '',
                'url' => '',
                'result' => false
            ];

            if ( $this->checkForm() )
            {
                $recaptcha = $this->checkReCAPTCHA() ;

                if ( $recaptcha === true )
                {
                    if ( $add ) $hookAfterCheck = 'hookAddCheckAfter' ;
                    else        $hookAfterCheck = 'hookUpdateCheckAfter' ;

                    $resultHook = $this->$hookAfterCheck();

                    if ( $resultHook === true )
                    {
                        if ( ! empty( $this->getEntity()->getField() ) )
                        {
                            if ( $this->getId() !== NULL )
                            {
                                $content = $this->getRepository()->findOne($this->getId());
                                if ( ! $content )
                                {
                                    $result['msg'] = $this->m("have_no_content");
                                }
                            }
                            else
                            {
                                $content = $this->getRepository()->create();
                            }

                            foreach( $this->getEntity()->getField() as $row )
                            {
                                if ( $this->checkCustomField( $row ) == true )
                                {
                                    if ( $row->getType() == "image" && !empty( $_FILES[ "upload_" . $row->getColumn() ]['name'] ) )
                                    {
                                        $Media = new Media;
                                        $Media->setModuleId( $this->getEntityId() ) ;
                                        $Media->setModuleName( $this->getEntityName() ) ;
                                        $Media->setFolder( $this->getEntity()->getFolder() ) ;
                                        $Media->upload( "upload_" . $row->getColumn() , $row->getName() ) ;

                                        $content->set($row->getColumn(), $Media->getImageId() );
                                    }
                                    else if ( $row->getType() == "document" && !empty( $_FILES[ $row->getColumn() ]['name'] ) )
                                    {
                                        $Doc = new Document;
                                        $Doc->setModuleId( $this->getEntityId() ) ;
                                        $Doc->setModuleName( $this->getEntityName() ) ;
                                        $Doc->setFolder( $this->getEntity()->getFolder() ) ;
                                        $Doc->upload( $row->getColumn() ) ;

                                        $content->set($row->getColumn(), $Doc->getDocumentId() );
                                    }
                                    else if ( $row->getType() != "image" && $row->save() == true && ( $row->isOrder() == true or ( $row->getDefault() !== NULL && $row->front() == false ) or ( $row->getDefault() !== NULL && $row->force() == true ) ) && $add == true )
                                    {
                                        $content->set($row->getColumn(), $row->getDefault());
                                    }
                                    else if ( $row->getType() != "image" && $row->save() == true && $row->getType() != "checkbox" && $row->canUpdate() == true && $row->isOrder() == false && $row->front() == true )
                                    {
                                        $content->set($row->getColumn(), $row->getValue());
                                    }
                                }
                            }

                            $date = new \DateTime();

                            if ( $add == true )
                            {
                                $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $date->format('Y-m-d H:i:s') );
                                $content->set( $this->getEntity()->get('date_created')->getColumn() , $date->format('Y-m-d H:i:s') );
                            }
                            else
                            {
                                $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $content->get($this->getEntity()->get('date_updated')->getColumn() ) );
                            }
                            $content->set( $this->getEntity()->get('date_updated')->getColumn() , $date->format('Y-m-d H:i:s') );

                            // On ajoute les infos sans multi-langue
                            $content->save();

                            if ( $this->getId() === NULL ) $this->setId( $content->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) );

                            foreach( $this->getEntity()->getField() as $nameField => $field )
                            {
                                if ( $field->getType() == "checkbox" )
                                {
                                    $this->getRepository()->pushDataAssoc($nameField, $field, $this->getId());
                                }
                                else if ( $field->getType() == "gallery" && $add == true )
                                {
                                    // On met a jour les 0
                                    $Gallery = new \App\Kernel\Back\Gallery;
                                    $Gallery->setElementId($this->getId());
                                    $Gallery->setField($field->getName());
                                    $Gallery->setModuleId($this->getEntityId());
                                    $Gallery->updateZero();
                                }
                            }

                            if ( $this->getEntity()->hasUrl() && $add == true )
                            {
                                $seo = new Seo;
                                $seo->setElementId($this->getId());
                                $seo->setModuleId($this->getEntityId());
                                $seo->setTitle($this->getEntity()->build($this->getEntity()->getUrlName())->field()->getValue());
                                $seo->setLangId($this->Lang()->getDefault()->id);
                                $seo->save();
                            }
                        }

                        if ( $add ) $hookAfterCheck = 'hookAddSaveAfter' ;
                        else        $hookAfterCheck = 'hookUpdateSaveAfter' ;

                        $this->$hookAfterCheck( $content );

                        foreach ($this->getEntity()->getField() as $nameField => $field)
                        {
                            $this->field( $field->getname() )->clearValue();
                        }

                        //$this->Factory()->Response()->flash( $result['msg'] , true );

                        $result['result'] = true;
                        $result['msg']    = ( $add ? $this->getAddSuccessMessage() : $this->getUpdateSuccessMessage() );

                        if ( $this->post('redirect') != '' )
                        {
                            $result['url'] = $this->getUrlRedirect();
                        }

                        if ( $this->post('timer') != '' )
                        {
                            $result['timer'] = $this->post('timer');
                        }
                    }
                    else
                    {
                        $result = $resultHook ;
                    }
                }
                else
                {
                    $result['result'] = false;
                    $result['msg']    = $this->getRecaptchaMessage();
                }
            }
            else
            {
                $tab   = [];
                $first = true;
                if ( ! empty( $this->getEntity()->getField() ) )
                {
                    foreach ( $this->getEntity()->getField() as $row )
                    {
                        if ( $row->getError() != '' )
                        {
                            $tab[] = [
                                'field' => $row->getName(),
                                'error' => $row->getError()
                            ];

                            if ( $first )
                            {
                                $result['msg']   = $row->getError();
                                $result['tab']   = $row->getTab();
                                $result['field'] = $row->getName();
                                $first = false;
                            }
                        }
                    }
                }

                $result['fields'] = $tab;
            }
        }

        $this->result_form = $result;

        return $result ;
    }

    protected function checkReCAPTCHA()
    {
        if ( $this->getEntity()->reCAPTCHA() == true )
        {
            $reCaptcha = new \ReCaptcha\ReCaptcha( RECAPTCHA_SECRET );

            if ( isset( $_POST["g-recaptcha-response"] ) )
            {
                $resp = $reCaptcha->verify( $_POST["g-recaptcha-response"] , $this->CMS()->getIp() );

                if ( ! $resp->isSuccess() )
                {
                    return false ;
                }
                else
                {
                    return true ;
                }
            }
            else
            {
                return false ;
            }
        }
        else
        {
            return true ;
        }
    }

    protected function getAddSuccessMessage()
    {
        return '' ;
    }

    protected function getUpdateSuccessMessage()
    {
        return '' ;
    }

    protected function getRecaptchaMessage()
    {
        return $this->_('error_recaptcha');
    }

    protected function _( $key , $var = [] )
    {
        return \App\Kernel\Front\Translate::getInstance()->getText( $key , $var ) ;
    }

    protected function getUrlRedirect()
    {
        return $this->post('redirect') ;
    }
}