<?php

namespace App\Kernel;

class Container
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $param = NULL ;
    private $entity = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ****************    SINGLETON   ****************** */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Container;
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     MODULE    ******************* */
    /* ************************************************** */

    public function module( $name )
    {
        $name = ucfirst( $name );

        if ( array_key_exists( $name , $this->entity ) )
        {
            return $this->entity[ $name ] ;
        }
        else
        {
            return $this->entity[ $name ] = new \App\Kernel\Entity\Container( $name );
        }
    }

    /* ************************************************** */
    /* ****************     CLASS     ******************* */
    /* ************************************************** */

    public function newClass( $namespace )
    {
        $exp = explode("\\" , $namespace );
        $customNamespace = str_replace( $exp[0] . '\\' . $exp[1] , 'Project\CustomClass' , $namespace );
        $file = _PATH_ . '/' . str_replace( '\\' , '/' , $customNamespace ) . ".php" ;

        if ( file_exists( $file ) )
        {
            return new $customNamespace ;
        }
        else
        {
            return new $namespace ;
        }
    }

    /* ***************************************************** */
    /* ****************       PARAM      ******************* */
    /* ***************************************************** */

    public function param()
    {
        if ( $this->param === NULL )
        {
            $this->param = new \App\Kernel\Param;
        }

        return $this->param;
    }
}





