<?php

namespace App\Kernel\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware de gestion d'exceptions — PSR-15.
 *
 * Intercepte toute exception non catchée par le pipeline Slim 4,
 * génère une page d'erreur HTML et notifie via Slack.
 */
class PrettyExceptions extends AbstractMiddleware
{
    protected array $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\App\Kernel\Exception\RedirectException $e) {
            // Laisser remonter les redirections intentionnelles
            throw $e;
        } catch (\Throwable $e) {
            error_log((string) $e);

            ob_start();
            \App\Kernel\Debug::view();
            $debugOutput = ob_get_clean();

            $this->getSlackNotification($e);

            $html = $debugOutput . $this->renderBody($e);

            $factory  = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $factory->createResponse(500)
                ->withHeader('Content-Type', 'text/html; charset=utf-8');
            $response->getBody()->write($html);
            return $response;
        }
    }

    protected function renderBody(\Throwable $exception): string
    {
        $title   = 'easyDOOR Error';
        $code    = $exception->getCode();
        $message = $exception->getMessage();
        $file    = $exception->getFile();
        $line    = $exception->getLine();
        $trace   = str_replace(['#', "\n"], ['<div>#', '</div>'], $exception->getTraceAsString());

        $html  = sprintf('<h1>%s</h1>', $title);
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        if ($code)    $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        if ($message) $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlspecialchars($message));
        if ($file)    $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        if ($line)    $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        if ($trace)   $html .= '<h2>Trace</h2>' . sprintf('<pre>%s</pre>', $trace);

        return sprintf(
            '<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>',
            $title,
            $html
        );
    }

    private function getSlackNotification(\Throwable $e): void
    {
        $text  = "Type: "    . get_class($e) . "\n";
        $text .= "Code: "    . $e->getCode() . "\n";
        $text .= "Message: " . $e->getMessage() . "\n";
        $text .= "File: "    . $e->getFile() . "\n";
        $text .= "Line: "    . $e->getLine();

        ob_start();
        print_r($_SERVER);
        $text .= ob_get_clean();

        try {
            $Slack = new \App\Kernel\Utils\Slack;
            $Slack->setText($text);
            $redirectUrl = isset($_SERVER['REDIRECT_URL'])
                ? $_SERVER['REDIRECT_URL']
                : ($_SERVER['REQUEST_URI'] ?? '/');
            $Slack->setTitle("Erreur sur un projet client - " . ($_SERVER['SERVER_NAME'] ?? 'unknown'));
            $Slack->setTitleLink('http://' . ($_SERVER['SERVER_NAME'] ?? 'unknown') . $redirectUrl);
            $Slack->setChannel("errors");
            $Slack->notification();
        } catch (\Throwable $slackError) {
            // Ne pas lever d'exception si Slack échoue
        }
    }
}
