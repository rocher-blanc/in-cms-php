<?php

namespace App\Kernel\Factory;

class Token
{
    public function getApp()
	{
        return \App\Kernel\SlimBridge::getInstance() ;
    }
	
	public function check( $token )
	{
		if ( $token == $_SESSION[ $this->getApp()->config('token') ] )	return true ;
		else															return false ;
	}
}