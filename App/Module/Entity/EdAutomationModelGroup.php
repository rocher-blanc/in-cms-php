<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class EdAutomationModelGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomationModel' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText( 'mandatory_template_name'))
            ->name(Translate::getInstance()->getText( 'nom'));
    }
}