<?php

namespace App\Kernel\Front;

class Helper
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $entity = NULL ;
    private $page   = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    public function setEntity( $name )
    {
        if ( ! empty( $name ) ) $this->entity = $name ;
    }

    public function setPage( $name )
    {
        $this->page = $name ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    private function getEntity()
    {
        return $this->entity ;
    }

    private function getPage()
    {
        return $this->page ;
    }

    private function getMethodName( $name )
    {
        return $name . 'Helper' ;
    }

    private function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function parseModule( $template , $var = [] )
    {
        return $this->parse( 'module/' . $this->getEntity() . '/' . $template . '.twig.html' , $var );
    }

    public function parsePage( $template , $var = [] )
    {
        return $this->parse( 'page/' . $this->getPage() . '/' . $template . '.twig.html' , $var );
    }

    private function parse( $template , $var = [] )
    {
        return $this->CMS()->fetch( 'helper/' . $template , $var );
    }

    public function getModuleHelper( $method , $arg = [] )
    {
        if ( $this->getEntity() !== NULL && ! empty( $method ) )
        {
            $ct = \DB::for_table('module')
                ->where_equal('module_class_name' , $this->getEntity() )
                ->where_equal('module_active', 1)
                ->count();

            if ( $ct != 0 )
            {
                if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . $this->getEntity() . '.php' ) ) $ControllerClass = "\Project\Module\Controller\Front\\" . $this->getEntity();
                else																			  $ControllerClass = '\App\Kernel\Front\Controller' ;

                $Controller = new $ControllerClass;
                $Controller->setEntityName( $this->getEntity() );

                if ( is_object( $Controller ) )
                {
                    $Controller->loadEntity();
                    $method = $this->getMethodName( $method ) ;

                    if ( method_exists( $Controller , $method ) )
                    {
                        return $Controller->$method( $this , $arg );
                    }
                    else
                    {
                        return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Method don\'t exist on this entity') ;
                    }
                }
                else
                {
                    return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Error on create object') ;
                }
            }
            else
            {
                return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Entity has not find') ;
            }
        }
        else
        {
            return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Entity or method has empty') ;
        }
    }

    public function getPageHelper( $method , $arg = [] )
    {
        if ( file_exists( CONTROLLER_PROJECT_PATH . '/' . $this->getPage() . ".php" ) )
        {
            require CONTROLLER_PROJECT_PATH . '/' . $this->getPage() . ".php";
            $class = str_replace("-", '', $this->getPage());
            $class = str_replace(".php", '', $class);
            $ControllerClass = '\Project\Controller\Front\\' . $class;
            $pageClass = new $ControllerClass;

            if ( is_object( $pageClass ) )
            {
                $method = $this->getMethodName( $method ) ;

                if ( method_exists( $pageClass , $method ) )
                {
                    return $pageClass->$method( $this , $arg );
                }
                else
                {
                    return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Method don\'t exist on this page') ;
                }
            }
            else
            {
                return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Error on create page object') ;
            }
        }
        else
        {
            return \App\Kernel\Factory::getInstance()->Response()->error('HELPER - Page was not find') ;
        }
    }
}