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

        $this->remove( $key ) ;

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

    public function label( $field , $remove = false )
    {
        $rst = $this->get( $field , "label" );

        if ( $remove ) $this->remove( $field ) ;

        return $rst ;
    }

    public function error( $field , $remove = false )
    {
        $rst = $this->get( $field , "error" );

        if ( $remove ) $this->remove( $field ) ;

        return $rst ;
    }

    public function help( $field , $remove = false )
    {
        $rst = $this->get( $field , "help" );

        if ( $remove ) $this->remove( $field ) ;

        return $rst ;
    }

    public function widget( $field , $remove = false )
    {
        $rst = $this->get( $field , "widget" );

        if ( $remove ) $this->remove( $field ) ;

        return $rst ;
    }

    public function value( $field , $remove = false )
    {
        $rst = $this->get( $field , "value" );

        if ( $remove ) $this->remove( $field ) ;

        return $rst ;
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