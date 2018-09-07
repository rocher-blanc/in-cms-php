<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;

class NewsletterCampaign extends Controller
{
    public function hookAddSaveAfter()
    {
        // j'envoi la rqt a api easyletter
    }
}