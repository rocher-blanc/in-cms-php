<?php

namespace App\Kernel\Front;

class Former
{
    private $form = [];

    public function __construct(array $form)
    {
        $this->form = $form ;
    }

    public function row( string $key )
    {
        $rst = $this->get( $key , "row" , true );

        unset( $this->form['field'][ $key ] );

        return $rst ;
    }

    public function remove( string $field )
    {
        unset( $this->form['field'][ $field ] );
    }

    public function js()
    {
        return $this->form['js'] ;
    }

    public function css()
    {
        return $this->form['css'] ;
    }

    public function start()
    {
        return $this->form['start'] ;
    }

    public function end()
    {
        return $this->form['end'] ;
    }

    public function label( $field )
    {
        return $this->get( $field , "label" );
    }

    public function error( $field )
    {
        return $this->get( $field , "error" );
    }

    public function help( $field )
    {
        return $this->get( $field , "help" );
    }

    public function widget( $field )
    {
        return $this->get( $field , "widget" );
    }

    public function get( $field , $key , $unset = false )
    {
        if ( array_key_exists( $field , $this->form['field'] ) )
        {
            $rst = $this->form['field'][ $field ][ $key ] ;
            if ( $unset )
            {
                $this->remove( $field );
            }

            return $rst ;
        }

        return false ;
    }

    public function fields()
    {
        $str = '' ;
        if ( ! empty( $this->form['field'] ) )
        {
            foreach( $this->form['field'] as $key => $tab )
            {
                $str.= $this->row( $key , false );
            }

            $this->form['field'] = [];
        }

        return $str ;
    }
}