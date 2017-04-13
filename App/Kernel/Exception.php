<?php

namespace App\Kernel;

class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $this->getSlackNotification( $message );
        parent::__construct($message, $code, $previous);
    }

	private function getSlackNotification( $message )
    {
        $data_string = '{
        "username": "JWeb-Bot",
        "icon_emoji": ":jweb:",
        "channel": "#errors",
        "attachments": [
        {
            "color": "#ffab40",
            "author_name": "JContent",
            "title": "Erreur sur un projet client - ' . $_SERVER['SERVER_NAME'] . '",
            "title_link": "http://' . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REDIRECT_URL'] . '",
            "text": "' . addslashes( $message ) . '"
                }
    ]
}';

        $ch = curl_init('https://hooks.slack.com/services/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');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
    }
}