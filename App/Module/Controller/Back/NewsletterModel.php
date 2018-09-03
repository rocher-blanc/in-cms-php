<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;

class NewsletterModel extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('apiKey' , TOPOL_API_KEY);
        $this->setRender('userId' , TOPOL_USER_ID);

        $this->render('draw.twig');
    }
}