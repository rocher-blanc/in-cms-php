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

    public function __construct( $token = NULL )
    {
        $this->client = new Client([
            'base_uri' => 'https://xrm3.eudonet.com/EudoAPI/',
        ]);

        if ( $token === NULL or $token == "" )
        {
            $this->getApiToken();
        }
        else
        {
            $this->headers['x-auth'] = $token ;
        }
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

    private function getApiToken()
    {
        $rst = $this->request("post" , 'Authenticate/Token' , [
            "SubscriberLogin"       => EUDO_SUBSCRIBER_LOGIN,
            "SubscriberPassword"    => EUDO_SUBSCRIBER_PASSWORD,
            "BaseName"              => EUDO_BASE_NAME,
            "UserLogin"             => EUDO_USER_LOGIN,
            "UserPassword"          => EUDO_USER_PASSWORD,
            "UserLang"              => EUDO_USER_LANG,
            "ProductName"           => EUDO_PRODUCT_NAME
        ])['result'];

        if ( $rst['ResultInfos']['Success'] == true )
        {
            //dump( $rst['ResultData']['Token'] );
            $this->headers['x-auth'] = $rst['ResultData']['Token'] ;
        }
    }

    public function getToken()
    {
        return $this->headers['x-auth'] ;
    }

    /* ************************************************** */
    /* ***********    NOTIFICATION DEBUG   ************** */
    /* ************************************************** */

    protected function notif( $type , $var , $message , $array , $table , $id = NULL )
    {
        ob_start();
        print_r( $array );
        //print_r( $var );
        $out1 = ob_get_contents();

        $idt = '' ;
        if ( $id !== NULL ) $idt = "ID : $id - " ;

        $slack   = new \App\Kernel\Utils\Slack;
        $slack->notify( "[$type] Table : $table - " . $idt . $message . "\n\n" . $var['ResultInfos']['ApiMessage'], "" , "logs-ifec" , $var['ResultInfos']['ErrorMessage'] . "\n\n" . $out1 );
    }

    /* ************************************************** */
    /* ****************       ONE      ****************** */
    /* ************************************************** */

    public function one( $tablId , $id )
    {
        $tab     = [];
        $dbValue = [];
        $value   = [];
        $libelle = [];

        $toto = $this->request("get" , 'Search/' . $tablId . '/' . $id );

        $rst = $toto['result'] ;

        if ( isset( $_GET['dump'] ) ) dump( $rst );

        if ( $rst['ResultInfos']['Success'] == true )
        {
            if ( $rst['ResultData']['Rows'][0]['Fields'] )
            {
                foreach( $rst['ResultData']['Rows'][0]['Fields'] as $row )
                {
                    $dbValue[ $row['DescId'] ] = $row['DbValue'] ;
                    $value[ $row['DescId'] ] = $row['Value'] ;

                    if ( isset( $row['FileId'] ) )
                    {
                        $descid = $row['DescId'] / 100;
                        list( $u, $null ) = explode( "." , $descid );
                        $descid = round( $u * 100 , 0 );
                        $value[ intval( $descid ) ] = $row['FileId'] ;
                    }
                }
            }

            if ( $rst['ResultMetaData']['Tables'][0]['Fields'] )
            {
                foreach( $rst['ResultMetaData']['Tables'][0]['Fields'] as $row )
                {
                    $libelle[ $row['DescId'] ] = $row['Label'] ;
                }
            }
        }

        return [
            'remain' => $toto['remain'],
            'result' => $rst['ResultInfos']['Success'],
            'value' => $value,
            'libelle' => $libelle,
            'dbvalue' => $dbValue
        ] ;
    }

    public function wording( $descId , $value = NULL )
    {
        $rst = $this->request("get" , 'Catalog/' . $descId )['result'];

        if ( $rst['ResultInfos']['Success'] == true )
        {
            if ( $rst['ResultData']['CatalogValues'] )
            {
                foreach( $rst['ResultData']['CatalogValues'] as $row )
                {
                    $tab[ $row['DBValue'] ] = $row['DisplayValue'] ;
                }
            }
        }

        if ( $value === NULL )  return $tab ;
        else                    return $tab[ $value ] ;
    }

    /* ************************************************** */
    /* ****************     SEARCH     ****************** */
    /* ************************************************** */

    public function search( $tablId , $params , $order , $fields , $page = 1 )
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
  "RowsPerPage": 50, 
  "NumPage": ' . $page . ',
  "ListCols": [
    ' . $fields . '
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
        $params = json_decode( $str );
        $tab    = [];
        $rst    = $this->request("post" , 'Search/' . $tablId , $params )['result'];

        if ( isset( $_GET['dump'] ) ) dump( $rst );

        if ( $rst['ResultInfos']['Success'] == true )
        {
            if ( $rst['ResultData']['Rows'] )
            {
                foreach( $rst['ResultData']['Rows'] as $row )
                {
                    $tab['data'][ $row['FileId'] ] = $row['FileId'] ;
                }
            }

            $tab['ResultMetaData'] = $rst['ResultMetaData'];

            return $tab ;
        }
        else
        {
            return false ;
        }
    }

    /* ************************************************** */
    /* ****************       ADD      ****************** */
    /* ************************************************** */

    public function add( $tablId , $params , $msg = '' )
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

        $rst = $this->request("post" , 'CUD/' . $tablId , $infos )['result'];

        if ( $rst['ResultInfos']['Success'] == false or ( $rst['ResultInfos']['Success'] == true && DEBUG_EUDONET == true ) )
        {
            $this->notif( "ADD" , $rst , $msg , $params , $tablId );
        }

        return $rst ;
    }

    /* ************************************************** */
    /* ****************     UPDATE     ****************** */
    /* ************************************************** */

    public function update( $tablId , $id , $params , $msg = '' )
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

        $rst = $this->request("post" , 'CUD/' . $tablId . '/' . $id , $infos )['result'];

        if ( $rst['ResultInfos']['Success'] == false or ( $rst['ResultInfos']['Success'] == true && DEBUG_EUDONET == true ) )
        {
            $this->notif( "UPDATE" , $rst , $msg , $params , $tablId , $id );
        }

        if ( $rst['ResultInfos']['Success'] == true )
        {
            return $rst['ResultData'] ;
        }
        else
        {
            return false ;
        }
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

            return [
                'result' => json_decode($authResponse->getBody(), true),
                'remain' => $authResponse->getHeader('X-CALL-REMAIN')[0]
            ];
        } catch ( Exception $e )
        {
            dump( $e );
        }
    }
}