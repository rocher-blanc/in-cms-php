<?php

namespace App\Kernel\Utils;

class Slack
{
    protected $color = "#ffab40" ;
    protected $username = "JWeb-Bot" ;
    protected $emoji = ":jweb:" ;

    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setText( $var )
    {
        $this->text = $var ;
    }

    public function setTitle( $var )
    {
        $this->title = $var ;
    }

    public function setTitleLink( $var )
    {
        $this->title_link = $var ;
    }

    public function setChannel( $var )
    {
        $this->channel = $var;
    }

    public function setColor( $var )
    {
        $this->color = $var;
    }

    public function setUsername( $var )
    {
        $this->username = $var;
    }

    public function setEmoji( $var )
    {
        $this->emoji = $var;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getText()
    {
        return $this->text ;
    }

    protected function getTitle()
    {
        return $this->title ;
    }

    protected function getTitleLink()
    {
        return $this->title_link ;
    }

    protected function getChannel()
    {
        return "#" . $this->channel ;
    }

    protected function getColor()
    {
        return $this->color ;
    }

    protected function getUsername()
    {
        return $this->username ;
    }

    protected function getEmoji()
    {
        return $this->emoji ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public static function notificationInstall( $title, $link , $channel , $text )
    {
        $Slack = new Slack;
        $Slack->setText( $text );
        $Slack->setTitle( $title );
        $Slack->setTitleLink( $link );
        $Slack->setChannel( $channel );
        $Slack->notification();
    }

    public function notification()
    {
        $msg = new \stdClass;
        $msg->color = $this->getColor() ;
        $msg->author_name = "JWeb" ;
        $msg->title = $this->getTitle() ;
        $msg->title_link = $this->getTitleLink() ;
        $msg->text = $this->getText() ;

        $std = new \stdClass;
        $std->username = $this->getUsername() ;
        $std->icon_emoji = $this->getEmoji() ;
        $std->channel = $this->getChannel() ;
        $std->attachments = [ $msg ];

        $data_string = json_encode( $std );

        $ch = curl_init('https://hooks.slack.com/services/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');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        curl_close($ch);
    }
}