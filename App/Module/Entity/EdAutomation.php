<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomation extends Builder
{
    protected function load()
    {
        if ( TOPOL_USER_ID !== NULL && TOPOL_API_KEY !== NULL )
        {
            $this->addAction( 'saveMail' );
            $this->addAction( 'duplicate' );

            $this->addAction( 'sendMail' );
            $this->addAction( 'topolFileManager' );
            $this->addAction( 'topolFileUpload' );

            $this->addIcon( 'icon-photo' , 'draw' );
            $this->addIcon( 'icon-line-link' , 'view' , function( $c ) {
                return ( $c->html != "" ? true : false );
            } , true );
        }

        $this->setFieldReference( 'name' );
        $this->setModuleParent( 'EdAutomationModel' );
        $this->setDefault();

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");

        $this->build('html')
            ->noFront()
            ->noBack()
            ->isText("LONG")
            ->name("HTML");

        $this->build('json')
            ->noFront()
            ->noBack()
            ->isText("LONG")
            ->name("JSON");
    }
}