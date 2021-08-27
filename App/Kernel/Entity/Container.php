<?php

namespace App\Kernel\Entity;

use App\Kernel\Exception;

class Container
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $repositoryFront = NULL ;
    private $repositoryBack  = NULL ;

    private $class      = NULL ;
    private $entity     = NULL ;
    private $controller = NULL ;
    private $webservice = NULL ;
    private $name       = NULL ;
    private $namespace  = ["App" , "Shop" , "Project"] ;

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

    public function setRepositoryFront( $repository )
    {
        $this->repositoryFront = $repository;
    }

    public function setRepositoryBack( $repository )
    {
        $this->repositoryBack = $repository;
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

    public function getRepositoryFront()
    {
        return $this->repositoryFront;
    }

    public function getRepositoryBack()
    {
        return $this->repositoryBack;
    }

    /**
     * @return Builder
     */
    public function getEntity()
    {
        if ( $this->entity === NULL )
        {
            $class = $this->getEntityClassName() ;

            if ( $class !== NULL ) $this->setEntity( new $class );
        }

        return $this->entity ;
    }

    /**
     * @return Builder
     */
    public function getEntityClassName()
    {
        foreach( $this->namespace as $namespace )
        {
            if ( class_exists( "\\" . $namespace . "\Module\Entity\\" . $this->getName() ) )
            {
                return "\\" . $namespace . "\Module\Entity\\" . $this->getName() ;
            }
        }

        return NULL ;
    }

    public function getRepository( $admin = false )
    {
        $name = '' ;

        if ( ( $admin == true and $this->repositoryBack === NULL ) or ( $admin == false and $this->repositoryFront === NULL )  )
        {
            foreach( $this->namespace as $namespace )
            {
                if ( class_exists( "\\" . $namespace . "\Module\Repository\\" . ( $admin ? 'Back' : 'Front') . "\\" . $this->getName() ) )
                {
                    $name = "\\" . $namespace . "\Module\Repository\\" . ( $admin ? 'Back' : 'Front') . "\\" . $this->getName() ;
                }
            }

            if ( empty( $name ) )
            {
                $name = "\App\Kernel\\" . ( $admin ? 'Back' : 'Front') . "\\Base\Repository" ;
            }

            if ( $admin )   $this->setRepositoryBack( new $name( $this->getName() ) );
            else            $this->setRepositoryFront( new $name( $this->getName() ) );
        }

        if ( $admin )   return $this->repositoryBack ;
        else            return $this->repositoryFront ;
    }

    public function getWebservice()
    {
        $name = '' ;

        if ( $this->webservice === NULL )
        {
            foreach( $this->namespace as $namespace )
            {
                if ( class_exists( "\\" . $namespace . "\Module\Webservice\\" . $this->getName() ) )
                {
                    $name = "\\" . $namespace . "\Module\Webservice\\" . $this->getName() ;
                }
            }

            if ( empty( $name ) )
            {
                $name = "\App\Kernel\Front\Base\Webservice" ;
            }

            $this->setWebservice( new $name( $this->getName() ) );
        }

        return $this->webservice ;
    }

    public function getController( $admin = false , $opt = [] )
    {
        $ControllerClass = '' ;
        
        if ( $this->controller === NULL )
        {
            foreach( $this->namespace as $namespace )
            {
                if ( class_exists( "\\" . $namespace . "\Module\Repository\\" . ( $admin ? 'Back' : 'Front') . "\\" . $this->getName() ) )
                {
                    $ControllerClass = "\\" . $namespace . "\Module\Controller\\" . ( $admin ? 'Back' : 'Front') . "\\" . $this->getName() ;
                }
            }

            if ( empty( $ControllerClass ) )
            {
                $ControllerClass = "\App\Kernel\\" . ( $admin ? 'Back' : 'Front') . "\\Base\Controller" ;
            }
            
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