<?php

namespace App\Kernel\View;

use App\Kernel\Container;

class Form extends \Twig_Extension
{
    private $form ;

    public function getName()
    {
        return 'form';
    }

    private function Container()
    {
        return Container::getInstance() ;
    }

    public function getFunctions()
    {
       return [
            new \Twig_SimpleFunction('form', [$this, 'form']),
            new \Twig_SimpleFunction('formDelete', [$this, 'formDelete']),

            new \Twig_SimpleFunction('form_init', [$this, 'formInit']),
            new \Twig_SimpleFunction('form_start', [$this, 'formStart']),
            new \Twig_SimpleFunction('form_end', [$this, 'formEnd']),
            new \Twig_SimpleFunction('form_fields', [$this, 'formFields']),

            new \Twig_SimpleFunction('form_row', [$this, 'formRow']),
            new \Twig_SimpleFunction('form_label', [$this, 'formLabel']),
            new \Twig_SimpleFunction('form_error', [$this, 'formError']),
            new \Twig_SimpleFunction('form_widget', [$this, 'formWidget']),
            new \Twig_SimpleFunction('form_help', [$this, 'formHelp']),

            new \Twig_SimpleFunction('form_css', [$this, 'formCss']),
            new \Twig_SimpleFunction('form_js', [$this, 'formJs']),
       ];
    }

    public function form( $module , $id = NULL , $url = '', $timer = '' )
    {
        return $this->Container()->module( $module )->getController()->getForm( $id , $url , $timer );
    }

    public function formDelete( $module , $id , $var = [], $url = '' )
    {
        return $this->Container()->module( $module )->getController()->getFormDelete( $id , $var , $url );
    }

    public function formInit( $module , $id = NULL , $url = '', $timer = '' )
    {
        return $this->Container()->module( $module )->getController()->getCustomForm( $id , $url , $timer );
    }

    public function formStart( $form )
    {
        return $form['start'] ;
    }

    public function formEnd( $form )
    {
        return $form['end'] ;
    }

    public function formJs( $form )
    {
        return $form['js'] ;
    }

    public function formCss( $form )
    {
        return $form['css'] ;
    }

    public function formFields( $form )
    {
        $str = '' ;
        if ( ! empty( $form['field'] ) )
        {
            foreach( $form['field'] as $key => $tab )
            {
                $str.= $this->formRow( $form , $key , false );
            }

            $form['field'] = [];
        }

        return $str ;
    }

    public function formRow( $form , $field , $unset = true )
    {
        $rst = $this->formGet( $form , $field , "row" , $unset );

        unset( $form['field'][ $field ] );

        return $rst ;
    }

    public function formLabel( $form , $field )
    {
        return $this->formGet( $form , $field , "label" );
    }

    public function formError( $form , $field )
    {
        return $this->formGet( $form , $field , "error" );
    }

    public function formHelp( $form , $field )
    {
        return $this->formGet( $form , $field , "help" );
    }

    public function formWidget( $form , $field )
    {
        return $this->formGet( $form , $field , "widget" );
    }

    public function formGet( $form , $field , $key , $unset = false )
    {
        if ( array_key_exists( $field , $form['field'] ) )
        {
            return $form['field'][ $field ][ $key ] ;
        }
    }
}