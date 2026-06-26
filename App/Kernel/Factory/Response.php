<?php

namespace App\Kernel\Factory;

use App\Kernel\AppContext;
use App\Kernel\Config;
use App\Kernel\Exception\NotFoundException;
use App\Kernel\Exception\RedirectException;
use App\Kernel\Factory;

class Response
{
    private function config(): Config
    {
        return Config::getInstance();
    }

    public function Factory(): Factory
    {
        return Factory::getInstance();
    }

    /* ------------------------------------------------------------------ */
    /* Flash                                                               */
    /* ------------------------------------------------------------------ */

    public function flash(string $msg, bool $result = false): void
    {
        AppContext::flash()?->addMessage('__msg',    addslashes($msg));
        AppContext::flash()?->addMessage('__result', (string)(int)$result);
    }

    /* ------------------------------------------------------------------ */
    /* Output HTML                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Écrit du contenu HTML directement dans le buffer de sortie.
     * Utilisé par les controllers qui appellent show() en dehors
     * d'un return explicite — compatible avec OutputBufferingMiddleware.
     */
    public function show(string $msg): void
    {
        echo $msg;
    }

    /* ------------------------------------------------------------------ */
    /* Redirect                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Lève RedirectException, capturée par AbstractMiddleware::process()
     * ou par le ErrorMiddleware Slim 4.
     *
     * C'est le pattern « exception as flow control » utilisé dans Symfony,
     * acceptable ici car les redirections sont des cas de sortie explicites.
     */
    public function redirect(string $url = '', int $status = 302): never
    {
        throw new RedirectException($url === '' ? '/' : $url, $status);
    }

    /* ------------------------------------------------------------------ */
    /* Flash + Redirect                                                    */
    /* ------------------------------------------------------------------ */

    public function flashAndRedirect(string $msg, bool $result = false, string $url = '', bool $admin = true): never
    {
        if ($url === '') {
            $urlTab = $this->Factory()->Url()->cutUrl();
            $url    = 'module/' . ($urlTab[1] ?? '');
        }

        $this->flash($msg, $result);

        $adminUrl = $this->config()->get('admin.url', '');
        $target   = ($admin ? $adminUrl . '/' : '') . ltrim($url, '/');
        $this->redirect($target);
    }

    /* ------------------------------------------------------------------ */
    /* JSON                                                                */
    /* ------------------------------------------------------------------ */

    public function returnJSON(string $msg, bool $result = false, array $extra = []): void
    {
        $this->printJSON(array_merge(['msg' => $msg, 'result' => $result], $extra));
    }

    /**
     * Sérialise en JSON et écrit dans le buffer de sortie.
     * Le Content-Type application/json doit être positionné par le
     * route handler via withHeader() — ou ce helper le fait via header()
     * si le pipeline Slim n'a pas encore envoyé les en-têtes.
     */
    public function printJSON(mixed $data): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($data);

        $array = is_array($data) ? $data : (array) $data;
        if (
            isset($array['msg'], $array['result']) &&
            ($array['noflash'] ?? false) !== true
        ) {
            $this->flash((string)$array['msg'], (bool)$array['result']);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Redirections nommées                                                */
    /* ------------------------------------------------------------------ */

    private function saveUrlDestination(): void
    {
        $url = $this->Factory()->Url()->getFullUrl();
        if ($url !== $this->config()->get('forbidden.url')) {
            $_SESSION['url_destination'] = $url;
        }
    }

    public function redirectUrlDestination(): never
    {
        if (!isset($_SESSION['url_destination'])) {
            $this->redirectHome();
        }

        $saveUrl = $_SESSION['url_destination'];
        unset($_SESSION['url_destination']);
        $this->redirect($saveUrl);
    }

    public function redirectForbidden(): never
    {
        $this->saveUrlDestination();
        $this->redirect($this->config()->get('forbidden.url', '/'));
    }

    public function redirectLogin(bool $save = true): never
    {
        if ($save) {
            $this->saveUrlDestination();
        }
        $this->redirect($this->config()->get('login.url', '/'));
    }

    public function redirectHome(): never
    {
        $this->redirect($this->config()->get('admin.url', '/'));
    }

    /* ------------------------------------------------------------------ */
    /* Erreurs                                                             */
    /* ------------------------------------------------------------------ */

    public function show404(): never
    {
        $body = 'Erreur 404';
        $twig = AppContext::twig();
        if ($twig !== null) {
            $env = $twig->getEnvironment();
            foreach (['errors/404.twig', 'errors/404.twig.html'] as $tpl) {
                if ($env->getLoader()->exists($tpl)) {
                    $body = $env->render($tpl);
                    break;
                }
            }
        }
        throw new NotFoundException($body);
    }

    public function error(string $message, string $type = '404'): never
    {
        if (defined('DEBUG_CMS') && DEBUG_CMS) {
            throw new \App\Kernel\Exception($message);
        }
        die('Une erreur est survenue lors du chargement de la page');
    }
}
