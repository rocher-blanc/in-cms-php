<?php

$app->map('/:entity(/:action(/:id(/:token(/:lang))))', function ( $entity , $action = "index" , $id = NULL , $token = NULL , $lang = NULL )
{
    if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $entity ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . ucfirst( $entity );
    else																			 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

    $Controller = new $ControllerClass;

	$Controller->setEntityName( $entity );
	$Controller->setActionName( $action );
	$Controller->setId( $id );
	$Controller->setToken( $token );
	$Controller->setLang( $lang );
	$Controller->execute();

})->conditions(	array(
					'entity' => '[_a-zA-Z0-9]+', 
					'action' => '[_a-zA-Z0-9]+', 
					'id' => '[0-9]+', 
					'token' => '[a-zA-Z0-9]+', 
					'lang' => '[0-9]+'
				)
			)
  ->via('GET', 'POST');