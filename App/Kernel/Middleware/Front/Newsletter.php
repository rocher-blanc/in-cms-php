<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Front\Translate;
use App\Kernel\Front\Newsletter as NewsletterTool;

class Newsletter extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    private function user()
    {
        return \App\Kernel\Front\User::getInstance();
    }

    public function observe()
    {
        if ( $this->app->request->isPost() )
        {
            if ( $this->app->request->post('newsletter_action') == 'add' )
            {
                $this->addNewsletter();
            }
        }
    }

    protected function addNewsletter()
	{
		$list  = $this->app->request->post('list');
		$email = $this->app->request->post('email');

		$Newsletter = new NewsletterTool;
		$Newsletter->setGroupId( $list );
		$Newsletter->setEmail( $email );
		$result = $Newsletter->subscribe();

		if( $result )
		{
			$this->returnError( $this->translate('newsletter_add_success') , true );
		}
		else
		{
			$this->returnError( $Newsletter->getError() , false );
		}
	}

	protected function returnError( $message , $result = false )
	{
		$this->app->response->body( json_encode([
			'result'  => $result,
			'msg' => $message
		]) );

		return $result;
	}

	protected function translate( $key )
	{
		return Translate::getInstance()->getText( $key );
	}
}