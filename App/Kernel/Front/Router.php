<?php

namespace App\Kernel\Front;

class Router
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    /*
     * @array
     * Contient toute la decoupe de l'URL
     */
    protected $_url = [] ;

    /*
     * @int
     * Offset a partir duquel on doit commencer a decouper l'url
     */
    protected $_offset = 0;

    /*
     * @boolean
     * Indique si une route est trouvée
     */
    protected $_findRoute = false;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function setUrl( $var )
    {
        return $this->_url[] = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function getOffset()
    {
        return $this->_offset ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    public function getUrl( $key = NULL )
    {
        if ( $key === NULL ) return $this->_url ;
        else                 return $this->_url[ $key ] ;
    }

    public function getFullUrl()
    {
        return $this->Factory()->Url()->getFullUrl() ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    protected function cutUrl()
    {
        $this->_url = $this->Factory()->Url()->cutUrl(0) ;
    }

    protected function updateRoute()
    {
        $this->_findRoute = true ;
    }

    protected function routeIsFind()
    {
        return $this->_findRoute ;
    }

    public function load()
    {
        $this->cutUrl() ;

        $urlTab = $this->getUrl() ;

        // languages
        if ( empty( $urlTab ) )
        {
            // On charge la page speciale par défaut
            $this->updateRoute() ;
            $this->displayDefaultPage() ;
        }
        else
        {
            $ct = count( $urlTab ) ;

            if ( ( $ct == 1 && $this->Lang()->count() == 1 ) or ( $ct == 2 && $this->Lang()->count() > 1 ) )
            {
                if ( $ct == 2 ) $this->_offset = 1;

                if ( $this->isLanguage() && $ct == 1 )
                {
                    if ( $this->getUrl(0) == $this->Lang()->getDefault()->url )
                    {
                        $this->getApp()->redirect('test');
                    }

                    $this->updateRoute() ;
                    $this->displayDefaultPage() ;
                }
                else if ( $this->isPage() )
                {
                    // c'est une page special
                    $this->updateRoute() ;
                    $this->displayPage() ;
                }
                else if ( $this->isModuleElementDefault() )
                {
                    // C'est un element du module par defaut
                    $this->updateRoute() ;
                    $this->displayModuleElement() ;
                }
                else if ( $this->isModule() )
                {
                    // C'est un module (getAll ou getOne)
                    $this->updateRoute() ;
                    $this->displayModule() ;
                }
            }
            else
            {
                if ( $this->Lang()->count() > 1 ) $this->_offset = 1;
                if ( $this->isLanguage() && $ct == 1 )
                {
                    if ( $this->getUrl(0) == $this->Lang()->getDefault()->url )
                    {
                        $this->getApp()->redirect('/');
                    }

                    $this->updateRoute() ;
                    $this->displayDefaultPage() ;
                }
                else if ( $this->isModule() )
                {
                    $this->updateRoute() ;
                    $this->displayModule() ;
                }
                else if ( $this->isPage() )
                {
                    // c'est une page special
                    $this->updateRoute() ;
                    $this->displayPage() ;
                }
            }
        }

        if ( $this->routeIsFind() == false )
        {
            // on insere les controllers speciales
            $controllers = glob( CONTROLLERS_PATH . '/*.php');
            if ($controllers && count($controllers) > 0)
            {
                $app = $this->getApp();
                foreach ($controllers as $controller)
                {
                    require $controller;
                }
            }
        }

        // Sinon, erreur 404
        /*

        $app->notFound(function () use($app) {
            $app->render('errors/404.twig.html') ;
        });

        */
    }

    /* ************************************************** */
    /* *****************      IS      ******************* */
    /* ************************************************** */

    protected function isLanguage()
    {
        if ( strlen( $this->getUrl(0) ) <= 3 )
        {
            foreach( $this->Lang()->getAll() as $lang )
            {
                if ( $lang->url == $this->getUrl(0) )
                {
                    $this->Lang()->setActive( $lang ) ;
                    return true ;
                }
            }

            return false ;
        }
        else
        {
            return false ;
        }
    }

    protected function isPage()
    {
        if ( $this->Lang()->count() > 1 ) $this->isLanguage() ;

        $ct = \DB::for_table('page')
            ->left_outer_join('page_lang', array('page.page_id', '=', 'page_lang.page_lang_page_id'))
            ->where(['page_lang.page_lang_lang_id' => $this->Lang()->getActive()->id, 'page_lang.page_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('page.page_active', 1)
            ->count();

        if ( $ct == 1 ) return true ;
        else            return false ;
    }

    protected function isModule()
    {
        if ( $this->Lang()->count() > 1 ) $this->isLanguage() ;

        $ct = \DB::for_table('module')
            ->left_outer_join('module_lang', array('module.module_id', '=', 'module_lang.module_lang_module_id'))
            ->where(['module_lang.module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang.module_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('module.module_active', 1)
            ->count();

        if ( $ct == 1 ) return true ;
        else            return false ;
    }

    protected function isModuleElementDefault()
    {
        if ( $this->Lang()->count() > 1 ) $this->isLanguage() ;

        $ct = \DB::for_table('module')
            ->left_outer_join('seo', array('seo.seo_module_id', '=', 'module.module_id'))
            ->where(['seo.seo_lang_id' => $this->Lang()->getActive()->id, 'module.module_default' => 1, 'module.module_active' => 1, 'seo.seo_url' => $this->getUrl( $this->getOffset() )])
            ->count();

        if ( $ct == 1 ) return true ;
        else            return false ;
    }

    /* ************************************************** */
    /* *****************    MODULE    ******************* */
    /* ************************************************** */

    protected function loadController( $class , $element = false , $mp = false )
    {
        $urlTab = $this->getUrl() ;
        $replaceString = '' ;
        $fullUrl = $this->Factory()->Url()->getFullUrl() ;
        if ( $this->Lang()->count() > 1 ) $replaceString.= $this->getUrl(0) . '/' ;
        if ( ! $mp ) $replaceString.= $this->getUrl( ( $this->Lang()->count() > 1 ? 1 : 0 ) ) ;
        $url = ltrim( str_replace( "/" . $replaceString . "/" , '' , $fullUrl ) , '/') ;

        if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $class ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Front\\" . ucfirst( $class );
        else																			$ControllerClass = '\App\Kernel\Front\Controller' ;

        $app = $this->getApp() ;
        $app->map(':page+', function ( $page = [] ) use ( $ControllerClass , $class , $url , $element )
        {
            $Controller = new $ControllerClass;
            $Controller->setEntityName( $class );
            $Controller->setUrl( explode('/',$url) );
            if ( $element ) $Controller->setElement();
            $Controller->execute();
        })->via('GET', 'POST');
    }

    /* ************************************************** */
    /* *****************   DISPLAY    ******************* */
    /* ************************************************** */

    protected function displayModuleElement( $mp = true )
    {
        if ( $mp )
        {
            $result = \DB::for_table('module')
                ->select('module_class_name')
                ->where(['module_default' => 1, 'module_active' => 1])
                ->find_one();
        }
        else
        {
            $result = \DB::for_table('module')
                ->select('module.module_class_name')
                ->left_outer_join('seo', array('seo.seo_module_id', '=', 'module.module_id'))
                ->where(['seo.seo_lang_id' => $this->Lang()->getActive()->id, 'module.module_default' => 0, 'module.module_active' => 1, 'seo.seo_url' => $this->getUrl( $this->getOffset() )])
                ->find_one();
        }

        $this->loadController( $result->module_class_name , true , $mp ) ;
    }

    protected function displayModule()
    {
        $result = \DB::for_table('module')
            ->select('module.module_class_name')
            ->left_outer_join('module_lang', array('module.module_id', '=', 'module_lang.module_lang_module_id'))
            ->where(['module_lang.module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang.module_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('module.module_active', 1)
            ->find_one();

        $urlTab = $this->getUrl() ;
        $ct     = count( $urlTab ) ;

        if ( $ct == 1 && $result )
        {
            $element = false ;
        }
        else
        {
            $element = ( $ct == $this->getOffset() ? false : true ) ;
        }

        $this->loadController( $result->module_class_name , $element ) ;
    }

    protected function displayDefaultPage()
    {
        $page = \DB::for_table('page')
            ->select('page_controller')
            ->select('page_id')
            ->where_equal('page_active', 1)
            ->where_equal('page_default', 1)
            ->find_one();

        if ( $page )
        {
            $app = $this->getApp() ;
            if ( file_exists( CONTROLLER_PROJECT_PATH . '/Page' . $page->page_id . ".php" ) )
            {
                // require CONTROLLER_PROJECT_PATH . '/' . $page->page_controller ;
				$app->get('/(:lang)', function ( $lang = NULL ) use ( $page )
                {
                    $ControllerClass = '\Project\Controller\Front\Page' . $page->page_id ;

					$pageClass = new $ControllerClass;
					$pageClass->setId( $page->page_id );
					$pageClass->execute();
					
                })->via('GET', 'POST');
            }
            else
            {
                $app->pass() ;
            }
        }
    }

    protected function displayPage()
    {
        $page = \DB::for_table('page')
            ->select('page.page_id')
            ->select('page.page_controller')
            ->left_outer_join('page_lang', array('page.page_id', '=', 'page_lang.page_lang_page_id'))
            ->where(['page_lang.page_lang_lang_id' => $this->Lang()->getActive()->id, 'page_lang.page_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('page.page_active', 1)
            ->where_not_equal('page.page_default', 1)
            ->find_one();

        if ( $page )
        {
            $app = $this->getApp() ;
            if ( file_exists( CONTROLLER_PROJECT_PATH . '/Page' . $page->page_id . ".php" ) )
            {
				// require CONTROLLER_PROJECT_PATH . '/' . $page->page_controller ;
				$app->map('/' . ( $this->Lang()->count() > 1 ? ':lang/' : '' ) . $this->getUrl( $this->getOffset() ) . '(/:params+)', function ($params = NULL) use ( $page )
                {
                    $ControllerClass = '\Project\Controller\Front\Page' . $page->page_id ;
					
					$pageClass = new $ControllerClass;
					$pageClass->setId( $page->page_id );
					$pageClass->execute();
					
                })->via('GET', 'POST');
            }
            else
            {
                $app->pass() ;
            }
        }
    }
}