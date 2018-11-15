<?php

namespace App\Kernel;

class Slim
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $_slim = NULL ;
    private $templateFolder = [] ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct() {}
	
	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */
	
	public function setConfig( $config )
	{
		if ( is_array( $config ) ) $this->_slim->config( $config ) ;
	}
	
	public function setParserExtensions( $ext )
	{
        if ( is_array( $ext ) ) $this->_slim->view()->parserExtensions = array_merge( $this->_slim->view()->parserExtensions , $ext ) ;
    }

    public function setTemplateFolder( $folder )
    {
        $this->templateFolder[] = $folder ;
    }
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

	public function getApp()
	{
		return $this->_slim ;
	}

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

	/* ************************************************** */
	/* ******************  FUNCTIONS  ******************* */
	/* ************************************************** */
	
	public function load()
	{
		$this->_slim = new \Slim\Slim([
            'view'  => new \Slim\Views\Twig(),
			'cache' => CACHE_PATH,
			'mode'  => SLIM_MODE
		]);
	}
	
	/* ************************************************** */
	/* ***************      PLUGIN      ***************** */
	/* ************************************************** */
	
	public function loadPlugin( $plugin )
	{
		$this->_slim->add(new \App\Kernel\Middleware\Plugin( $plugin ));
	}
	
	/* ************************************************** */
	/* ***************    MIDDLEWARE    ***************** */
	/* ************************************************** */
	
	public function initMiddleware()
	{
        $this->_slim->add(new \App\Kernel\Middleware\PrettyExceptions);
        $this->_slim->add(new \App\Kernel\Middleware\SessionCrypt([
            'expires' => '60 minutes',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'name' => $this->_slim->config('session_name'),
        ]));

        if ( DEBUG_BAR )
        {
			$debugbar = new \Slim\Middleware\DebugBar ;
            $debugbar->addCollector(new \App\Kernel\Collector\Database());
            $this->_slim->add( $debugbar ) ;
        }
    }
	
	public function addMiddleware( $middleware ) 
	{
		if ( is_array( $middleware ) )
		{
			foreach( $middleware as $row )
			{
				if ( is_object( $row ) ) $this->_slim->add( $row );
			}
		}
		else if ( is_object( $middleware ) )
		{
			$this->_slim->add( $middleware );
		}
	}

    /* ************************************************** */
    /* ******************    INIT    ******************** */
    /* ************************************************** */
	
	public function initView()
	{
		$this->_slim->view()->parserOptions = [
			'debug' => $this->_slim->config('twig.debug'),
			'cache' => $this->_slim->config('cache'),
			'autoescape' => false
		];

        $viewArray[] = VIEW_PROJECT_PATH ;
        $viewArray[] = VIEW_PROJECT_COMMON_PATH ;

        if ( ! empty( $this->templateFolder ) )
        {
            foreach( $this->templateFolder as $row )
            {
                $viewArray[] = $row ;
            }
        }

        $url = $this->Factory()->Url()->getFullUrl();
        $exp = explode( '/' , $url );
        if ( count( $exp ) > 2 )
        {
            if ( $exp[2] == 'module' )
            {
                if ( is_dir( VIEW_PROJECT_PATH . "/module/" . $exp[3] ) )
                {
                    $viewArray[] = VIEW_PROJECT_PATH . "/module/" . $exp[3];
                }

                if ( is_dir( TEMPLATES_PATH . "/module/" . $exp[3] ) )
                {
                    $viewArray[] = TEMPLATES_PATH . "/module/" . $exp[3];
                }
            }
        }

        $viewArray[] = TEMPLATES_PATH ;
        if ( defined('THEME' ) && $this->_slim->config('config') == 'front' )
        {
            $viewArray[] = TEMPLATES_COMMON_TECH_PATH ;
        }
        $viewArray[] = TEMPLATES_COMMON_PATH ;

        $this->_slim->view()->twigTemplateDirs = $viewArray ;
        $this->_slim->view()->parserExtensions = [
			new \Twig_Extensions_Extension_Text(),
			new \Slim\Views\TwigExtension(),
		];
	}
	
	public function initMode()
	{
		// Seulement appelée si le mode est "production"
		$this->_slim->configureMode('production', function () {
            $this->_slim->config([
                'log.enable' => false,
                'cache' 	 => CACHE_PATH,
                'debug' 	 => false,
                'twig.debug' => false
            ]);

            $this->_slim->log->setEnabled(true);
            $this->_slim->log->setLevel(\Slim\Log::DEBUG);
		});

		// Seulement appelée si le mode est "development"
		$this->_slim->configureMode('development', function () {
            $this->_slim->config([
                'log.enable' => false,
                'cache' 	 => false,
                'debug' 	 => true,
                'twig.debug' => true
            ]);

            $this->_slim->log->setEnabled(true);
            $this->_slim->log->setLevel(\Slim\Log::DEBUG);
		});
	}
	
	/* ************************************************** */
	/* ******************    RUN     ******************** */
	/* ************************************************** */
	
	public function run()
	{
		$this->_slim->run();
	}
}