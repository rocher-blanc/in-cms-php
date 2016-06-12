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
        $separator = "/" ;
        $vendorName = 'jweb/cms' ;
        $path = implode( PATH_SEPARATOR, array( realpath( dirname(__FILE__) . '/../../' ) ) ) ;

        if ( substr( $path , 0 , 1 ) != '/' )  $separator = "\\" ;

        $vendor = str_replace( "/" , $separator , "/vendor/" . $vendorName ) ;
        $path   = str_replace( $vendor , "" , $path );

        define('_PATH_', $path );
        define('PROJECT_PATH', _PATH_ . '/Project');
        define("VENDOR_PATH", _PATH_ . "/vendor");

        define('WEB_PATH', _PATH_ . '/web');
        define('ASSET_PATH', WEB_PATH . '/assets');
        define('BOWER_PATH', ASSET_PATH . '/vendor');

        require VENDOR_PATH . '/autoload.php';

        self::postInstall() ;
    }

    public static function postInstall()
    {
        self::checkFolder() ;
        self::checkConfigSass() ;
        self::checkHtaccess() ;
        self::checkConfig() ;
        self::checkIndex() ;
        self::minify() ;
        self::patchVendor() ;
        self::patchModule() ;
    }

    protected static function patchModule()
    {
        $tab = ["Back","Front"];

        $scan = glob(PROJECT_PATH . "/Module/Entity/*.php");
        if ( $scan )
        {
            foreach( $scan as $namePhp )
            {
                $name = str_replace(".php","",$namePhp);
                $name = str_replace(PROJECT_PATH . "/Module/Entity/","",$name);

                // On génére le repository
                foreach( $tab as $row )
                {
                    $php = '' ;
                    $php.= "<"."?"."php\n\n" ;
                    $php.= "namespace Project\Module\Repository\\" . $row . ";\n\n" ;
                    $php.= "class " . $name . "Repository extends \App\Kernel\\" . $row . "\Repository\n" ;
                    $php.= "{\n" ;
                    $php.= "\t\n" ;
                    $php.= "}" ;
                    self::create( PROJECT_PATH . "/Module/Repository/" . $row . "/" . $name . ".php" , $php ) ;
                }

                // On génére le controller
                foreach( $tab as $row )
                {
                    $php = '' ;
                    $php.= "<"."?"."php\n\n" ;
                    $php.= "namespace Project\Module\Controller\Front;\n\n" ;
                    $php.= "class " . $name . " extends \App\Kernel\Front\Controller\n" ;
                    $php.= "{\n" ;
                    $php.= "\t\n" ;
                    $php.= "}" ;

                    $php = '' ;
                    $php.= "<"."?"."php\n\n" ;
                    $php.= "namespace Project\Module\Controller\\" . $row . ";\n\n" ;
                    $php.= "class " . $name . " extends \App\Kernel\\" . $row . "\Controller\n" ;
                    $php.= "{\n" ;
                    $php.= "\t\n" ;
                    $php.= "}" ;
                    self::create( PROJECT_PATH . "/Module/Controller/" . $row . "/" . $name . ".php" , $php ) ;
                }

                // On génére la class entity
                $php = '' ;
                $php.= "<"."?"."php\n\n" ;
                $php.= "namespace Project\Module\Entity\Class\\" . $row . ";\n\n" ;

                $php.= "/** @Entity @Table(name=\"addresses\") */\n" ;
                $php.= "class " . $name . " extends \App\Kernel\\" . $row . "\Controller\n" ;
                $php.= "{\n" ;
                $php.= "\t\n" ;
                $php.= "}" ;
                self::create( PROJECT_PATH . "/Module/Entity/Class/" . $name . ".php" , $php ) ;
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

    protected static function minify()
    {
        self::minCSS( BOWER_PATH . "/cmsmedias/css/login.css" ) ;
        self::minCSS( BOWER_PATH . "/cmsmedias/css/media.css" ) ;
        self::minCSS( BOWER_PATH . "/cmsmedias/css/std.css" ) ;
        self::minCSS( BOWER_PATH . "/cmsmedias/css/error.css" ) ;

        self::minJS( BOWER_PATH . "/cmsmedias/js/i2n.functions.js" ) ;
        self::minJS( BOWER_PATH . "/cmsmedias/js/i2n.text.js" ) ;
        self::minJS( BOWER_PATH . "/cmsmedias/js/init.js" ) ;
        self::minJS( BOWER_PATH . "/cmsmedias/js/script.js" ) ;
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
            $php.= 'define("_PATH_", implode(PATH_SEPARATOR, array( realpath(dirname(__FILE__) . "/../../")) ));' . "\n" ;
            $php.= 'define("VENDOR_PATH", _PATH_ . "/vendor");' . "\n" ;
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
            $php.= 'define("_PATH_", implode(PATH_SEPARATOR, array( realpath(dirname(__FILE__) . "/../")) ));' . "\n" ;
            $php.= 'define("VENDOR_PATH", _PATH_ . "/vendor");' . "\n" ;
            $php.= "require VENDOR_PATH . '/autoload.php';" . "\n" ;
            $php.= '$loader = new \App\Kernel\Front\Loader;' . "\n" ;
            $php.= '$loader->index();' ;

            self::create( $indexFront , $php ) ;
        }
    }

    protected static function checkConfig()
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
            $php.= '#output_style = :compressed' . "\n" ;

            self::create( $rubyFile , $php ) ;
        }
    }

    protected static function checkFolder()
    {
        $folders = [
            "Project",
            "Project/Lang",
            "Project/config",
            "Project/Controller",
            "Project/Controller/back",
            "Project/Controller/front",
            "Project/Module",
            "Project/Module/Repository",
            "Project/Module/Repository/Back",
            "Project/Module/Repository/Front",
            "Project/Module/Controller",
            "Project/Module/Controller/Back",
            "Project/Module/Controller/Front",
            "Project/Module/Entity",
            "Project/Module/Entity/Class",
            "Project/Middleware",
            "Project/Middleware/Back",
            "Project/Middleware/Front",
            "Project/view",
            "Project/view/back",
            "Project/view/front",
            "Project/view/front/module",
            "Project/view/front/page",
            "Project/view/front/helper",
            "Project/view/front/helper/module",
            "Project/view/front/helper/page",
            "cache",
            "cache/back",
            "cache/front",
            "web",
            "web/" . self::getAdminFolder(),
            "web/" . self::getAdminFolder() . "/assets",
            "web/" . self::getAdminFolder() . "/assets/img",
            "web/uploads",
            "web/images",
            "web/assets",
            "web/assets/css",
            "web/assets/sass",
            "web/assets/css/src",
            "web/assets/css/dist",
            "web/assets/js",
            "web/assets/js/src",
            "web/assets/js/dist",
            "web/assets/img"
        ] ;

        foreach( $folders as $folder )
        {
            if ( ! is_dir( _PATH_ . "/" . $folder ) ) mkdir( _PATH_ . "/" . $folder ) ;
        }
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
}