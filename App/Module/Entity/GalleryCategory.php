<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class GalleryCategory extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );

        $this->build('name')
            ->full()
            ->isVarchar()
            ->notEmpty("Veuillez renseigner un titre")
            ->name("Titre");

        $this->build('comment')
            ->full()
            ->isText()
            ->name("Commentaire");
    }
}