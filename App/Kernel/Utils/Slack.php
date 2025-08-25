<?php

namespace App\Kernel\Utils;

class Slack
{
    protected $color = SLACK_COLOR ;
    protected $username = SLACK_USERNAME ;
    protected $author_name = SLACK_AUTHORNAME ;
    protected $emoji = SLACK_EMOJI ;

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

    public function setAuthorName($author_name)
    {
        $this->author_name = $author_name;
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
        return $this->hexToDecimal($this->color);
    }

    protected function getUsername()
    {
        return $this->username ;
    }

    protected function getEmoji()
    {
        return $this->emoji ;
    }

    public function getAuthorName()
    {
        return $this->author_name;
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

    public static function notify( $title, $link , $channel , $text )
    {
        $Slack = new Slack;
        $Slack->setText( $text );
        $Slack->setTitle( $title );
        $Slack->setTitleLink( $link );
        $Slack->setChannel( $channel );
        $Slack->notification();
    }

    public function notifyMe( $title, $link , $channel , $text )
    {
        $this->setText( $text );
        $this->setTitle( $title );
        $this->setTitleLink( $link );
        $this->setChannel( $channel );
        $this->notification();
    }

    public function notification() {
        if( !defined('DISCORD_WEBHOOK') ) return;

        $payload = [
            "content" => "",
            "embeds"  => [
                [
                    "title"       => $this->getTitle(),
                    "description" => $this->getText(),
                    "color"       => $this->getColor(),
                ]
            ]
        ];

        $data_string = json_encode( $payload );

        $ch = curl_init( DISCORD_WEBHOOK );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
        ]);

        curl_exec($ch);
        curl_close($ch);

    }

    private function hexToDecimal($hexColor) {
        // Supprimer le # et convertir en décimal
        $hexColor = ltrim($hexColor, '#');
        return hexdec($hexColor);
    }
}