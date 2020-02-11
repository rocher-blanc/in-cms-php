<?php

namespace App\Kernel\Common;

class Mail
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $obj = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->obj = new \PHPMailer;

        if ( defined('EL_TOKEN' ) )
        {

        }
        else if ( MAIL_SMTP )
        {
            if ( DEBUG_CMS && SMTP_DEBUG ) $this->obj->SMTPDebug = 3;          // Enable verbose debug output

            $this->obj->isSMTP();                            // Set mailer to use SMTP
            $this->obj->Host = MAIL_SMTP_HOST ;      // Specify main and backup SMTP servers

            if ( defined('MAIL_SMTP_USER') && defined('MAIL_SMTP_PASSWORD') )
            {
                $this->obj->SMTPAuth = true;                 // Enable SMTP authentication
                $this->obj->Username = MAIL_SMTP_USER ;      // SMTP username
                $this->obj->Password = MAIL_SMTP_PASSWORD ;  // SMTP password
            }
            else
            {
                $this->obj->SMTPAuth = false;                 // Enable SMTP authentication
            }

            $this->obj->Port = MAIL_SMTP_PORT ;      // TCP port to connect to

            if ( defined('MAIL_SMTP_SECURE') ) $this->obj->SMTPSecure = MAIL_SMTP_SECURE ;    // Enable TLS encryption, `ssl` also accepted
        }
        else
        {
            $this->obj->isSendmail();
        }

        if ( defined('MAIL_FROM_ADDRESS') && defined('MAIL_FROM_NAME') ) $this->obj->setFrom( MAIL_FROM_ADDRESS , MAIL_FROM_NAME );
        if ( defined('MAIL_REPLY_ADDRESS') && defined('MAIL_REPLY_NAME') ) $this->obj->addReplyTo( MAIL_REPLY_ADDRESS , MAIL_REPLY_NAME );

        $this->obj->isHTML(true);                            // Set email format to HTML
        $this->obj->CharSet = 'UTF-8';
    }

    /* ************************************************** */
    /* *****************    TOOLS     ******************* */
    /* ************************************************** */

    protected function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    public function getError()
    {
        return $this->obj->ErrorInfo ;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    public function setAltBody( $var )
    {
        $this->obj->AltBody = $var ;
        return $this ;
    }

    public function setBody( $var )
    {
        $this->obj->Body = $var ;
        $this->setAltBody( strip_tags( $var ) );
        return $this ;
    }

    public function setSubject( $var )
    {
        $this->obj->Subject = html_entity_decode( $var ) ;
        return $this ;
    }

    public function from( $mail , $name )
    {
        $this->obj->setFrom( $mail , html_entity_decode( $name ) );
        return $this ;
    }

    public function replyTo( $mail , $name )
    {
        $this->obj->addReplyTo( $mail , html_entity_decode( $name ) );
        return $this ;
    }

    public function attachment( $file , $name = '' )
    {
        if ( $name == '' )  $this->obj->addAttachment( $file );
        else                $this->obj->addAttachment( $file , $name );

        return $this ;
    }

    public function add( $var )
    {
        $this->obj->addAddress( $var );
        return $this ;
    }

    public function cc( $var )
    {
        $this->obj->addCC( $var );
        return $this ;
    }

    public function bcc( $var )
    {
        $this->obj->addBCC( $var );
        return $this ;
    }

    public function parse( $template , $var )
    {
        $this->setBody( $this->CMS()->view()->fetch( 'mail/' . $template . '.twig.html' , $var ) ) ;
        return $this ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function send()
    {
        $ret = $this->obj->send();
        if ( ! $ret && SMTP_DEBUG && DEBUG_CMS ) \App\Kernel\Debug::dump( $this->getError() );
        return $ret ;
    }
}