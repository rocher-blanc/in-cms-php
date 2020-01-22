<?php

namespace App\Kernel\Middleware;

class APCCache extends \Slim\Middleware
{
    protected $settings;

    public function __construct( $settings = [] )
    {
        if ( extension_loaded('apc') && ini_get('apc.enabled') )
        {
            $this->settings = array_merge([
                'ttl'            => 300,	// 5 minutes
                'caching_prefix' => 'SlimCache_'
            ], $settings );
        }
        else
        {
            if ( ! DEBUG_CMS )
            {
                return \App\Kernel\Factory::getInstance()->Response()->error('APC not available');
            }
        }
    }

    public function call()
    {
        if ( DEBUG_CMS )
        {
            $this->next->call();
            return;
        }

        $key_name = $this->settings['caching_prefix'] . $this->app->request()->getResourceUri();
        $rsp = $this->app->response();

        // Check cache
        if ( apc_exists( $key_name ) )
        {
            // Return content from cache
            $data = apc_fetch($key_name);
            foreach ( $data['header'] as $key => $value )
            {
                $rsp->headers->set( $key, $value ) ;
            }
            $rsp->body( $data["body"] );
            return;
        }

        // Not in cache. Call controller
        $this->next->call();

        // Cache the content
        if ( ( $rsp->status() == 200 ) && ( $this->settings['ttl'] > 0 ) )
        {
            $header = $rsp->headers->all();
            $data = [
                'header' => $header,
                'body'   => $rsp->body()
            ];
            apc_store( $key_name, $data, $this->settings['ttl'] );
        }
    }
}
