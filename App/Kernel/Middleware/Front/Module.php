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
        if ( $this->app->request->isPost() && $this->app->request->post('keyControl') != '' && $this->app->request->post('moduleName') != '' && $this->app->request->post('keyControl') != '' )
        {
            $rst = [];
            $key = md5( $this->app->request->post('moduleName') . $this->app->request->post('id_element') );

            if ( $key != $this->app->request->post('keyControl') )
            {
                $rst = $this->error("La clef du module est incorrect" );
            }
            else
            {
                if ( $this->app->request->post('show') != '' )
                {
                    $action = 'showIf';
                }
                else if ( $this->app->request->post('delete') != '' )
                {
                    $action = 'delete';
                }

                switch( $action )
                {
                    // SHOW IF
                    case "showIf" :
                        $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                        $rst = $Controller->getShow();
                        break;

                    // DELETE
                    case "delete" :
                        $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                        $Controller->setId( $this->app->request->post('id_element') );
                        $rst = $Controller->delete();
                        break;

                    // ADD/UPDATE
                    default :
                        $add = ( $this->app->request->post('id_element') == '-1' ? true : false );
                        $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                        if ( ! $add ) $Controller->setId( $this->app->request->post('id_element') );
                        $rst = $Controller->listenForm( $add );
                    break;
                }
            }

            if ( ! empty( $rst ) )
            {
                if ( $this->app->request->isAjax() )
                {
                    $this->app->contentType('application/json');
                    //$this->app->response()->body( json_encode( $rst ) );
                    echo json_encode( $rst ) ;
                    die;
                }
            }
        }

    }
}