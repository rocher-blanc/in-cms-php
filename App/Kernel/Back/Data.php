<?php
/**
 * Created by PhpStorm.
 * User: Jammye
 * Date: 22/08/2018
 * Time: 12:11
 */

namespace App\Kernel\Back;


class Data
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $name;
    private $data;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $name )
    {
        $this->name = $name ;
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    private function setData( $rst )
    {
        $this->data = $rst ;
    }

    public function set( $key , $value )
    {
        $this->data->set( $this->getEntity()->get( $key )->getColumn() , $value );
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getName()
    {
        return $this->name ;
    }

    public function getData()
    {
        return $this->data ;
    }

    public function getEntity()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function getRepository()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getRepository(true) ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function save()
    {
        return $this->data->save();
    }

    public function findOrCreate( $value )
    {
        if ( is_array( $value ) )
        {
            $rst = $this->getRepository()->findWhere( $value );
        }
        else
        {
            $rst = $this->getRepository()->findOne( $value );
        }

        if ( ! $rst )
        {
            $rst = $this->getRepository()->create() ;
        }

        $this->setData( $rst );
    }
}