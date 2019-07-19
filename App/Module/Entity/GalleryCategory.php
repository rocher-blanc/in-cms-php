<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class GalleryCategory extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->addIcon( 'icon-photo' , 'draw' );
        $this->addAction('deletemedia');


        $this->build('name')
            ->full()
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_title'))
            ->name(Translate::getInstance()->getText('title'));

        $this->build('comment')
            ->full()
            ->isText()
            ->name(Translate::getInstance()->getText('comment'));
    }
}