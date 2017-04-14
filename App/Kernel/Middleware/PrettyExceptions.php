<?php

namespace App\Kernel\Middleware;

class PrettyExceptions extends \Slim\Middleware
{
    protected $settings;

    public function __construct( $settings = [] )
    {
        $this->settings = $settings;
    }

    public function call()
    {
        try {
            $this->next->call();
        } catch (\Exception $e) {
            $log = $this->app->getLog(); // Force Slim to append log to env if not already
            $env = $this->app->environment();

            $env['slim.log'] = $log;
            $env['slim.log']->error($e);

            $this->app->contentType('text/html');
            $this->app->response()->status(500);
            $this->app->response()->body($this->renderBody($env, $e));
        }
    }

    protected function renderBody( &$env , $exception )
    {
        $title = 'JContent Application Error';
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = str_replace(array('#', "\n"), array('<div>#', '</div>'), $exception->getTraceAsString());

        $html = sprintf('<h1>%s</h1>', $title);
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if ($code)
        {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if ($message)
        {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }

        if ($file)
        {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if ($line)
        {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if ($trace)
        {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }

        $this->getSlackNotification( $exception ) ;
        return sprintf("<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>", $title, $html);
    }

    private function getSlackNotification( $e )
    {
        $msg = new \stdClass;
        $msg->color = "#ffab40" ;
        $msg->author_name = "JWeb" ;
        $msg->title = "Erreur sur un projet client - " . $_SERVER['SERVER_NAME'] ;
        $msg->title_link = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REDIRECT_URL'] ;
        $msg->text = "Type: " . get_class($e) . "\n";
        $msg->text.= "Code: " . $e->getCode() . "\n";
        $msg->text.= "Message: " . $e->getMessage() . "\n";
        $msg->text.= "File: " . $e->getFile() . "\n";
        $msg->text.= "Line: " . $e->getLine() ;

        $std = new \stdClass;
        $std->username = "JWeb-Bot" ;
        $std->icon_emoji = ":jweb:" ;
        $std->channel = "#errors" ;
        $std->attachments = [
            $msg
        ];

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
