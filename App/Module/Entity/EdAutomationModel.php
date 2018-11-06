<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationModel extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomation' );
        $this->setModuleParent( 'EdAutomationModelGroup' );

        $this->build('name')
            ->full()
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du modèle")
            ->name("Nom");

        $this->build('subject')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le sujet")
            ->name("Sujet");

        $this->build('key')
            ->column(1, 3)
            ->isVarchar()
            ->name("Clef");

        $this->build('vars')
            ->column(1, 3)
            ->isCheckbox()
            ->ManyToMany('EdAutomationVar')
            ->name("Variables d'environnements");
    }
}