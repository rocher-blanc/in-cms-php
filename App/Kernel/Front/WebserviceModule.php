<?php

namespace App\Kernel\Front;

class WebserviceModule
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $name = NULL ;
    protected $route = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $name )
    {
        $this->name = $name ;
        $this->load() ;
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
        return $this->getController()->getRepository() ;
    }

    protected function getCallback( $route , $method )
    {
        return "WS" . $this->route[ $method ][ $route ] ;
    }

    /* ************************************************** */
    /* ******************    ISER     ******************* */
    /* ************************************************** */

    public function isDeclare( $method , $route )
    {
        $method = strtoupper( $method );

        if ( array_key_exists( $method , $this->route ) )
        {
            if ( ! empty( $this->route ) )
            {
                foreach( $this->route[ $method ] as $subRoute => $callback )
                {
                    if ( $this->match( $subRoute , $route , $method ) == true )
                    {
                        return true ;
                    }
                }
                return false ;
            }

            if ( array_key_exists( $route , $this->route[ $method ] ) )
            {
                return true ;
            }
            else
            {
                return false ;
            }
        }
        else
        {
            return false ;
        }
    }

    /* ************************************************** */
    /* ******************   DECLARE   ******************* */
    /* ************************************************** */

    protected function load()
    {

    }

    protected function add( $method , $route , $callback )
    {
        $this->route[ strtoupper( $method ) ][ $route ] = $callback ;
    }

    protected function get( $route , $callback )
    {
        $this->add( 'GET' , $route , $callback ) ;
    }

    protected function post( $route , $callback )
    {
        $this->add( 'POST' , $route , $callback ) ;
    }

    protected function put( $route , $callback )
    {
        $this->add( 'PUT' , $route , $callback ) ;
    }

    protected function delete( $route , $callback )
    {
        $this->add( 'DELETE' , $route , $callback ) ;
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

    public function displayCustom( $route , $method )
    {
        foreach( $this->route[ $method ] as $subRoute => $callback )
        {
            if ( $this->match( $subRoute , $route , $method ) == true )
            {
                $function = $this->getCallback( $subRoute , $method ) ;

                if ( method_exists( $this , $function ) == true )
                {
                    $this->printArray( $this->$function() ) ;
                }
                else
                {
                    $this->printArray();
                }
            }
        }
    }

    /* ************************************************** */
    /* ******************    MACTH    ******************* */
    /* ************************************************** */

    public function match( $url , $route , $method )
    {
        $this->params = [];
        $route = trim( $route, '/' );
        $path  = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $url);
        $regex = "#^$path$#i";

        if ( ! preg_match( $regex , $route , $matches ) )
        {
            return false;
        }

        array_shift( $matches );
        $this->matches = $matches;

        if ( $this->matches )
        {
            foreach( $this->matches as $key => $row )
            {
                $this->args[ $this->params[ $key ] ] = $this->matches[ $key ] ;
            }
        }

        return true;
    }

    private function paramMatch( $match )
    {
        $this->params[] = $match[1] ;

        return '([^/]+)';
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