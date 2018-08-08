<?php

namespace App\Kernel\Entity;

class Container
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $repository = NULL ;
    private $class      = NULL ;
    private $entity     = NULL ;
    private $controller = NULL ;
    private $webservice = NULL ;
    private $name       = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $name )
    {
        $this->setName( $name );
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    public function setName( $name )
    {
        $this->name = ucfirst( $name );
    }

    public function setEntity( $entity )
    {
        $this->entity = $entity;
    }

    public function setRepository( $repository )
    {
        $this->repository = $repository;
    }

    public function setWebservice( $webservice )
    {
        $this->webservice = $webservice;
    }

    public function setController( $controller )
    {
        $this->controller = $controller;
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    public function getName()
    {
        return $this->name ;
    }

    public function getEntity()
    {
        if ( $this->entity === NULL )
        {
            if ( file_exists( V_ENTITY_PATH . '/' . $this->getName() . '.php' ) )
            {
                $name = "\App\Module\Entity\\" . $this->getName() ;
            }
            else
            {
                $name = "\Project\Module\Entity\\" . $this->getName() ;
            }
            $this->setEntity( new $name );
        }

        return $this->entity ;
    }

    public function getRepository( $admin = false )
    {
        if ( $this->repository === NULL )
        {
            if ( file_exists( V_REPOSITORY_PATH . '/' . $this->getName() . '.php' ) )
            {
                $name = "\App\Module\Repository\\" . ( $admin ? "Back" : "Front" ) . "\\" . $this->getName() ;
            }
            else
            {
                $name = "\Project\Module\Repository\\" . ( $admin ? "Back" : "Front" ) . "\\" . $this->getName() ;
            }


            $this->setRepository( new $name( $this->getName() ) );
        }

        return $this->repository ;
    }

    public function getWebservice()
    {
        if ( $this->webservice === NULL )
        {
            $name = "\Project\Module\Webservice\\" . $this->getName() ;

            $this->setWebservice( new $name( $this->getName() ) );
        }

        return $this->webservice ;
    }

    public function getController( $admin = false , $opt = [] )
    {
        if ( $this->controller === NULL )
        {
            if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . $this->getName() . '.php' ) )   $ControllerClass = "\Project\Module\Controller\\" . ( $admin ? "Back" : "Front" ) . "\\" . $this->getName() ;
            else if ( file_exists( V_CONTROLLER_PATH . '/' . $this->getName() . '.php' ) )    $ControllerClass = "\App\Module\Controller\\" . ( $admin ? "Back" : "Front" ) . "\\" . $this->getName() ;
            else																			           $ControllerClass = '\App\Kernel\\' . ( $admin ? "Back" : "Front" ) . '\Controller' ;

            $Controller = new $ControllerClass;

            $Controller->setEntityName( $this->getName() );
            $result = $Controller->loadEntity();
            if ( $admin )
            {
                if ( ! empty( $opt ) )
                {
                    foreach( $opt as $key => $value )
                    {
                        $Controller->setOption( $key , $value );
                    }
                    $Controller->init();
                }
            }

            if ( $result ) $this->setController( $Controller );
        }

        return $this->controller ;
    }
}