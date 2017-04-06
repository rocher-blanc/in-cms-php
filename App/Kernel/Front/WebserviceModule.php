<?php

namespace App\Kernel\Front;

class WebserviceModule
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $name = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $name )
    {
        $this->name = $name ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */



    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getName()
    {
        return $this->name ;
    }

    protected function getEntity()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    protected function getController()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getController() ;
    }

    protected function getRepository()
    {
        return $this->getController()->getRespository() ;
    }

    /* ************************************************** */
    /* ******************   DISPLAY   ******************* */
    /* ************************************************** */

    public function displayGetAll( $filter , $sort , $limit , $offset )
    {
        $all = $this->getRepository()->findApi( $filter , $sort , $limit , $offset );

        if ( $all )
        {
            $elmts = [];
            foreach( $all as $row )
            {
                $elmts[] = $this->getController()->parseValue( $row );
            }

            $this->printArray( $elmts ) ;
        }
        else
        {
            $this->printArray();
        }
    }

    public function displayGetOne( $id )
    {
        $one = $this->getRepository()->findOne( $id );

        if ( $one )
        {
            $this->printArray( $this->getController()->parseValue( $one ) ) ;
        }
        else
        {
            $this->printArray();
        }
    }

    /* ************************************************** */
    /* ******************    PRINT    ******************* */
    /* ************************************************** */

    protected function printArray( $array = [] )
    {
        if ( empty( $array ) )
        {
            http_response_code( 404 );
        }
        else
        {
            http_response_code( 200 );
        }

        echo json_encode( $array );
        die;
    }
}