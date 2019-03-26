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

            ob_start();
            \App\Kernel\Debug::view();
            $out1 = ob_get_contents();
            ob_end_clean();

            $this->app->contentType('text/html');
            $this->app->response()->status(500);
            $this->app->response()->body( $out1 . $this->renderBody($env, $e) );
        }
    }

    protected function renderBody( &$env , $exception )
    {
        $title = 'easyDOOR Error';
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
        $text = "Type: " . get_class($e) . "\n";
        $text.= "Code: " . $e->getCode() . "\n";
        $text.= "Message: " . $e->getMessage() . "\n";
        $text.= "File: " . $e->getFile() . "\n";
        $text.= "Line: " . $e->getLine() ;

        $Slack = new \App\Kernel\Utils\Slack;
        $Slack->setText( $text );
        $Slack->setTitle( "Erreur sur un projet client - " . $_SERVER['SERVER_NAME'] );
        $Slack->setTitleLink( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL'] );
        $Slack->setChannel("errors");
        $Slack->notification();
    }
}
