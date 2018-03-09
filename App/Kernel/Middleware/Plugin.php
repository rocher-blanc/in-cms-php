<?php

namespace App\Kernel\Middleware;

class Plugin extends \Slim\Middleware
{
    private $arrayPlugin = [] ;
	
	public function __construct( $arrayPlugin = [] )
	{
		$this->arrayPlugin = $arrayPlugin ;
	}

    public function call() 
    {
        $this->app->hook('slim.before', array($this, 'load'));
        $this->next->call();
    }

    public function load()
	{
		if ( !empty( $this->arrayPlugin ) )
		{
			foreach( $this->arrayPlugin as $row )
			{
				if ( method_exists( $row , 'load' ) )       $row->load();
                else                                                    throw new \App\Kernel\Exception('Function "load" is not defined on this plugin "' . get_class( $row ) . '"') ;
			}
		}
    }
}