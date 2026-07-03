<?php

namespace App\Kernel\View;

use App\Kernel\Factory;

class TwigFront extends \Twig\Extension\AbstractExtension
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
            new \Twig\TwigFunction('siteUrl', array($this, 'site')),
            new \Twig\TwigFunction('remove', array($this, 'remove')),
            new \Twig\TwigFunction('addslashes', array($this, 'slashes')),
            new \Twig\TwigFunction('env', array($this, 'env')),
            new \Twig\TwigFunction('vendor', array($this, 'vendor')),
            new \Twig\TwigFunction('asset', array($this, 'asset')),
            new \Twig\TwigFunction('css', array($this, 'getCssVar')),
            new \Twig\TwigFunction('javascript', array($this, 'getJsVar')),
            new \Twig\TwigFunction('currentUrl', array($this, 'currentUrl'))
        );
    }

    /**
     * URL courante complète (scheme://host/uri).
     * Restaure la fonction Twig `currentUrl()` de l'ancien CMS, utilisée
     * notamment comme action de formulaire dans les templates projet.
     */
    public function currentUrl()
    {
        $protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri      = $_SERVER['REQUEST_URI'] ?? '/';

        return $protocol . '://' . $host . $uri;
    }

    public function remove( $str , $array )
    {
        if ( is_array( $array ) && ! empty( $array ) )
        {
            foreach( $array as $row )
            {
                $str = str_replace( $row , '' , $str );
            }
        }
        return $str ;
    }

    public function site( $url )
    {
        $protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . '://' . $host . '/' . ltrim( $url , '/' );
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
                if ( DEBUG_CMS == false && $minify == true && $directMin == true ) $url = $this->min( $url , $type ) ;
                $this->js[ $url ] = $minify ;
                break;

            case "css" :
                if ( DEBUG_CMS == false && $minify == true && $directMin == true ) $url = $this->min( $url , $type ) ;
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
                $content.= ( DEBUG_CMS ? "\t\t" : "" ) . '<script src="' . \App\Kernel\Http::getInstance()->getCdn() . $name . '"></script>' ;
                $i++;
            }
        }

        return $content ;
    }

    private function getJSProject()
    {
        $newName = '/assets/js/dist/script.min.js' ;

        if ( ( DEBUG_CMS == false && ( ! file_exists( WEB_PATH . $newName ) or ( file_exists( WEB_PATH . $newName ) && $this->dateConfigFile() >= filemtime( WEB_PATH . $newName ) ) ) ) or DEBUG_CMS == true )
        {
            $js = glob(WEB_PATH . '/assets/js/src/*.js');
            if ( $js && count( $js ) > 0 )
            {
                $min = '' ;
                foreach( $js as $file )
                {
                    $url = str_replace( WEB_PATH , '' , $file );

                    if ( DEBUG_CMS == true )	$this->js[ $url ] = $url ;
                    else					$min.= \JSMin::minify( $this->Factory()->File()->read( $file ) ) ;
                }

                if ( DEBUG_CMS == false )
                {
                    $this->Factory()->File()->create( WEB_PATH . $newName , $min ) ;
                    $this->js[ $newName ] = true ;
                }
            }
        }
        else if ( DEBUG_CMS == false )
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
                $content.= ( DEBUG_CMS ? "\t\t" : "" ) . '<link rel="stylesheet" href="' . \App\Kernel\Http::getInstance()->getCdn() . $name . '" />' ;
                $i++;
            }
        }

        return $content ;
    }

    private function getCSSProject()
    {
        $newName = '/assets/css/dist/std.min.css' ;
		
		if ( ( DEBUG_CMS == false && ( ! file_exists( WEB_PATH . $newName ) or ( file_exists( WEB_PATH . $newName ) && $this->dateConfigFile() >= filemtime( WEB_PATH . $newName ) ) ) ) or DEBUG_CMS == true )
		{
            $css = glob(WEB_PATH . '/assets/css/src/*');
			if ( $css && count( $css ) > 0 )
			{
				$min = '' ;
				foreach( $css as $file )
				{
					$url = str_replace( WEB_PATH , '' , $file );

					if ( DEBUG_CMS == true ) 	$this->css[ $url ] = $url ;
					else 					$min.= \Minify_CSS::minify( $this->Factory()->File()->read( $file ) ) ;
				}

				if ( DEBUG_CMS == false )
				{
					$this->Factory()->File()->create( WEB_PATH . $newName , $min ) ;
					$this->css[ $newName ] = true ;
				}
			}
		}
		else if ( DEBUG_CMS == false )
		{
			$this->css[ $newName ] = true ;
		}
    }
}