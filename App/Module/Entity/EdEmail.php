<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdEmail extends Builder
{
    protected function load()
    {
        if ( TOPOL_USER_ID !== NULL && TOPOL_API_KEY !== NULL )
        {
            $this->addAction( 'saveMail' );
            $this->addAction( 'duplicate' );
            $this->addIcon( 'icon-photo' , 'draw' , function($c) {
                return ( $c->blocked == 0 ? true : false );
            });
        }

        $this->setFieldReference( 'name' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");

        $this->build('blocked')
            ->isBoolean()
            ->name("Modification bloquer" );

        $this->build('html')
            ->noFront()
            ->noBack()
            ->isText()
            ->name("HTML");

        $this->build('json')
            ->noFront()
            ->noBack()
            ->isText()
            ->name("JSON");
    }
}