<?php

namespace App\Api;

use App\Kernel\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Kernel\Front\Data;
use App\Kernel\Container;
//use \Firebase\JWT\JWT;

class Easyletter
{
    private $urlApi = 'https://api.easyletter.fr/' ;
    private $token = NULL ;
    private $client ;
    private $obj ;
    private $error = '' ;

    public function __construct()
    {
    	if( defined('EL_TOKEN') )
		{
			$this->token = EL_TOKEN;
		}

        if ( $this->token === NULL )
        {
            $this->client = NULL ;

            $this->phpmailer = new \PHPMailer;
            if ( MAIL_SMTP )
            {
                if ( DEBUG && SMTP_DEBUG ) $this->phpmailer->SMTPDebug = 3;          // Enable verbose debug output

                $this->phpmailer->isSMTP();                            // Set mailer to use SMTP
                $this->phpmailer->Host         = MAIL_SMTP_HOST ;      // Specify main and backup SMTP servers
                $this->phpmailer->SMTPAuth     = true;                 // Enable SMTP authentication
                $this->phpmailer->Username     = MAIL_SMTP_USER ;      // SMTP username
                $this->phpmailer->Password     = MAIL_SMTP_PASSWORD ;  // SMTP password
                $this->phpmailer->SMTPSecure   = MAIL_SMTP_SECURE ;    // Enable TLS encryption, `ssl` also accepted
                $this->phpmailer->Port         = MAIL_SMTP_PORT ;      // TCP port to connect to
            }
            else
            {
                $this->phpmailer->isSendmail();
            }

            $this->phpmailer->isHTML(true);                            // Set email format to HTML
            $this->phpmailer->CharSet = 'UTF-8';
        }
        else
        {
            /*
             * $_SERVER['SERVER_NAME']
             * $jwt = JWT::encode($token, $key);
             */


            $this->client = new Client([
                'base_uri' => $this->urlApi,
                'http_errors' => false,
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

                if ( $this->client === NULL )
                {
                    $this->phpmailer->setFrom( $Sender->get('email') , $Sender->get('name') );
                    $this->phpmailer->addReplyTo( $Sender->get('email_response') , $Sender->get('name') );
                    $this->phpmailer->Subject = html_entity_decode( $EdAutomationModel->get('subject') ) ;
                    $this->phpmailer->addAddress( $email );

                    $html = $EdAutomation->get('html');

                    foreach( $tab[ $email ] as $key => $value )
                    {
                        $html = str_replace( '[' . $key . ']' , $value , $html ) ;
                    }

                    $this->phpmailer->AltBody = strip_tags( $html ) ;
                    $this->phpmailer->Body = $html ;
                    $ret = $this->phpmailer->send();

                }
                else
                {
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

                        'recipient' => Http::getInstance()->getUrl() . "/email/automation/recipient/" . $AutomationHistory->get('id'),
                        'content' => Http::getInstance()->getUrl() . "/email/automation/template/" . $EdAutomation->get('id'),
                    ]);

                    if ( $response !== false )
                    {
                        $AutomationHistory->set('id_easyletter' , $response );
                        $AutomationHistory->save();
                    }
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

    public function test( string $email , int $id , string $module )
    {
        $Sender = new Data('NewsletterSender');
        $Sender->find([
            "default" => 1
        ]);

        $Model = new Data( $module );
        $rstModel = $Model->find($id);

        if ( $rstModel )
        {
            if ( $this->client === NULL )
            {
                $this->phpmailer->setFrom( $Sender->get('email') , $Sender->get('name') );
                $this->phpmailer->addReplyTo( $Sender->get('email_response') , $Sender->get('name') );
                $this->phpmailer->Subject = html_entity_decode("Test - " . $Model->get('name') ) ;
                $this->phpmailer->addAddress( $email );

                $html = $Model->get('html');

                $this->phpmailer->AltBody = strip_tags( $html ) ;
                $this->phpmailer->Body = $html ;
                return $this->phpmailer->send();
            }
            else
            {
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

                    'subject' => "Test email",
                    'senderName' => $Sender->get('name'),
                    'senderEmail' => $Sender->get('email'),
                    'returnPathEmail' => $Sender->get('email_response'),

                    'recipient' => Http::getInstance()->getUrl() . "/email/test/recipient/" . $email,
                    'content' => Http::getInstance()->getUrl() . "/email/test/template/" . $module . "/" . $id ,
                ]);

                if ( $response !== false )
                {
                    return true ;
                }
            }
        }
        else
        {
            $this->setError("Aucun automation n'est disponible dans la catégorie \"" . $Model->get('name') . "\"") ;
        }

        return false ;
    }

    public function newsletter( int $idNewsletter )
    {
        $NL = new Data('NewsletterCampaign');
        $NL->find( $idNewsletter );

        $Sender = new Data('NewsletterSender');
        $Sender->find( $NL->get('sender') );

        $params = [
            'msgType' => '0', // 0 = HTML ; 1 = TXT ; 2 = SMS
            'msgSMS' => "",
            'urlUnsubscribe' => "",

            'txtOnlineViewTag' => "Cliquez ici pour visualiser cet email dans votre navigateur",
            'txtHtmlUnsubscribeTag' => "",
            'txtSendToAFriendTag' => "",

            'schedule' => '1',
            'sendingRate' => '0',
            'transactional' => '0',

            'dateTimeUTC' => $NL->get('date'),
            'subject' => $NL->get('subject'),
            'senderName' => $Sender->get('name'),
            'senderEmail' => $Sender->get('email'),
            'returnPathEmail' => $Sender->get('email_response'),

            'recipient' => Http::getInstance()->getUrl() . "/email/newsletter/recipient/" . $NL->get('id'),
            'content' => Http::getInstance()->getUrl() . "/email/newsletter/template/" . $NL->get('template'),
        ];

        $response = $this->request( $params );

        if ( $response !== false )
        {
            $NL->set('id_easyletter' , $response );
            $NL->set('statut' , 2 );
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

    public function delete( int $id )
    {
        return $this->response( $this->client->delete('/v1/campaign/' . $id ) ) ;
    }

    public function cancel( int $id )
    {
        return $this->response( $this->client->put('/v1/campaign/' . $id . '/cancel') ) ;
    }

    public function stats( $id )
    {
        if ( is_array( $id ) )
        {
            try {
                return $this->response( $this->client->get('v1/campaign/statsLight', [
                    'body' => json_encode([
                        'id' => $id
                    ])
                ]) ) ;
            }
            catch ( \ErrorException $e ) {
                return false ;
            }
        }
        else
        {
            return $this->response( $this->client->get('v1/campaign/stats/' . $id ) ) ;
        }
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