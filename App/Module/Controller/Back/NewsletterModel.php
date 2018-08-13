<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;

class NewsletterModel extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('apiKey' , 'DCSCyS2S1STYFy0EpQJByYkLSIl8eCetqNJ6Awh79Ykk4Qh0WymMJsTqZYLW');
        $this->setRender('userId' , 'easydoor');

        $this->render('draw.twig');
    }
}