<?php

namespace App\Kernel;

class Install
{
    public function __construct() {}

    public static function getAdminFolder()
    {
        return 'admin-site' ;
    }

    public static function postUpdate()
    {
        $vendorName = 'JWebCreation/cms' ;
        if ( getenv('APP_HOME') === false )
        {
            $separator = "/" ;
            $path = implode( PATH_SEPARATOR, array( realpath( dirname(__FILE__) . '/../../' ) ) ) ;

            if ( substr( $path , 0 , 1 ) != '/' )  $separator = "\\" ;

            $vendor = str_replace( "/" , $separator , "/vendor/" . $vendorName ) ;
            $path   = str_replace( $vendor , "" , $path );

            defined('_PATH_') || define('_PATH_', $path );
            defined('VENDOR_PATH') || define("VENDOR_PATH", _PATH_ . "/vendor");
        }
        else
        {
            $path   = getenv('APP_HOME') . "/public" ;
            defined('_PATH_') || define('_PATH_', $path );
            defined('VENDOR_PATH') || define("VENDOR_PATH", _PATH_ . "/..//vendor");
        }

        defined('SLACK_WEBHOOK') || define('SLACK_WEBHOOK', 'https://hooks.slack.com/services/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' );
        defined('SLACK_EMOJI') || define('SLACK_EMOJI', ":jweb:" );
        defined('SLACK_AUTHORNAME') || define('SLACK_AUTHORNAME', "JWeb" );
        defined('SLACK_USERNAME') || define('SLACK_USERNAME', "JWeb-Bot" );
        defined('SLACK_COLOR') || define('SLACK_COLOR', "#ffab40" );

        defined('PROJECT_PATH') || define('PROJECT_PATH', _PATH_ . '/Project');

        defined('WEB_PATH') || define('WEB_PATH', _PATH_ . '/web');
        defined('KERNEL_PATH') || define('KERNEL_PATH', VENDOR_PATH . "/" . $vendorName . '/App/Kernel');
        defined('ASSET_PATH') || define('ASSET_PATH', WEB_PATH . '/assets');
        defined('BOWER_PATH') || define('BOWER_PATH', ASSET_PATH . '/vendor');

        require VENDOR_PATH . '/autoload.php';
        require KERNEL_PATH . '/DB.php';

        self::postInstall() ;
    }

    public static function postInstall()
    {
        $install = self::isInstallation() ;

        self::checkFolder() ;
        self::checkConfigSass() ;
        self::checkHtaccess() ;
        self::checkConfig() ;
        self::checkIndex() ;
        // self::minify() ;
        // self::patchVendor() ;
        self::patchDb() ;

        \App\Kernel\Utils\Slack::notificationInstall( "JContent" , "" , "dev" , ( $install ? "Installation" : "Mise à jour" ) . " du CMS avec succés\nProjet : " . self::getFolderProject() );
    }

    protected static function getFolderProject()
    {
        $folders = explode('/' , _PATH_ );
        return $folders[2] ;
    }

    protected static function isInstallation()
    {
        return ( is_dir( _PATH_ . "/Project" ) ? false : true ) ;
    }

    protected static function patchDb()
    {
        $regex = PROJECT_PATH . "/config/*.php" ;
        $files = glob( $regex ) ;

        if ( $files )
        {
            foreach( $files as $file )
            {
                if ( strpos( $file , 'vps1') !== false )
                {
                    $monfichier = str_replace( PROJECT_PATH . "/config/config." , "" , $file );
                    $url = str_replace( ".php" , "" , $monfichier );

                    @file_get_contents( "http://" . $url . "/?checkDB" ) ;
                }
            }
        }
    }

    protected static function patchVendor()
    {
        $file = BOWER_PATH . "/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css" ;
        $css  = self::read( $file ) ;
        $css  = str_replace( 'url(/img/' , 'url(../img/' , $css ) ;

        self::create( $file , $css ) ;
    }

    protected static function minCSS( $file )
    {
        $newname = str_replace( '.css' , '.min.css' , $file ) ;
        self::create( $newname , \Minify_CSS::minify( self::read( $file ) ) ) ;
    }

    protected static function minJS( $file )
    {
        $newname = str_replace( '.js' , '.min.js' , $file ) ;
        self::create( $newname , \JSMin::minify( self::read( $file ) ) ) ;
    }

    protected static function checkHtaccess()
    {
        // admin
        $htaccessAdmin = WEB_PATH . '/' . self::getAdminFolder() . '/.htaccess' ;
        if ( ! file_exists( $htaccessAdmin ) )
        {
            $content = "RewriteEngine On\n" ;
            $content.= "RewriteBase /" . self::getAdminFolder() . "/\n" ;
            $content.= "RewriteCond %{REQUEST_FILENAME} !-f\n" ;
            $content.= "RewriteRule ^ index.php [QSA,L]" ;

            self::create( $htaccessAdmin , $content ) ;
        }

        // front
        $htaccessFront = WEB_PATH . '/.htaccess' ;
        if ( ! file_exists( $htaccessFront ) )
        {
            $content = "RewriteEngine On\n" ;
            #$content.= "RewriteRule ^(" . self::getAdminFolder() . ")($|/) - [L]\n" ;
            #$content.= "RewriteCond %{HTTP_HOST} !^www\.\n" ;
            #$content.= "RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]\n" ;
            $content.= "RewriteBase /\n" ;
            $content.= "RewriteCond %{REQUEST_FILENAME} !-f\n" ;
            $content.= "RewriteRule ^ index.php [QSA,L]" ;

            self::create( $htaccessFront , $content ) ;
        }
    }

    protected static function checkIndex()
    {
        // admin
        $indexAdmin = WEB_PATH . '/' . self::getAdminFolder() . '/index.php' ;
        if ( ! file_exists( $indexAdmin ) )
        {
            $php = '' ;
            $php.= "<"."?"."php\n" ;

            if ( getenv('APP_HOME') !== false )
            {
                $php.= 'define("_PATH_", getenv("APP_HOME") . "/public" );' . "\n" ;
                $php.= 'define("VENDOR_PATH", getenv("APP_HOME") . "/vendor");' . "\n" ;
            }
            else
            {
                $php.= 'define("_PATH_", implode(PATH_SEPARATOR, array( realpath(dirname(__FILE__) . "/../../")) ));' . "\n" ;
                $php.= 'define("VENDOR_PATH", _PATH_ . "/vendor");' . "\n" ;
            }

            $php.= "require VENDOR_PATH . '/autoload.php';" . "\n" ;
            $php.= '$loader = new \App\Kernel\Back\Loader;' . "\n" ;
            $php.= '$loader->index();' ;

            self::create( $indexAdmin , $php ) ;
        }

        // front
        $indexFront = WEB_PATH . '/index.php' ;
        if ( ! file_exists( $indexFront ) )
        {
            $php = '' ;
            $php.= "<"."?"."php\n" ;
            if ( getenv('APP_HOME') !== false )
            {
                $php.= 'define("_PATH_", getenv("APP_HOME") . "/public" );' . "\n" ;
                $php.= 'define("VENDOR_PATH", getenv("APP_HOME") . "/vendor");' . "\n" ;
            }
            else
            {
                $php.= 'define("_PATH_", implode(PATH_SEPARATOR, array( realpath(dirname(__FILE__) . "/../")) ));' . "\n" ;
                $php.= 'define("VENDOR_PATH", _PATH_ . "/vendor");' . "\n" ;
            }
            $php.= "require VENDOR_PATH . '/autoload.php';" . "\n" ;
            $php.= '$loader = new \App\Kernel\Front\Loader;' . "\n" ;
            $php.= '$loader->index();' ;

            self::create( $indexFront , $php ) ;
        }
    }

    protected static function checkConfig()
    {
        if ( getenv('APP_ID') !== false && getenv( 'MYSQL_ADDON_HOST' ) !== false )
        {
            $configFileProject = PROJECT_PATH . '/config/config.' . str_replace( "app_" , "app-" , getenv('APP_ID') ) .'.cleverapps.io.php' ;

            if ( ! file_exists( $configFileProject ) )
            {
                $php = '' ;
                $php.= "<"."?"."php\n" ;
                $php.= "define('DB_HOST','" . getenv('MYSQL_ADDON_HOST') . "');\n" ;
                $php.= "define('DB_USER','" . getenv('MYSQL_ADDON_USER') . "');\n" ;
                $php.= "define('DB_PASSWORD','" . getenv('MYSQL_ADDON_PASSWORD') . "');\n" ;
                $php.= "define('DB_DATABASE','" . getenv('MYSQL_ADDON_DB') . "');\n" ;
                $php.= "define('DB_PORT','" . getenv('MYSQL_ADDON_PORT') . "');\n" ;
                $php.= "define('DEBUG',true);" ;

                self::create( $configFileProject , $php ) ;
            }
        }
        else
        {
            $configFileProject = PROJECT_PATH . '/config/config.sample.php' ;

            if ( ! file_exists( $configFileProject ) )
            {
                $php = '' ;
                $php.= "<"."?"."php\n" ;
                $php.= "define('DB_HOST','');\n" ;
                $php.= "define('DB_USER','');\n" ;
                $php.= "define('DB_PASSWORD','');\n" ;
                $php.= "define('DB_DATABASE','');\n" ;
                $php.= "define('DEBUG',true);" ;

                self::create( $configFileProject , $php ) ;
            }
        }
    }

    protected static function checkConfigSass()
    {
        $rubyFile = _PATH_ . '/web/assets/config.rb' ;

        if ( ! file_exists( $rubyFile ) )
        {
            $php = '' ;
            $php.= 'css_dir = "css/src"' . "\n" ;
            $php.= 'sass_dir = "sass"' . "\n" ;
            $php.= 'images_dir = "img"' . "\n" ;
            $php.= 'javascripts_dir = "js"' . "\n" ;
            $php.= 'line_comments = false' . "\n" ;
            $php.= 'output_style = :compressed' . "\n" ;

            self::create( $rubyFile , $php ) ;
        }
    }

    protected static function checkFolder()
    {
        $folders = [
            "Project",
            "Project/CustomClass",
            "Project/CustomClass/Back",
            "Project/CustomClass/Front",
            "Project/CustomClass/Common",
            "Project/Lang",
            "Project/config",
            "Project/Controller",
            "Project/Controller/Back",
            "Project/Controller/Front",
            "Project/Form",
            "Project/Module",
            "Project/Module/Webservice",
            "Project/Module/Repository",
            "Project/Module/Repository/Back",
            "Project/Module/Repository/Front",
            "Project/Module/Controller",
            "Project/Module/Controller/Back",
            "Project/Module/Controller/Front",
            "Project/Module/Entity",
            "Project/Middleware",
            "Project/Middleware/Back",
            "Project/Middleware/Front",
            "Project/view",
            "Project/view/common",
            "Project/view/common/mail",
            "Project/view/back",
            "Project/view/back/mail",
            "Project/view/front",
            "Project/view/front/mail",
            "Project/view/front/module",
            "Project/view/front/page",
            "Project/view/front/component",
            "Project/view/front/helper",
            "Project/view/front/helper/module",
            "Project/view/front/helper/page",
            "cache",
            "cache/back",
            "cache/front",
            "cache/save",
            "cache/save/traduction",
            "web",
            "web/" . self::getAdminFolder(),
            "web/uploads",
            "web/images",
            "web/images/_avatar",
            "web/documents",
            "web/assets",
            "web/assets/css",
            "web/assets/sass",
            "web/assets/js",
            "web/assets/img",
            "web/assets/vendor",
			"web/assets/vendor/cmsmedias"
        ] ;

        foreach( $folders as $folder )
        {
            if ( ! is_dir( _PATH_ . "/" . $folder ) ) mkdir( _PATH_ . "/" . $folder ) ;
        }

        self::copyr( VENDOR_PATH . '/JWebCreation/cms/assets' , _PATH_ . "/web/assets/vendor/cmsmedias" );
    }

    protected static function create( $nameFile , $content )
    {
        $fp = fopen( $nameFile , 'w+' ) ;
        $rst = fwrite( $fp , $content ) ;
        fclose( $fp ) ;

        return $rst ;
    }

    protected static function read( $nameFile )
    {
        if ( file_exists( $nameFile ) )
        {
            $fp 	 = fopen( $nameFile , 'r' ) ;
            $content = fread( $fp , filesize( $nameFile ) ) ;
            fclose( $fp ) ;
        }
        else
        {
            return false ;
        }

        return $content ;
    }

    protected static function copyr( $source, $dest )
	{
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			self::copyr("$source/$entry", "$dest/$entry");
		}

		// Clean up
		$dir->close();
		return true;
	}
}