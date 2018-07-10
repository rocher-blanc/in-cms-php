<?php

namespace App\Api;

use Guzzle\Http\Client;

class Eudonet
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $token = '' ;
    private $headers = [
        'Content-Type' => "application/json"
    ];
    private static $instance = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://xrm3.eudonet.com/EudoAPI/',
        ]);
        $this->client->setDefaultHeaders( $this->headers );

        $this->getToken();
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************    SINGLETON   ****************** */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Eudonet;
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************      TOKEN     ****************** */
    /* ************************************************** */

    private function getToken()
    {
        $rst = $this->request("post" , 'Authenticate/Token' , [
            "SubscriberLogin" => EUDO_SUBSCRIBER_LOGIN,
            "SubscriberPassword" => EUDO_SUBSCRIBER_PASSWORD,
            "BaseName" => EUDO_BASE_NAME,
            "UserLogin" => EUDO_USER_LOGIN,
            "UserPassword" => EUDO_USER_PASSWORD,
            "UserLang" => EUDO_USER_LANG,
            "ProductName" => EUDO_PRODUCT_NAME
        ]);

        dump( $rst );
    }

    /* ************************************************** */
    /* ****************     REQUEST    ****************** */
    /* ************************************************** */

    private function request( $type , $route , $params )
    {
        $type = strtolower( $type );
        try
        {
            $authResponse = $this->client->$type( $route , $this->headers , [
                'json' => $params
            ]);

            return json_decode($authResponse->getBody(), true);
        } catch ( Exception $e )
        {
            dump( $e );
        }
    }
}