<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Route 1 : entity/action/id/:id[/token]
$app->map(['GET', 'POST'], '/{entity:[_a-zA-Z0-9]+}/{action:[_a-zA-Z0-9]+}/id/{id:[0-9]+}[/{token:[a-zA-Z0-9]+}]',
    function (Request $req, Response $res, array $args): Response {
        $Controller = \App\Kernel\Container::getInstance()->module($args['entity'])->getController(true);
        $Controller->setEntityName($args['entity']);
        $Controller->setActionName($args['action']);
        $Controller->setIdParent([]);
        $Controller->setToken($args['token'] ?? null);
        $Controller->setId($args['id']);
        $Controller->execute();
        return $res;
    }
);

// Route 2 : entity/action[/params...] (parent + optional id + token)
$app->map(['GET', 'POST'], '/{entity:[_a-zA-Z0-9]+}/{action:[_a-zA-Z0-9]+}[/{params:.+}]',
    function (Request $req, Response $res, array $args): Response {
        $rawParams = $args['params'] ?? '';
        $id        = null;
        $token     = null;
        $parent    = [];

        // Extraire /id/:id[/:token] depuis $rawParams si présent
        if (preg_match('#^(.*)/id/(\d+)(?:/([a-zA-Z0-9]+))?$#', $rawParams, $m)) {
            $parent = array_filter(explode('/', $m[1]), fn($s) => $s !== '');
            $id     = $m[2];
            $token  = $m[3] ?? null;
        } else {
            $parent = array_filter(explode('/', $rawParams), fn($s) => $s !== '');
        }

        $Controller = \App\Kernel\Container::getInstance()->module($args['entity'])->getController(true);
        $Controller->setEntityName($args['entity']);
        $Controller->setActionName($args['action']);
        $Controller->setIdParent(array_values($parent));
        $Controller->setToken($token);
        $Controller->setId($id);
        $Controller->execute();
        return $res;
    }
);

// Route 3 : entity/action/id/:id/depedency/:module[/:element]
$app->map(['GET', 'POST'], '/{entity:[_a-zA-Z0-9]+}/{action:[_a-zA-Z0-9]+}/id/{id:[0-9]+}/depedency/{module:[0-9]+}[/{element:[0-9]+}]',
    function (Request $req, Response $res, array $args): Response {
        $Controller = \App\Kernel\Container::getInstance()->module($args['entity'])->getController(true);
        $Controller->setEntityName($args['entity']);
        $Controller->setActionName($args['action']);
        $Controller->setDepedencyModule($args['module']);
        $Controller->setDepedencyElement($args['element'] ?? null);
        $Controller->setId($args['id']);
        $Controller->execute();
        return $res;
    }
);

// Route 4 : entity[/action[/id[/token[/lang]]]] (route générale, en dernier)
$app->map(['GET', 'POST'], '/{entity:[_a-zA-Z0-9]+}[/{action:[_a-zA-Z0-9]+}[/{id:[0-9]+}[/{token:[a-zA-Z0-9]+}[/{lang:[0-9]+}]]]]',
    function (Request $req, Response $res, array $args): Response {
        $Controller = \App\Kernel\Container::getInstance()->module($args['entity'])->getController(true);
        $Controller->setEntityName($args['entity']);
        $Controller->setActionName($args['action'] ?? 'index');
        $Controller->setId($args['id'] ?? null);
        $Controller->setToken($args['token'] ?? null);
        $Controller->setLang($args['lang'] ?? null);
        $Controller->execute();
        return $res;
    }
);
