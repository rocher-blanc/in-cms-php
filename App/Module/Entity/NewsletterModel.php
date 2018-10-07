<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterModel extends Builder
{
    protected function load()
    {
        if ( TOPOL_USER_ID !== NULL && TOPOL_API_KEY !== NULL )
        {
            $this->addAction( 'saveMail' );
            $this->addIcon( 'icon-photo' , 'draw' );
            $this->addAction( 'duplicate' );
        }

        $this->setFieldReference( 'name' );
        $this->setModuleParent( 'NewsletterCampaignGroup' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");

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