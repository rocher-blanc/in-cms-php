<?php

namespace App\Kernel\Middleware\Front;

class Module extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    public function observe()
    {
        if ( $this->app->request->isPost() )
        {
            $key = md5( $this->app->request->post('moduleName') . $this->app->request->post('id_element') );
            if ( $this->app->request->post('moduleAction') == '1' && $this->app->request->post('moduleName') != '' && $this->app->request->post('keyControl') != '' && $key == $this->app->request->post('keyControl') )
            {
                $add = ( $this->app->request->post('id_element') == '-1' ? true : false );
                $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                $rst = $Controller->listenForm( $add );

                if ( $this->app->request->isAjax() )
                {
                    header('Content-Type: application/json');
                    json_encode( $rst );
                    die;
                }
            }
        }
    }
}