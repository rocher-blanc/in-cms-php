<?php

namespace App\Api;

use App\Kernel\Http;
use App\Kernel\Utils\Slack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Kernel\Front\Data;
use App\Kernel\Container;
//use \Firebase\JWT\JWT;

class Easyletter
{
    /**
     * @var string 
     */
    private $urlApi ;
    private $token = NULL ;
    private $client ;
    private $obj ;
    private $error = '' ;

    public function __construct( $version = null )
    {
        if ( $version == null )
        {
            $version = EL_VERSION;
        }

        if ( $version == 'v2' )
        {
            $this->urlApi = 'https://api.easyletter.fr/v1/' ;
            if( defined('EL_TOKEN') )
            {
                $this->token = EL_TOKEN;
            }
        }
        else
        {
            $this->urlApi = 'https://api.v3.easyletter.fr/v2/' ;
            if( defined('EL_TOKEN_V3') )
            {
                $this->token = EL_TOKEN_V3;
            }
            else if( defined('EL_TOKEN') )
            {
                $this->token = EL_TOKEN;
            }
        }

        if ( $this->token === NULL )
        {
            $this->client = NULL ;

            $this->phpmailer = new \PHPMailer;
            if ( MAIL_SMTP )
            {
                if ( DEBUG_CMS && SMTP_DEBUG ) $this->phpmailer->SMTPDebug = 3;          // Enable verbose debug output

                $this->phpmailer->isSMTP();                            // Set mailer to use SMTP
                $this->phpmailer->Host = MAIL_SMTP_HOST ;              // Specify main and backup SMTP servers
                $this->phpmailer->Port = MAIL_SMTP_PORT ;              // TCP port to connect to
                $this->phpmailer->SMTPAuth = false;                    // Enable SMTP authentication
                $this->phpmailer->SMTPAutoTLS = false;

                if ( defined('MAIL_SMTP_USER') && defined('MAIL_SMTP_PASSWORD') )
                {
                    if ( ! empty( MAIL_SMTP_USER ) && ! empty( MAIL_SMTP_PASSWORD ) )
                    {
                        $this->phpmailer->SMTPAuth = true;                 // Enable SMTP authentication
                        $this->phpmailer->Username = MAIL_SMTP_USER ;      // SMTP username
                        $this->phpmailer->Password = MAIL_SMTP_PASSWORD ;  // SMTP password
                    }
                }

                if ( defined('MAIL_SMTP_SECURE') )
                {
                    if ( ! empty( MAIL_SMTP_SECURE ) )
                    {
                        $this->phpmailer->SMTPSecure = MAIL_SMTP_SECURE ;
                    }
                }
            }
            else
            {
                $this->phpmailer->isSendmail();
            }

            if ( defined('MAIL_FROM_ADDRESS') && defined('MAIL_FROM_NAME') ) $this->phpmailer->setFrom( MAIL_FROM_ADDRESS , MAIL_FROM_NAME );
            if ( defined('MAIL_REPLY_ADDRESS') && defined('MAIL_REPLY_NAME') ) $this->phpmailer->addReplyTo( MAIL_REPLY_ADDRESS , MAIL_REPLY_NAME );

            $this->phpmailer->isHTML(true);                            // Set email format to HTML
            $this->phpmailer->CharSet = 'UTF-8';
        }
        else
        {
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
        Slack::notify( "Erreur sur un projet client - " . $_SERVER['SERVER_NAME'] , 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL'] , "logs-errors" , $msg );
    }

    public function getError()
    {
        return $this->error ;
    }

    /**
     * @param string $keyAutomation
     * @param string $email
     * @param array $data
     * @param null $idSender
     * @param null $subject
     * @param array $attachments
     * @return false
     */
    public function automotion(string $keyAutomation , string $email , array $data = [] , $idSender = NULL , $subject = null , $attachments = [] )
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

                    if ( ! empty( $attachments ) )
                    {
                        foreach ( $attachments as $attachment )
                        {
                            $this->phpmailer->addAttachment($attachment);
                        }
                    }

                    $this->phpmailer->send();

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

                        'subject' => ( $subject === null ? $EdAutomationModel->get('subject') : $subject ),
                        'senderName' => $Sender->get('name'),
                        'senderEmail' => $Sender->get('email'),
                        'returnPathEmail' => $Sender->get('email_response'),

                        'recipient' => Http::getInstance()->getUrl() . "/email/automation/recipient/" . $AutomationHistory->get('id'),
                        'content' => Http::getInstance()->getUrl() . "/email/automation/template/" . $EdAutomation->get('id') . "/" . $AutomationHistory->get('id'),
                    ]);


                    if ( $response !== false )
                    {
                        $AutomationHistory->set('version' , EL_VERSION );
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

    public function newsletter( int $idNewsletter , $url_recipient = null , $url_content = null )
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

            'recipient' => ( $url_recipient === null ? Http::getInstance()->getUrl() . "/email/newsletter/recipient/" . $NL->get('id') : $url_recipient ),
            'content' => ( $url_content === null ? Http::getInstance()->getUrl() . "/email/newsletter/template/" . $NL->get('template') : $url_content ),
        ];

        $response = $this->request( $params );

        if ( $response !== false )
        {
            $NL->set('id_easyletter' , $response );
            $NL->set('version' , EL_VERSION );
            $NL->save();

            return true ;
        }
        else
        {
            ob_start();
            echo json_encode( $params , JSON_PRETTY_PRINT );
            $c = ob_get_clean();

            \App\Kernel\Utils\Slack::notificationInstall( "easyDOOR" , "" , "test" , $c );

            return false ;
        }
    }

    private function request( array $data )
    {
        $response = $this->response( $this->client->post('campaign', [
            'body' => json_encode( $data )
        ]) ) ;

        if ( $response !== false )  return $response['id'] ;
        else                        return $response ;
    }

    public function delete( int $id )
    {
        return $this->response( $this->client->delete('campaign/' . $id ) ) ;
    }

    public function cancel( int $id )
    {
        return $this->response( $this->client->put('campaign/' . $id . '/cancel') ) ;
    }

    public function stats( $id )
    {
        if ( is_array( $id ) )
        {
            try {
                return $this->response( $this->client->get('campaign/statsLight', [
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
            return $this->response( $this->client->get('campaign/stats/' . $id ) ) ;
        }
    }

    public function downloadStats( $id , $format )
    {
        if ( ! empty( $id ) )
        {
            try {
                $response = $this->client->get('campaign/export/' . $format . '/' . $id) ;

                if ( $response->getStatusCode() == 200 )
                {
                    return $response->getBody()->getContents() ;
                }
                else
                {
                    return $this->response( $response ) ;
                }
            }
            catch ( \ErrorException $e ) {
                return false ;
            }
        }
        else
        {
            return false ;
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