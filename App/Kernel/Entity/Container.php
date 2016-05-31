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

    public function setClass( $class )
    {
        $this->class = $class;
    }

    public function setRepository( $repository )
    {
        $this->repository = $repository;
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
        return $this->entity ;
    }

    public function getClass()
    {
        if ( $this->class === NULL )
        {
            $className = "\Project\Module\Entity\Class\\" . $this->getName() ;
            $this->setClass( new $className );
        }

        return $this->class ;
    }

    public function getRepository()
    {
        if ( $this->repository === NULL )
        {
            $this->setRepository( \App\Kernel\Container::getInstance()->get('em')->getRepository( "\Project\Module\Entity\Class\\" . $this->getName() ) );
        }

        return $this->repository ;
    }

    public function getController()
    {
        if ( $this->controller === NULL )
        {
            if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . $this->getName() . '.php' ))  $ControllerClass = "\Project\Module\Controller\Front\\" . $this->getName() ;
            else																			$ControllerClass = '\App\Kernel\Front\Controller' ;

            $Controller = new $ControllerClass;
            $Controller->setEntityName( $this->getName() );
            $result = $Controller->loadEntity();

            if ( $result ) $this->setController( $Controller );
        }

        return $this->controller ;
    }
}