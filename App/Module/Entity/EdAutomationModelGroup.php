<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationModelGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomationModel' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");
    }
}