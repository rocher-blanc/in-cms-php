<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationHistory extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );

        $this->build('email')
            ->column(1, 1)
            ->isVarchar("255", "email")
            ->notEmpty("Veuillez renseigner un email")
            ->name("Email");

        $this->build('date')
            ->isDate(true)
            ->name("Date");

        $this->build('information')
            ->noFront()
            ->noBack()
            ->isText()
            ->name("JSON - Informations");

        $this->build('automation')
            ->isSelect()
            ->ManyToMany("EdAutomation")
            ->name("Automation");
    }
}