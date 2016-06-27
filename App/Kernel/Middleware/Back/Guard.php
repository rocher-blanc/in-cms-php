<?php

namespace App\Kernel\Middleware\Back;

class Guard extends \Slim\Middleware
{
    public function __construct() {}

    public function call() 
    {
        $this->app->hook('slim.before', array($this, 'check'));
        $this->next->call();
    }
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}

    public function check()
	{
        $check  = array( "ext" , "admin" , "module" ) ;
		$urlTab = $this->Factory()->Url()->cutUrl() ;
        $Guard  = new \App\Kernel\Back\Acl;

        $this->app->view()->appendData([
            'isAdmin' => $Guard->isAdmin()
        ]);
		
		if ( ! empty( $urlTab ) ) 
		{
			if ( in_array( $urlTab[0] , $check ) )
			{
				if ( $urlTab[0] == 'ext' or $urlTab[0] == 'module' )
				{
					if ( $urlTab[0] == 'ext' )			$Guard->setExtension( $urlTab[1] );
					else if ( $urlTab[0] == 'module' )	$Guard->setModule( $urlTab[1] );
					
					$Guard->load();
					
					if ( $Guard->hasRight() == false )
					{
						$this->Factory()->Response()->redirectForbidden() ;
					}
					else
					{
						if ( $urlTab[2] == 'enable' or $urlTab[2] == 'disable' )
						{
							// Validation
							if ( $Guard->checkValidation() == false ) $this->Factory()->Response()->redirectForbidden() ;
						}
						else if ( $urlTab[2] == 'add' )
						{
							// Ajout
							if ( $Guard->checkAdd() == false ) $this->Factory()->Response()->redirectForbidden() ;
						}
						else if ( $urlTab[2] == 'config' )
						{
							// Config
							if ( $Guard->checkConfig() == false ) $this->Factory()->Response()->redirectForbidden() ;
						}
						else if ( $urlTab[2] == 'delete' )
						{
							// Suppression
							if ( $Guard->checkDelete() == false ) $this->Factory()->Response()->redirectForbidden() ;
						}
						else
						{
							// Modification
							if ( $Guard->checkUpdate() == false ) $this->Factory()->Response()->redirectForbidden() ;
						}
					}
				}
				else if ( $urlTab[0] == 'admin' && $Guard->isAdmin() == false )
				{
					$this->Factory()->Response()->redirectForbidden() ;
				}
			}
		}
    }
}