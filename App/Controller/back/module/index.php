<?php

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
])
->via('GET', 'POST');