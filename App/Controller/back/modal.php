<?php

$app->group('/modal', function () use ($app) {
    $app->get('/confirm', function () use ($app) {
        $app->render('modal/confirm.twig.html') ;
    });

    $app->get('/delete', function () use ($app) {
        $app->render('modal/delete.twig.html') ;
    });

    $app->get('/deleteModule', function () use ($app) {
        $app->render('modal/deleteModule.twig.html') ;
    });

    $app->get('/deleteElementMenu', function () use ($app) {
        $app->render('modal/deleteElementMenu.twig.html') ;
    });
});
