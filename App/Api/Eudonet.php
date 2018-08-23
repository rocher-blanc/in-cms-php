<?php

namespace App\Api;

use GuzzleHttp\Client;

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
            "SubscriberLogin"       => EUDO_SUBSCRIBER_LOGIN,
            "SubscriberPassword"    => EUDO_SUBSCRIBER_PASSWORD,
            "BaseName"              => EUDO_BASE_NAME,
            "UserLogin"             => EUDO_USER_LOGIN,
            "UserPassword"          => EUDO_USER_PASSWORD,
            "UserLang"              => EUDO_USER_LANG,
            "ProductName"           => EUDO_PRODUCT_NAME
        ]);

        if ( $rst['ResultInfos']['Success'] == true )
        {
            $this->headers['x-auth'] = $rst['ResultData']['Token'] ;
        }
    }

    /* ************************************************** */
    /* ****************       ONE      ****************** */
    /* ************************************************** */

    public function one( $tablId , $id )
    {
        $tab = [];
        $rst = $this->request("get" , 'Search/' . $tablId . '/' . $id );

        dump( $rst );

        if ( $rst['ResultInfos']['Success'] == true )
        {
            if ( $rst['ResultData']['Rows'][0]['Fields'] )
            {
                foreach( $rst['ResultData']['Rows'][0]['Fields'] as $row )
                {
                    $tab[ $row['DescId'] ] = $row['DbValue'] ;
                }
            }

            if ( $rst['ResultMetaData']['Tables'][0]['Fields'] )
            {
                foreach( $rst['ResultMetaData']['Tables'][0]['Fields'] as $row )
                {
                    $tab1[ $row['DescId'] ] = $row['Label'] ;
                }
                dump( $tab1 );
            }
        }

        return $tab ;
    }

    /* ************************************************** */
    /* ****************     SEARCH     ****************** */
    /* ************************************************** */

    public function search( $tablId , $params , $order , $page = 1 )
    {
        $custom = '' ;
        $strTmp = '' ;
        $i      = 0;

        foreach( $params as $row )
        {
            if ( $i > 0 ) $strTmp.= ',';
            $std = new \stdClass;
            $std->WhereCustoms = null;
            $std->Criteria = new \stdClass;
            $std->Criteria->Operator = intval($row['crit']['operator']);
            $std->Criteria->Field = $row['crit']['field'];
            $std->Criteria->Value = $row['crit']['value'];
            $std->InterOperator = $row['interoperator'];

            $strTmp.= json_encode( $std );
            $i++;
        }

        $str = '{
  "ShowMetadata": true,
  "RowsPerPage": 25, 
  "NumPage": ' . $page . ',
  "ListCols": [
    0
  ],
  "FilterId": 0,
  "WhereCustom": 
  {
    "WhereCustoms": [
        ' . $strTmp . '
    ],
    "Criteria": null,
    "InterOperator": 0
  },
  "OrderBy": [
    {
      "DescId": ' . $order . ',
      "Order": 0
    }
  ]
}' ;

        dump( $str );
        $params = json_decode( $str );

        $tab = [];
        $rst = $this->request("post" , 'Search/' . $tablId , $params );

        dump( $rst );

        if ( $rst['ResultInfos']['Success'] == true )
        {
            if ( $rst['ResultData']['Rows'][0]['Fields'] )
            {
                foreach( $rst['ResultData']['Rows'][0]['Fields'] as $row )
                {
                    $tab[ $row['DescId'] ] = $row['DbValue'] ;
                }
            }
        }

        return $tab ;
    }

    /* ************************************************** */
    /* ****************       ADD      ****************** */
    /* ************************************************** */

    public function add( $tablId , $params )
    {
        $infos = [];

        if ( ! empty( $params ) )
        {
            foreach( $params as $descId => $value )
            {
                $infos['Fields'][] = [
                    "DescId" => $descId,
                    "Value"  => $value
                ];
            }
        }

        $rst = $this->request("post" , 'CUD/' . $tablId , $infos );

        return $rst ;
    }

    /* ************************************************** */
    /* ****************     UPDATE     ****************** */
    /* ************************************************** */

    public function update( $tablId , $id , $params )
    {
        $infos = [];

        if ( ! empty( $params ) )
        {
            foreach( $params as $descId => $value )
            {
                $infos['Fields'][] = [
                    "DescId" => $descId,
                    "Value"  => $value
                ];
            }
        }

        $rst = $this->request("post" , 'CUD/' . $tablId . '/' . $id , $infos );

        return $rst ;
    }

    /* ************************************************** */
    /* ****************     DELETE     ****************** */
    /* ************************************************** */

    public function delete( $tablId , $id )
    {

    }

    /* ************************************************** */
    /* ****************     IMAGE     ****************** */
    /* ************************************************** */

    public function image( $tablId , $id )
    {

    }

    /* ************************************************** */
    /* ****************     REQUEST    ****************** */
    /* ************************************************** */

    private function request( $type , $route , $params = [] )
    {
        $type = strtolower( $type );
        try
        {
            $authResponse = $this->client->$type( $route , [
                'headers' => $this->headers,
                'json' => $params
            ]);

            return json_decode($authResponse->getBody(), true);
        } catch ( Exception $e )
        {
            dump( $e );
        }
    }
}