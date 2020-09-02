<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class EdAutomationVarGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomationVar' );

		$this->addIcon( 'icon-list' , 'addVariables' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_name'))
            ->name(Translate::getInstance()->getText('nom'));
    }
}