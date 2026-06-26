<?php

namespace App\Kernel\Back;

use App\Kernel\AppContext;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Router
{
    private array $_url = [];
    private array $folders = [];

    public function __construct(array $folders = [])
    {
        $this->folders = $folders;
    }

    /* -------------------------------------------------- */
    /* Getters                                            */
    /* -------------------------------------------------- */

    private function getApp(): App
    {
        return AppContext::slimApp();
    }

    private function Factory(): \App\Kernel\Factory
    {
        return \App\Kernel\Factory::getInstance();
    }

    public function getUrl(): array
    {
        return $this->_url;
    }

    /* -------------------------------------------------- */
    /* URL                                                */
    /* -------------------------------------------------- */

    private function cutUrl(): void
    {
        $this->_url = $this->Factory()->Url()->cutUrl();
    }

    /* -------------------------------------------------- */
    /* Chargement des routes                              */
    /* -------------------------------------------------- */

    public function load(): void
    {
        $this->cutUrl();

        $app    = $this->getApp();
        $urlTab = $this->getUrl();

        if (empty($urlTab)) {
            // INDEX — pas de groupe, require direct
            $file = $this->findFile('index.php');
            if ($file !== null) {
                require $file;
            }
            return;
        }

        switch ($urlTab[0]) {
            case 'module':
                $file    = $this->findFile($urlTab[0] . '/index.php');
                $folders = $this->folders;
                if ($file !== null) {
                    $app->group('/' . $urlTab[0], function (RouteCollectorProxy $app) use ($file, $folders) {
                        require $file;
                    });
                }
                break;

            case 'ext':
            case 'admin':
                $file    = isset($urlTab[1]) ? $this->findFile($urlTab[0] . '/' . $urlTab[1] . '.php') : null;
                $folders = $this->folders;
                if ($file !== null) {
                    $app->group('/' . $urlTab[0], function (RouteCollectorProxy $app) use ($file, $folders) {
                        require $file;
                    });
                }
                break;

            default:
                $group = false;
                $file  = null;

                // Chercher d'abord un fichier dans un sous-dossier ($urlTab[0]/$urlTab[1].php)
                if (isset($urlTab[1])) {
                    $found = $this->findFile($urlTab[0] . '/' . $urlTab[1] . '.php');
                    if ($found !== null) {
                        $file  = $found;
                        $group = true;
                    }
                }

                // Sinon un fichier à plat ($urlTab[0].php)
                if ($file === null) {
                    $file = $this->findFile($urlTab[0] . '.php');
                }

                if ($file !== null) {
                    if ($group) {
                        $app->group('/' . $urlTab[0], function (RouteCollectorProxy $app) use ($file) {
                            require $file;
                        });
                    } else {
                        require $file;
                    }
                }
                break;
        }
    }

    /* -------------------------------------------------- */
    /* Helpers                                            */
    /* -------------------------------------------------- */

    private function findFile(string $relative): ?string
    {
        foreach ($this->folders as $folder) {
            $path = $folder . '/' . $relative;
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}
