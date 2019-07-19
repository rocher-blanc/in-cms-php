<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class EdAutomationVar extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'label' );
        $this->setModuleParent( 'EdAutomationVarGroup' );

        $this->build('key')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_key'))
            ->name(Translate::getInstance()->getText('key'));

        $this->build('text')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_txt'))
            ->name(Translate::getInstance()->getText('text'));

        $this->build('label')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_label'))
            ->name();
    }
}