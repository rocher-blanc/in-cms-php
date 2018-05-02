<?php

$app->map('/:entity/:action/id/:id', function ( $entity , $action , $id )
{
    $Controller = \App\Kernel\Container::getInstance()->module( $entity )->getController( true );
    $Controller->setEntityName( $entity );
    $Controller->setActionName( $action );
    $Controller->setIdParent([]);
    $Controller->setId( $id );
    $Controller->execute();

})->conditions([
    'id' => '[0-9]+',
    'action' => '[_a-zA-Z0-9]+',
    'entity' => '[_a-zA-Z0-9]+'
])->via('GET', 'POST');

$app->map('/:entity/:action(/:parent+(/id/:id))', function ( $entity , $action , $parent = [] , $id = NULL )
{
    $Controller = \App\Kernel\Container::getInstance()->module( $entity )->getController( true );
    $Controller->setEntityName( $entity );
    $Controller->setActionName( $action );
    $Controller->setIdParent( $parent );
    $Controller->setId( $id );
    $Controller->execute();

})->conditions([
    'action' => '[_a-zA-Z0-9]+',
    'entity' => '[_a-zA-Z0-9]+'
])->via('GET', 'POST');

$app->map('/:entity(/:action(/:id(/:token(/:lang))))', function ( $entity , $action = "index" , $id = NULL , $token = NULL , $lang = NULL )
{
    $Controller = \App\Kernel\Container::getInstance()->module( $entity )->getController( true );
    $Controller->setEntityName( $entity );
    $Controller->setActionName( $action );
    $Controller->setId( $id );
    $Controller->setToken( $token );
    $Controller->setLang( $lang );
    $Controller->execute();

})->conditions([
    'entity' => '[_a-zA-Z0-9]+',
    'action' => '[_a-zA-Z0-9]+',
    'id' => '[0-9]+',
    'token' => '[a-zA-Z0-9]+',
    'lang' => '[0-9]+'
])->via('GET', 'POST');

