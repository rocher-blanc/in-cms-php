<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Kernel\Front\Data;
use App\Kernel\Container;

class Easyletter
{
    private $urlApi = 'https://api.easyletter.fr/' ;
    private $token  = EL_TOKEN ;
    private $client ;
    private $error = '' ;

    public function __construct()
    {
        if ( $this->token === NULL )
        {

        }
        else
        {
            $this->client = new Client([
                'base_uri' => $this->urlApi,
                'headers'  => [
                    'X-API-KEY'    => $this->token,
                    'Content-Type' => 'application/json'
                ]
            ]);
        }
    }

    private function setError( $msg )
    {
        $this->error = $msg ;
        \App\Kernel\Utils\Slack::notify( "Erreur sur un projet client - " . $_SERVER['SERVER_NAME'] , 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL'] , "logs-errors" , $msg );
    }

    public function getError()
    {
        return $this->error ;
    }

    public function automotion( string $keyAutomation , string $email , array $data = [] , $idSender = NULL )
    {
        if ( $idSender !== NULL )
        {
            $Sender = new Data('NewsletterSender');
            $Sender->find( $idSender );
        }
        else
        {
            $Sender = new Data('NewsletterSender');
            $Sender->find([
                "default" => 1
            ]);
        }

        $EdAutomationModel = new Data('EdAutomationModel');
        $rst = $EdAutomationModel->find([
            'key' => $keyAutomation
        ]);

        if ( $rst )
        {
            $EdAutomation = new Data('EdAutomation');
            $rstAutomation = $EdAutomation->find([
                'element_module_parent_id' => $EdAutomationModel->get('id'),
                'default' => 1
            ]);

            if ( $rstAutomation )
            {
                $tab = [] ;
                $tab[ $email ] = array_merge( $data , [ 'Email' => $email ]) ;

                $AutomationHistory = new Data('EdAutomationHistory');
                $AutomationHistory->create([
                    'automation' => $EdAutomation->get('id'),
                    'email' => $email,
                    'date' => date("Y-m-d H:i:s"),
                    'information' => json_encode( $tab ),
                ]);
                $AutomationHistory->save();

                $response = $this->request([
                    'msgType' => '0', // 0 = HTML ; 1 = TXT ; 2 = SMS
                    'msgSMS' => "",
                    'urlUnsubscribe' => "",

                    'txtOnlineViewTag' => "",
                    'txtHtmlUnsubscribeTag' => "",
                    'txtSendToAFriendTag' => "",

                    'dateTimeUTC' => date("Y-m-d H:i:s"),
                    'schedule' => '0',
                    'sendingRate' => '0',
                    'transactional' => '1',

                    'subject' => $EdAutomationModel->get('subject'),
                    'senderName' => $Sender->get('name'),
                    'senderEmail' => $Sender->get('email'),
                    'returnPathEmail' => $Sender->get('email_response'),

                    'recipient' => \App\Kernel\Http::getInstance()->getUrl() . "/email/automation/recipient/" . $AutomationHistory->get('id'),
                    'content' => \App\Kernel\Http::getInstance()->getUrl() . "/email/automation/template/" . $EdAutomation->get('id'),
                ]);

                if ( $response !== false )
                {
                    $AutomationHistory->set('id_easyletter' , $response );
                    $AutomationHistory->save();
                }

                return $AutomationHistory->get('id');
            }
            else
            {
                $this->setError("Aucun automation n'est disponible dans la catégorie \"" . $EdAutomationModel->get('name') . "\"") ;
            }
        }
        else
        {
            $this->setError("Aucun modele ne correspond à la clef \"$keyAutomation\"") ;
        }

        return false ;
    }

    public function newsletter( int $idNewsletter )
    {
        $NL = new Data('NewsletterCampaign');
        $NL->find( $idNewsletter );

        $Sender = new Data('NewsletterSender');
        $Sender->find( $NL->get('sender') );

        $response = $this->request([
            'msgType' => '0', // 0 = HTML ; 1 = TXT ; 2 = SMS
            'msgSMS' => "",
            'urlUnsubscribe' => "",

            'txtOnlineViewTag' => "",
            'txtHtmlUnsubscribeTag' => "",
            'txtSendToAFriendTag' => "",

            'schedule' => '1',
            'sendingRate' => '0',
            'transactional' => '0',

            'subject' => $NL->get('subject'),
            'senderName' => $Sender->get('name'),
            'senderEmail' => $Sender->get('email'),
            'returnPathEmail' => $Sender->get('email_response'),

            'recipient' => \App\Kernel\Http::getInstance()->getUrl() . "/email/newsletter/recipient/" . $NL->get('id'),
            'content' => \App\Kernel\Http::getInstance()->getUrl() . "/email/newsletter/template/" . $NL->get('template'),
        ]);

        if ( $response !== false )
        {
            $NL->set('statut' , 2 ); // on passele statut a transferee
            $NL->save();

            return true ;
        }
        else
        {
            return false ;
        }
    }

    private function request( array $data )
    {
        $response = $this->response( $this->client->post('/v1/campaign', [
            'body' => json_encode( $data )
        ]) ) ;

        if ( $response !== false )  return $response['id'] ;
        else                        return $response ;
    }

    public function stats( int $id )
    {
        return $this->response( $this->client->get('v1/routage/stats/' . $id ) ) ;
    }

    public function credit()
    {

    }

    public function response( $response )
    {
        if ( $response->getStatusCode() == 200 )
        {
            $body = json_decode( $response->getBody()->getContents() , true ) ;
            Container::getInstance()->param()->set('el_credits' , $body['credits'] );

            return $body['data'] ;
        }
        else
        {
            $body = json_decode( $response->getBody()->getContents() , true ) ;
            $this->setError( $body['response']['error'] );

            return false ;
        }
    }
}