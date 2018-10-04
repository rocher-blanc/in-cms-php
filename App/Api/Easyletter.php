<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Easyletter
{
    private $urlApi = 'https://api.easyletter.fr/v1/' ;
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
                    'X-API-KEY' => $this->token
                ]
            ]);
        }
    }

    public function automotion( array $data )
    {
        $array = array_merge( $data , [
            'msgType' => 0, // 0 = HTML ; 1 = TXT ; 2 = SMS
            'msgSMS' => "",
            'urlUnsubscribe' => "",

            'txtOnlineViewTag' => "",
            'txtHtmlUnsubscribeTag' => "",
            'txtSendToAFriendTag' => "",

            'dateTimeUTC' => date("Y-m-d H:i:s"),
            'schedule' => 0,
            'sendingRate' => 0,
            'transactional' => 1,
        ]);

        return $this->request( $array );
    }

    public function newsletter( array $data )
    {
        $array = array_merge( $data , [
            'msgType' => 0, // 0 = HTML ; 1 = TXT ; 2 = SMS
            'msgSMS' => "",
            'urlUnsubscribe' => "",

            'txtOnlineViewTag' => "",
            'txtHtmlUnsubscribeTag' => "",
            'txtSendToAFriendTag' => "",

            'schedule' => 1,
            'sendingRate' => 0,
            'transactional' => 0,
        ]);

        return $this->request( $array );
    }

    private function request( array $data )
    {
        $response = $this->client->post('/v1/campaign/quick', [
            'body' => json_encode( $data )
        ]);

        if ( $response->getStatusCode() == 200 )
        {
            return true ;
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