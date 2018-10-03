<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationVarGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomationVar' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom")
            ->name("Nom");
    }
}