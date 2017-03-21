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

    protected function User()
    {
        return \App\Kernel\Front\User::getInstance() ;
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
                        $this->getApp()->redirect('/');
                    }

                    $this->updateRoute() ;
                    $this->displayDefaultPage() ;
                }
                else if ( $this->isWebservice() )
                {
                    // c'est un webservice
                    $this->updateRoute() ;
                    $this->displayWebservice() ;
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
        if ( file_exists( TEMPLATES_PATH . '/errors/404.twig.html' ) )
        {
            $app = $this->getApp() ;
            $app->notFound(function () use ($app) {
                $app->render('errors/404.twig.html') ;
            });
        }
    }

    /* ************************************************** */
    /* *****************     DOMAIN   ******************* */
    /* ************************************************** */

    protected function hasMultiDomain()
    {
        $ct = \DB::for_table('domain')->count();

        if ( $ct < 2 )  return false ;
        else            return true ;
    }

    protected function getDomain()
    {
        return $_SERVER['SERVER_NAME'] ;
    }

    protected function getDomainId()
    {
        $rst = \DB::for_table('domain')
            ->where_equal('domain_name' , $this->getDomain() )
            ->find_one();

        if ( $rst )
        {
            return $rst->domain_id ;
        }

        return NULL ;
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

    protected function isWebservice()
    {
        if ( $this->getUrl(0) == 'api' ) return true ;
        else                             return false ;
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

    protected function loadController( $class , $id_module , $element = false , $mp = false )
    {
        $urlTab = $this->getUrl() ;
        $replaceString = '' ;
        $fullUrl = $this->Factory()->Url()->getFullUrl() ;
        if ( $this->Lang()->count() > 1 ) $replaceString.= $this->getUrl(0) . '/' ;
        if ( ! $mp ) $replaceString.= $this->getUrl( ( $this->Lang()->count() > 1 ? 1 : 0 ) ) ;
        $url = ltrim( str_replace( "/" . $replaceString . "/" , '' , $fullUrl ) , '/') ;

        if ( ! $element )
        {
            $this->urlModule( $id_module ) ;
        }
        else
        {
            $this->urlElementModule( $mp , $url , $id_module );
        }

        $app = $this->getApp() ;
        $app->map(':page+', function ( $page = [] ) use ( $class , $url , $element )
        {
            $Controller = \App\Kernel\Container::getInstance()->module( $class )->getController();
            $Controller->setUrl( explode('/',$url) );
            if ( $element ) $Controller->setElement();
            $Controller->execute();
        })->via('GET', 'POST');
    }

    /* ************************************************** */
    /* *****************   DISPLAY    ******************* */
    /* ************************************************** */

    protected function displayWebservice()
    {
        $result = \DB::for_table('module')
            ->select('module_class_name')
            ->where(['module_class_name' => $this->getUrl(1), 'module_active' => 1])
            ->find_one();

        if ( $result )
        {

        }
        else
        {

        }
    }

    protected function displayModuleElement( $mp = true )
    {
        if ( $mp )
        {
            $result = \DB::for_table('module')
                ->select('module_class_name')
                ->select('module_id')
                ->where(['module_default' => 1, 'module_active' => 1])
                ->find_one();
        }
        else
        {
            $result = \DB::for_table('module')
                ->select('module.module_class_name')
                ->select('module.module_id')
                ->left_outer_join('seo', array('seo.seo_module_id', '=', 'module.module_id'))
                ->where(['seo.seo_lang_id' => $this->Lang()->getActive()->id, 'module.module_default' => 0, 'module.module_active' => 1, 'seo.seo_url' => $this->getUrl( $this->getOffset() )])
                ->find_one();
        }
  
        $this->loadController( $result->module_class_name , $result->module_id , true , $mp ) ;
    }

    protected function displayModule()
    {
        $result = \DB::for_table('module')
            ->select('module.module_class_name')
            ->select('module.module_id')
            ->left_outer_join('module_lang', array('module.module_id', '=', 'module_lang.module_lang_module_id'))
            ->where(['module_lang.module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang.module_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('module.module_active', 1)
            ->find_one();

        $urlTab = $this->getUrl() ;
        $ct     = count( $urlTab ) ;

        if ( $ct == 1 && $result or ( $ct == 2 && $this->Lang()->count() > 1 ) )
        {
            $element = false ;
        }
        else
        {
            $element = ( $ct == $this->getOffset() ? false : true ) ;
        }

        $this->loadController( $result->module_class_name , $result->module_id , $element ) ;
    }

    protected function displayDefaultPage()
    {
        $page = \DB::for_table('page')
            ->select('page_id')
            ->where_equal('page_active', 1)
            ->where_equal('page_default', 1);

        if ( $this->hasMultiDomain() )
        {
            $idDomain = $this->getDomainId() ;

            if ( $idDomain !== NULL )
            {
                $page = $page->where_equal('page_domain_id', $idDomain);
            }
        }

        $page = $page->find_one();

        if ( $page )
        {
            if ( $this->Lang()->count() > 1 ) $this->urlDefaultPage() ;

            $app = $this->getApp() ;
            if ( file_exists( CONTROLLER_PROJECT_PATH . '/Page' . $page->page_id . ".php" ) )
            {
                $app->get('/(:lang)', function ( $lang = NULL ) use ( $page )
                {
                    $ControllerClass = '\Project\Controller\Front\Page' . $page->page_id ;

                    $pageClass = new $ControllerClass;
                    $pageClass->setId( $page->page_id );
                    $pageClass->execute();

                })->conditions(['lang' => '[a-z]+'])->via('GET', 'POST');
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
            ->select('page.page_access_user')
            ->select('page.page_access_user_group')
            ->select('page.page_access_user_redirect')
            ->left_outer_join('page_lang', array('page.page_id', '=', 'page_lang.page_lang_page_id'))
            ->where(['page_lang.page_lang_lang_id' => $this->Lang()->getActive()->id, 'page_lang.page_lang_url' => $this->getUrl( $this->getOffset() )])
            ->where_equal('page.page_active', 1)
            ->where_not_equal('page.page_default', 1);

        if ( $this->hasMultiDomain() )
        {
            $idDomain = $this->getDomainId() ;

            if ( $idDomain !== NULL )
            {
                $page = $page->where_equal('page_domain_id', $idDomain);
            }
        }

        $page = $page->find_one();

        if ( $page )
        {
            if ( $this->Lang()->count() > 1 ) $this->urlPage( $page->page_id ) ;

            $app = $this->getApp() ;
            if ( file_exists( CONTROLLER_PROJECT_PATH . '/Page' . $page->page_id . ".php" ) )
            {
				if ( ACTIVE_USER )
                {
                    // On observe les users manuellement car cela est fait bien plus tard normalement dans le middleware (necessite une route slim non présente actuellement)
                    $this->User()->observe();

                    if ( ( $page->page_access_user == 1 && $page->page_access_user_redirect != 0 && $this->User()->isLogged() == true ) or ( $page->page_access_user == 2 && $page->page_access_user_redirect != 0 && $this->User()->isLogged() == false ) )
                    {
                        $this->Factory()->Response()->redirect( $this->Factory()->Url()->page( $page->page_access_user_redirect ) );
                    }
                    else if ( $page->page_access_user == 2 && $this->User()->isLogged() == true )
                    {
                        // S'il est connecté mais pas dans le bon groupe
                        $tabGroup = unserialize( $page->page_access_user_group );
                        if ( ! in_array( $this->User()->getGroup() , $tabGroup ) )
                        {
                            $this->Factory()->Response()->redirect();
                        }
                    }
                }

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

    /* ************************************************** */
    /* *****************     URL      ******************* */
    /* ************************************************** */

    protected function urlPage( $id )
    {
        $urlPage = \DB::for_table('page_lang')
            ->select('page_lang_url')
            ->select('page_lang_lang_id')
            ->where(['page_lang_page_id' => $id])
            ->find_many();

        foreach( $urlPage as $row )
        {
            $this->Lang()->setUrl( $row->page_lang_lang_id , $row->page_lang_url );
        }

        $this->Lang()->setFront();
    }

    protected function urlDefaultPage()
    {
        foreach( $this->Lang()->getAll() as $l )
        {
            $this->Lang()->setUrl( $l->id , ( $this->Lang()->getDefault()->id != $l->id ? $l->url : '' )  , false );
        }

        $this->Lang()->setFront();
    }

    protected function urlModule( $id )
    {
        $result = \DB::for_table('module_lang')
            ->select('module_lang_url')
            ->select('module_lang_lang_id')
            ->where(['module_lang_module_id' => $id])
            ->find_many();

        foreach( $result as $row )
        {
            $this->Lang()->setUrl( $row->module_lang_lang_id , $row->module_lang_url );
        }

        $this->Lang()->setFront();
    }

    protected function urlElementModule( $mp , $url , $id_module )
    {
        $result = \DB::for_table('seo')
            ->select('seo.seo_element_id')
            ->where(['seo.seo_lang_id' => $this->Lang()->getActive()->id, 'seo.seo_url' => $url, 'seo.seo_module_id' => $id_module])
            ->find_one();

        if ( $result )  $id = $result->seo_element_id ;
        else            return false ;

        if ( $mp )
        {
            $Seo = new \App\Kernel\Front\Seo;
            $Seo->setElementId( $id ) ;
            $Seo->setModuleId( $id_module ) ;
            $urlElement = $Seo->getAllUrl() ;

            foreach( $urlElement as $lang_id => $url )
            {
                $this->Lang()->setUrl( $lang_id , $url );
            }
        }
        else
        {
            $Seo = new \App\Kernel\Front\Seo;
            $Seo->setElementId( $id ) ;
            $Seo->setModuleId( $id_module ) ;
            $urlElement = $Seo->getAllUrl() ;

            $result = \DB::for_table('module_lang')
                ->select('module_lang_url')
                ->select('module_lang_lang_id')
                ->where(['module_lang_module_id' => $id_module])
                ->find_many();

            foreach( $result as $module )
            {
                $this->Lang()->setUrl( $module->module_lang_lang_id , $module->module_lang_url . '/' . $urlElement[ $module->module_lang_lang_id ] );
            }
        }

        $this->Lang()->setFront();
    }
}