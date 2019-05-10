<?php

namespace App\Kernel\View;

use App\Kernel\Factory;
use Slim\Slim;

class TwigFront extends \Twig_Extension
{
    public $css = [];
    public $js = [];

    public function getName()
    {
        return 'front';
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    private function Factory()
    {
        return Factory::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    private function dateConfigFile()
    {
        $configFileProject       = 'config.php' ;
        $configFileProjectDomain = 'config.' . $_SERVER['HTTP_HOST'] . '.php' ;

        if ( file_exists( $configFileProject ) )
        {
            $file = $configFileProjectDomain;
        }
        else if ( file_exists( $configFileProjectDomain ) )
        {
            $file = $configFileProject;
        }
        else
        {
            defined('FILE_CONFIG') || define('FILE_CONFIG',false);
        }

        return filemtime( PROJECT_PATH . '/config/' . $file ) ;
    }

    public function getFunctions()
    {
        $this->tab = [];

        return array(
            new \Twig_SimpleFunction('addslashes', array($this, 'slashes')),
            new \Twig_SimpleFunction('env', array($this, 'env')),
            new \Twig_SimpleFunction('vendor', array($this, 'vendor')),
            new \Twig_SimpleFunction('asset', array($this, 'asset')),
            new \Twig_SimpleFunction('css', array($this, 'getCssVar')),
            new \Twig_SimpleFunction('javascript', array($this, 'getJsVar'))
        );
    }

    public function slashes( $str )
    {
        return addslashes($str) ;
    }

    public function env()
    {
        if ( PRODUCTION === true )  return 'prod' ;
        else                        return 'dev' ;
    }

    public function vendor( $url )
    {
        if ( is_array( $url ) )
        {
            foreach( $url as $row )
            {
                $this->stock($row, '/assets/vendor/' , true );
            }
        }
        else
        {
            return $this->stock($url, '/assets/vendor/' , true );
        }
    }

    public function asset( $url , $stock = false )
    {
        if ( $stock == true && ( substr( $url , -3 ) == '.js' or substr( $url , -4 ) == '.css' ) )
        {
            return $this->stock($url, '/assets/' );
        }
        else
        {
            return \App\Kernel\Http::getInstance()->getCdn() . '/assets/' . $url ;
        }
    }

    public function stock( $url , $folder , $directMin = false )
    {
        $exp    = explode( '.' , $url );
        $ct     = count( $exp );
        $type   = $exp[ $ct - 1 ];
        $url    = $folder . ltrim($url, '/');
        $minify = ( strpos($url, '.min.') !== false ? false : true ) ;

        switch( $type )
        {
            case "js" :
                if ( DEBUG == false && $minify == true && $directMin == true ) $url = $this->min( $url , $type ) ;
                $this->js[ $url ] = $minify ;
                break;

            case "css" :
                if ( DEBUG == false && $minify == true && $directMin == true ) $url = $this->min( $url , $type ) ;
                $this->css[ $url ] = $minify ;
                break;

            default :
                return \App\Kernel\Http::getInstance()->getCdn() . $url ;
                break;
        }
    }

    private function min( $url , $type )
    {
        $newName = substr( $url , 0 , ( ( strlen( $type ) + 1 ) * -1 ) ) . ".min." . $type ;

        if ( file_exists( WEB_PATH . $newName ) )
        {
            if ( filemtime( WEB_PATH . $newName ) >= filemtime( WEB_PATH . $url ) ) return $newName ;
        }

        switch( $type )
        {
            case "css" :
                $min = \Minify_CSS::minify( $this->Factory()->File()->read( WEB_PATH . $url ) ) ;
                break;
            case "js" :
                $min = \JSMin::minify( $this->Factory()->File()->read( WEB_PATH . $url ) ) ;
                break;
        }

        $this->Factory()->File()->create( WEB_PATH . $newName , $min ) ;

        return $newName ;
    }

    /* ************************************************** */
    /* ****************       JS      ******************* */
    /* ************************************************** */

    public function getJsVar()
    {
        return ASSET_JS_VAR ;
    }

    public function javascript( $project = true )
    {
        if ( $project == true )
        {
            $this->getJSProject();
        }

        $content = '' ;

        if ( ! empty( $this->js ) )
        {
            $i = 0;
            foreach( $this->js as $name => $minify )
            {
                if ( $i != 0 ) $content.= "\n" ;
                $content.= ( DEBUG ? "\t\t" : "" ) . '<script src="' . \App\Kernel\Http::getInstance()->getCdn() . $name . '"></script>' ;
                $i++;
            }
        }

        return $content ;
    }

    private function getJSProject()
    {
        $newName = '/assets/js/dist/script.min.js' ;

        if ( ( DEBUG == false && ( ! file_exists( WEB_PATH . $newName ) or ( file_exists( WEB_PATH . $newName ) && $this->dateConfigFile() >= filemtime( WEB_PATH . $newName ) ) ) ) or DEBUG == true )
        {
            $js = glob(WEB_PATH . '/assets/js/src/*.js');
            if ( $js && count( $js ) > 0 )
            {
                $min = '' ;
                foreach( $js as $file )
                {
                    $url = str_replace( WEB_PATH , '' , $file );

                    if ( DEBUG == true )	$this->js[ $url ] = $url ;
                    else					$min.= \JSMin::minify( $this->Factory()->File()->read( $file ) ) ;
                }

                if ( DEBUG == false )
                {
                    $this->Factory()->File()->create( WEB_PATH . $newName , $min ) ;
                    $this->js[ $newName ] = true ;
                }
            }
        }
        else if ( DEBUG == false )
        {
            $this->js[ $newName ] = true ;
        }
    }

    /* ************************************************** */
    /* ****************      CSS      ******************* */
    /* ************************************************** */

    public function getCssVar()
    {
        return ASSET_CSS_VAR ;
    }

    public function css( $project = true )
    {
        if ( $project == true )
        {
            $this->getCSSProject();
        }
        $content = '' ;

        if ( ! empty( $this->css ) )
        {
            $i = 0;
            foreach( $this->css as $name => $minify )
            {
                if ( $i != 0 ) $content.= "\n" ;
                $content.= ( DEBUG ? "\t\t" : "" ) . '<link rel="stylesheet" href="' . \App\Kernel\Http::getInstance()->getCdn() . $name . '" />' ;
                $i++;
            }
        }

        return $content ;
    }

    private function getCSSProject()
    {
        $newName = '/assets/css/dist/std.min.css' ;
		
		if ( ( DEBUG == false && ( ! file_exists( WEB_PATH . $newName ) or ( file_exists( WEB_PATH . $newName ) && $this->dateConfigFile() >= filemtime( WEB_PATH . $newName ) ) ) ) or DEBUG == true )
		{
            $css = glob(WEB_PATH . '/assets/css/src/*');
			if ( $css && count( $css ) > 0 )
			{
				$min = '' ;
				foreach( $css as $file )
				{
					$url = str_replace( WEB_PATH , '' , $file );

					if ( DEBUG == true ) 	$this->css[ $url ] = $url ;
					else 					$min.= \Minify_CSS::minify( $this->Factory()->File()->read( $file ) ) ;
				}

				if ( DEBUG == false )
				{
					$this->Factory()->File()->create( WEB_PATH . $newName , $min ) ;
					$this->css[ $newName ] = true ;
				}
			}
		}
		else if ( DEBUG == false )
		{
			$this->css[ $newName ] = true ;
		}
    }
}