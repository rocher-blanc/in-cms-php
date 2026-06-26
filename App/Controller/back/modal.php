<?php

$app->group('/modal', function (\Slim\Routing\RouteCollectorProxy $app) {
    $app->get('/help', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        return \App\Kernel\AppContext::twig()->render($res, 'modal/help.twig');
    });

    $app->get('/confirm', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        return \App\Kernel\AppContext::twig()->render($res, 'modal/confirm.twig.html');
    });

    $app->get('/delete', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        return \App\Kernel\AppContext::twig()->render($res, 'modal/delete.twig.html');
    });

    $app->get('/deleteModule', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        return \App\Kernel\AppContext::twig()->render($res, 'modal/deleteModule.twig.html');
    });

    $app->get('/deleteElementMenu', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        return \App\Kernel\AppContext::twig()->render($res, 'modal/deleteElementMenu.twig.html');
    });
});
