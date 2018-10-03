<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationVar extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'label' );
        $this->setModuleParent( 'EdAutomationVarGroup' );

        $this->build('key')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner la clef")
            ->name("Clef");

        $this->build('text')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le texte")
            ->name("Texte");

        $this->build('label')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le label")
            ->name("Label");
    }
}