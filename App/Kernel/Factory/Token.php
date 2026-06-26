<?php

namespace App\Kernel\Factory;

class Token
{
	public function check( $token )
	{
		$key = \App\Kernel\Config::getInstance()->get('token', 'csrf_token');
		if ( $token == $_SESSION[ $key ] )	return true ;
		else								return false ;
	}
}