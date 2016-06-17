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
        else
        {
            return NULL;
        }
    }

    /* ************************************************** */
    /* ****************     REMOVE    ******************* */
    /* ************************************************** */

    public function remove( $key )
    {
        $rst = \DB::for_table('param')->where_equal('param_key',$key)->find_one();
        $rst->delete();
    }
}





