<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Kernel\Front\Data;

class Easyletter
{
    private $urlApi = 'https://api.easyletter.fr/' ;
    private $token  = EL_TOKEN ;
    private $client ;

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

        $Automation = new Data('EdAutomationModel');
        $Automation->find([
            'key' => $keyAutomation
        ]);

        $AutomationModel = new Data('EdAutomation');
        $AutomationModel->find([
            'element_module_parent_id' => $Automation->get('id'),
            'default' => 1
        ]);

        $tab = [] ;
        $tab[ $email ] = array_merge( $data , [ 'Email' => $email ]) ;

        $AutomationHistory = new Data('EdAutomationHistory');
        $AutomationHistory->create([
            'automation' => $AutomationModel->get('id'),
            'email' => $email,
            'date' => date("Y-m-d H:i:s"),
            'information' => json_encode( $tab ),
        ]);
        $AutomationHistory->save();

        $rst = $this->request([
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

            'subject' => $Automation->get('subject'),
            'senderName' => $Sender->get('name'),
            'senderEmail' => $Sender->get('email'),
            'returnPathEmail' => $Sender->get('email_response'),

            'recipient' => \App\Kernel\Http::getInstance()->getUrl() . "/email/automation/recipient/" . $AutomationHistory->get('id'),
            'content' => \App\Kernel\Http::getInstance()->getUrl() . "/email/automation/template/" . $AutomationModel->get('id'),
        ]);

        if ( $rst !== false )
        {
            $AutomationHistory->set('id_easyletter' , $rst );
            $AutomationHistory->save();
        }

        return $AutomationHistory->get('id');
    }

    public function newsletter( array $data )
    {
        $array = array_merge([
            'msgType' => '0', // 0 = HTML ; 1 = TXT ; 2 = SMS
            'msgSMS' => "",
            'urlUnsubscribe' => "",

            'txtOnlineViewTag' => "",
            'txtHtmlUnsubscribeTag' => "",
            'txtSendToAFriendTag' => "",

            'schedule' => '1',
            'sendingRate' => '0',
            'transactional' => '0',
        ], $data );

        return $this->request( $array );
    }

    private function request( array $data )
    {
        $response = $this->client->post('/v1/campaign/quick', [
            'body' => json_encode( $data )
        ]);

        if ( $response->getStatusCode() == 200 )
        {
            $body = json_decode( $response->getBody()->getContents() , true ) ;

            return $body['data']['id'] ;
        }
        else
        {
            $body = json_decode( $response->getBody()->getContents() , true ) ;
            $this->setError( $body['response']['error'] );

            return false ;
        }
    }

    public function stats()
    {

    }

    public function credit()
    {

    }
}