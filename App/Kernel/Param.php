<?php

namespace App\Kernel;

class Param
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $_var = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    public function redis()
    {
        return \App\Kernel\Redis::getInstance() ;
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function set( $key , $value )
    {
        $content = \DB::for_table('param')->where_equal('param_key',$key)->find_one();

        if ( ! $content )
        {
            $content = \DB::for_table('param')->create();
        }

        $content->set('param_key',$key);
        $content->set('param_value',$value);
        $content->save();

        $this->redis()->set( 'param-' . $key , $value );

        $this->_var[ $key ] = $value;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function get( $key )
    {
        if ( array_key_exists( $key , $this->_var ) )
        {
            return $this->_var[ $key ];
        }
        /*else if ( $this->redis()->exist( 'param-' . $key ) != false )
        {
            return $this->redis()->get( 'param-' . $key );
        }*/
        else
        {
            $content = \DB::for_table('param')
                    ->select('param_value')
                    ->where_equal('param_key',$key)
                    ->find_one();

            if ( $content )
            {
                $this->_var[ $key ] = $content->param_value ;
                $this->redis()->set( 'param-' . $key , $content->param_value );
                return $content->param_value;
            }
            else
            {
                return NULL ;
            }
        }
    }

    /* ************************************************** */
    /* ****************     REMOVE    ******************* */
    /* ************************************************** */

    public function remove( $key )
    {
        $rst = \DB::for_table('param')->where_equal('param_key',$key)->find_one();
        if ( $rst ) $rst->delete();

        $this->redis()->get( 'param-' . $key );
    }
}