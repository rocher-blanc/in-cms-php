<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'NewsletterSubscriber' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText( mandatory_group_name))
            ->name(Translate::getInstance()->getText( group_name));
    }
}