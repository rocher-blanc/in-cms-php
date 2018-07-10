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

    private function error( $msg )
    {
        return [
            'error' => true,
            'result' => false,
            'msg' => $msg,
        ];
    }

    public function observe()
    {
        if ( $this->app->request->isPost() )
        {
            $rst = [];
            $key = md5( $this->app->request->post('moduleName') . $this->app->request->post('id_element') );

            if ( $this->app->request->post('moduleAction') != '1' )
            {
                $rst = $this->error("L'action du module est inconnue" );
            }
            else if ( $this->app->request->post('moduleName') == '' )
            {
                $rst = $this->error("Le module est inconnu" );
            }
            else if ( $this->app->request->post('keyControl') == '' )
            {
                $rst = $this->error("Le clef module est requise" );
            }
            else if ( $key != $this->app->request->post('keyControl') )
            {
                $rst = $this->error("La clef du module est incorrect" );
            }
            else
            {
                $add = ( $this->app->request->post('id_element') == '-1' ? true : false );
                $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                $rst = $Controller->listenForm( $add );
            }

            if ( !empty( $rst ) )
            {
                if ( $this->app->request->isAjax() )
                {
                    header('Content-Type: application/json');
                    echo json_encode( $rst );
                    die;
                }
            }
        }
    }
}