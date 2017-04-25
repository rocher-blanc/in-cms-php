<?php

namespace App\Kernel\Middleware\Back;

class Auth extends \Slim\Middleware
{
    public function __construct() {}

    public function call() 
    {
        $this->app->hook('slim.before', array($this, 'observe'));
        $this->next->call();
    }
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}

    public function observe()
	{
        $this->checkIPAccess() ;

        if ( ! $this->isLogged() )
		{
			if ( ! $this->isOnLoginPage() )
			{
                $this->Factory()->Response()->redirectLogin() ;
			}
			else if ( $this->isOnLoginPage() )
			{
				if ( $this->app->request->isPost() )
				{
					if ( $this->checkAuth() )
					{
						$this->Factory()->Response()->redirectUrlDestination() ;
					}
				}
			}
		}
		else if ( $this->isLogged() )
		{
			if ( $this->isOnLogoutPage() or $this->isBadIp() )
			{
				$this->logout() ;
				$this->Factory()->Response()->redirectLogin( false ) ;
			}
			else if ( $this->isOnLoginPage() )
			{
				$this->Factory()->Response()->redirectHome() ;
			}
			else if ( ! $this->isOnLogoutPage() )
			{
				$this->pushData() ;
			}
		}
    }

    private function checkIPAccess()
    {
        $security_lock_ip = \DB::for_table('param')
            ->where_equal('param_key', 'security_lock_ip')
            ->find_one();

        if ( $security_lock_ip->param_value == 1 )
        {
            $auth = false ;
            $security_list_ip = \DB::for_table('param')
                ->where_equal('param_key', 'security_list_ip')
                ->find_one();

            $list = $security_list_ip->param_value ;
            $exp  = explode("\n" , $list );

            if ( $exp )
            {
                foreach( $exp as $ip )
                {
                    if ( $ip == $this->getIp() )
                    {
                        $auth = true ;
                    }
                }
            }

            if ( $auth == false )
            {
                $this->Factory()->Response()->show404() ;
            }
        }
    }

    private function isLogged()
	{
       return ( isset( $_SESSION[ $this->app->config('session') ] ) && !empty( $_SESSION[ $this->app->config('session') ] ) );
    }

    private function checkAuth()
	{
		$username = $this->app->request()->post( $this->app->config('auth_username') ) ;
		$password = $this->app->request()->post( $this->app->config('auth_password') ) ;
		
		if ( $username !== '' && $password !== '' )
		{
			$username = htmlentities($username, ENT_QUOTES) ;
			
			$user = \DB::for_table('user')
				->where_equal('user_name', $username)
				->find_one();
			
			if ( is_object( $user ) && $user->user_name === $username && password_verify( $password , $user->user_password ) == true )
			{
				return $this->login( $user ) ;
			}
			else
			{
				\App\Kernel\Back\Log::getInstance()->alert( 1 , $username ) ;
				return false ;
			}
		}
		else
		{
			return false ;
		}
    }

    private function isOnLoginPage()
	{
		if ( $this->app->request()->getPath() === $this->app->config('login.url') ) return true ;
		else															            return false ;
    }

    private function getIp()
	{
		return $_SERVER['REMOTE_ADDR'] ;
    }

    private function isBadIp()
	{
		if ( isset( $_SESSION[ $this->app->config('session') ]['ip'] ) && $this->getIp() === $_SESSION[ $this->app->config('session') ]['ip'] ) return false ;
		else																	   																return true ;
    }

    private function isOnLogoutPage()
	{
		if ( $this->app->request()->getPath() === $this->app->config('logout.url') ) return true ;
		else																		 return false ;
    }

    private function login( $user )
	{
		$_SESSION[ $this->app->config('session') ] = [
            'id' 		=> $user->user_id,
            'username' 	=> $user->user_name,
            'group_id' 	=> $user->user_group_id,
            'name' 		=> ucfirst( $user->user_fname ) . ' ' . strtoupper( $user->user_lname ),
            'logged_in' => true,
            'ip' 		=> $this->getIp()
        ];
		
		$this->pushData() ;
		
		$log = \App\Kernel\Back\Log::getInstance() ;
		$log->setUserId( $user->user_id ) ;
		$log->info( 2 ) ;
		
		return true ;
    }

    private function pushData()
	{
		$user = \DB::for_table('user')
				->left_outer_join('user_group', array('user.user_group_id', '=', 'user_group.user_group_id'))
				->where_equal( 'user_id' , $_SESSION[ $this->app->config('session') ]['id'] )
				->find_one();

        if ( ! $user )
        {
            $this->logout() ;
            $this->Factory()->Response()->redirectLogin() ;
        }
		
		$this->app->environment['user'] = [
			'id' 		 => $user->user_id,
			'name' 	 	 => $user->user_name,
			'lname' 	 => $user->user_lname,
			'fname' 	 => $user->user_fname,
			'group_name' => $user->user_group_name,
			'group_id' 	 => $user->user_group_id
        ];
		
		$this->app->view()->appendData([
            'user' => $this->app->environment['user']
        ]);
    }

    private function logout()
	{
		session_destroy();
		$_SESSION[ $this->app->config('session') ] = [];
		
		return !$this->isLogged();
    }
}