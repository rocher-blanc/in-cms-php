<?php

namespace App\Kernel\Front;

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

    protected function render( $template )
    {
        $this->checkTemplate( $template ) ;
        return parent::render( $template ) ;
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

    protected function getAssocValue( $field , $id , $level = 0 )
    {
        $content = $this->getRepository()->getAssocValue( $field->getName() , $id );
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
            $rows = $this->Container()->module( $field->getObject() )->getRepository()->findIn( $result );
            if ( $rows )
            {
                $elmts = [] ;

                foreach( $rows as $row )
                {
                    $elmts[] = $this->Container()->module( $field->getObject() )->getController()->parseValue( $row , $level );
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
    /* *****************    ACTION    ******************* */
    /* ************************************************** */

    protected function getoneAction()
    {
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
        $this->render('getone.twig.html');
    }

    protected function getallAction()
    {
        if ( ! DEBUG )
        {
            $result = $this->getRepository()->lastUpdated();
            if ( $result )
            {
                $date = new \DateTime( $result->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
                $this->getApp()->lastModified( intval( $date->format('U') ) );
            }
        }

        $all = $this->getRepository()->findAll();

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
        $this->render('getall.twig.html') ;
    }

    /* ************************************************** */
    /* ***************   PARSE VALUE   ****************** */
    /* ************************************************** */

    protected function getSelectValue( $field , $value , $level = 0 )
    {
        if ( $field->isAssociated() )
        {
            $result = $this->Container()->module( $field->getObject() )->getRepository()->findOne( $value );

            if ( $result )  return $this->Container()->module( $field->getObject() )->getController()->parseValue( $result , $level );
            else            return NULL ;
        }
        else
        {
            return $field->getOption( $value );
        }

        return NULL ;
    }

    public function parseValue( $result , $level = 0 )
    {
        $level++;

        if ( $level > 4 ) return [] ;

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
                    if ( $result->get( $row->getColumn() ) == 0 )
                    {
                        $arrayElement[ $row->getName() ] = [];
                    }
                    else
                    {
                        $Doc = new \App\Kernel\Front\Document;
                        $Doc->setDocumentId( $result->get( $row->getColumn() ) );
                        $Doc->getNameById();

                        $tab = [];

                        $tab['url']  = $this->getApp()->request()->getUrl() . $this->getEntity()->getPathDocument(false) . '/' . $Doc->getDocumentName();
                        $tab['icon'] = $Doc->getIcon( $Doc->getDocumentName() );

                        $arrayElement[ $row->getName() ] = $tab;
                    }
                }
                else if ( $row->getType() == 'checkbox' )
                {
                    $arrayElement[ $row->getName() ] = $this->getAssocValue( $row , $result->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) , $level ) ;
                }
                else if ( $row->getType() == 'select' )
                {
                    $arrayElement[ $row->getName() ] = $this->getSelectValue( $row , $result->get( $row->getColumn() ) , $level ) ;
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
                $arrayElement['url'] = \App\Kernel\Http::getInstance()->getUrl() . '/' ;
                if ( $this->Lang()->count() > 1 )
                {
                    $arrayElement['url'].= \App\Kernel\Lang::getInstance()->getActive()->url . "/" ;
                }
                $arrayElement['url'].= $this->getModuleUrl() . $Seo->getUrl() ;
            }
        }
        $arrayElement['lvl'] = $level ;
        return $arrayElement ;
    }

    public function getComponent( $type , $request , $vars )
    {
        $elmts = [] ;
        switch( $type )
        {
            case "one" :
                $result = $this->getRepository()->requestOne( $request );
                break;
            case "all" :
                $result = $this->getRepository()->requestAll( $request );
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
                            $elmts[ $field->getTwigKey() ] = $parse[ $field->getName() ];
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
                                $elmts[ $i ][ $field->getTwigKey() ] = $parse[ $field->getName() ];
                            }
                        }

                        if ( array_key_exists( 'url' , $parse ) ) $elmts[ $i ]['url'] = $parse['url'];
                        $i++;
                    }
                break;
            }
        }

        return $this->Container()->newClass('App\Kernel\View')->fetch( 'component/' . $this->getComponentName() . ".twig" , array_merge([
            'object' => $elmts
        ], $vars ));
    }

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function generateForm( $value = false )
    {
        $form = parent::generateForm( $value );

        if ( $form === false )
        {
            return false ;
        }

        return array_merge( $form , [
            'form' => $this->renderForm([
                'field' => $form['field'],
                'tabs' => $form['tabs'],
                'route' => \App\Kernel\Http::getInstance()->getUrl(),
                'id' => $form['id'],
                'module' => $this->getEntityName(),
                'keyControl' => md5( $this->getEntityName() . $form['id'] ),
            ])
        ]);
    }

    protected function getDataView()
    {
        return "front" ;
    }

    public function getForm( $type )
    {
        switch( $type )
        {
            case "html" :
                return $this->generateForm( $value = false ) ;
            break;
            case "object" :
                // a faire
            break;
        }
    }

    public function listenForm()
    {
        
    }
}