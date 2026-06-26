<?php

namespace App;

use App\Kernel\AppContext;
use App\Kernel\Base;
use App\Kernel\Database;
use App\Kernel\Lang;
use App\Kernel\Slim;
use App\Kernel\View\TwigFront;

class Kernel
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    /*
     * @array
     * Contient la tableau de config
     */
    private $_config = [
        'config' => 'front'
    ];

    /*
     * @object
     * Contient Slim
     */
    private $_slim = NULL;

    /*
     * @object
     * Contient l'instance avec la database
     */
    private $_db = NULL;

    /*
     * @array
     * Contient les middlewares
     */
    private $_middleware = array();

    /*
     * @array
     * Contient les extensions twig
     */
    private $_parserExtension = array();

    /*
     * @boolean
     * Active le cache DB
     */
    private $_caching_db = false;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $config = [] )
    {
        $this->config( $config ) ;
    }

    public function load()
    {
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', VENDOR_PATH . '/jwebcreation/cms/App');

        require APPLICATION_PATH . '/config/config.php';
        require APPLICATION_PATH . '/config/config.' . $this->config('config') . '.php';

        $this->checkChmod() ;

        if ( FILE_CONFIG === false )
        {
            $this->configFileNotFound() ;
        }
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setMiddleware( $obj )
    {
        if ( is_object( $obj ) ) $this->_middleware[] = $obj ;
    }

    public function setParserExtension( $ext )
    {
        if ( is_object( $ext ) ) 		$this->_parserExtension[] = $ext ;
        else if ( is_array( $ext ) )	$this->_parserExtension = array_merge( $this->_parserExtension , $ext ) ;
    }

    /* ************************************************** */
    /* ******************   TOOLS    ******************** */
    /* ************************************************** */

    protected function Container()
    {
        return \App\Kernel\Container::getInstance();
    }

    protected function Param()
    {
        return $this->Container()->param();
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getConfig()
    {
        return $this->_config ;
    }

    public function getMiddleware()
    {
        return array_reverse( $this->_middleware ) ;
    }

    public function getParserExtensions()
    {
        return $this->_parserExtension ;
    }

    private function getPlugin()
    {
        return $this->_plugin ;
    }

    public function getSlim()
    {
        if ( $this->_slim === NULL )
        {
            $this->_slim = new Slim;
            $this->_slim->load();
        }

        return $this->_slim ;
    }

    public function getDb()
    {
        if ( $this->_db === NULL ) $this->_db = new Database;
        return $this->_db ;
    }

    /* ************************************************** */
    /* ****************    CONFIG     ******************* */
    /* ************************************************** */

    public function config( $config )
    {
        if ( is_array( $config ) )
        {
            foreach( $config as $key => $value )
            {
                $this->_config[ $key ] = $value ;
            }
        }
        else if ( is_string( $config ) && array_key_exists( $config , $this->getConfig() ) )
        {
            return $this->_config[ $config ] ;
        }
    }

    /* ************************************************** */
    /* ****************     SLIM      ******************* */
    /* ************************************************** */

    protected function initSlim()
    {
        /* CONFIG */
        $this->getSlim()->setConfig( $this->getConfig() ) ;
        $this->getSlim()->setBasePathFromConfig() ;

        /* MODE (DEV / PROD)*/
        $this->getSlim()->initMode() ;

        /* TWIG */
        $this->getSlim()->initView() ;
        $this->getSlim()->setParserExtensions( $this->getParserExtensions() ) ;

        /* MIDDLEWARE */
        $this->getSlim()->initMiddleware() ;
        $this->getSlim()->addMiddleware( $this->getMiddleware() ) ;
    }

    /* ************************************************** */
    /* ****************  	 DATE	    ***************** */
    /* ************************************************** */

    private function initDate()
    {
        date_default_timezone_set( TIMEZONE );

        $locale = strtolower( Lang::getInstance()->getActive()->locale ) . '_' . strtoupper( COUNTRY );

        $encodage = '' ; // '.UTF-8'
        setlocale( LC_COLLATE, $locale . $encodage , "French" );
        setlocale( LC_CTYPE, $locale . $encodage , "French" );
        setlocale( LC_TIME, $locale . $encodage , "French" );
//        setlocale( LC_NUMERIC, 'en_US.UTF-8', 'en_US.utf8' );
    }

    /* ************************************************** */
    /* ****************   DATABASE    ******************* */
    /* ************************************************** */

    private function connectToDatabase()
    {
        if ( DB_HOST == '' || DB_USER == '' || DB_DATABASE == '' )
        {
            $this->viewTemplateError('bdd') ;
        }

        try
        {
            $this->getDb()->connect() ;
            $generate = false ;

            try
            {
                $this->getDb()->testTable() ;
            }
            catch (\Exception $e)
            {
                $this->generateTable();
                $generate = true ;
            }

            if ( isset( $_GET['checkDB'] ) && $generate == false )
            {
                $this->generateTable( false );
            }

            if ( $this->_caching_db == true	)
            {
                $this->getDb()->caching() ;
            }
        }
        catch (\Exception $e)
        {
            $this->viewTemplateError('database' , [
                'exception' => $e,
                'view' => ( isset( $_GET['debug'] ) ? true : false )
            ]) ;
        }
    }

    protected function generateTable( $content = true )
    {
        $Base = new Base;
        $Base->insertBase( $content );
    }

    public function activeDbCaching()
    {
        $this->_caching_db = true ;
    }

    /* ********************************************************* */
    /* ****************     MAINTENANCE      ******************* */
    /* ********************************************************* */

    private function initMaintenance()
    {
        if ( $this->Param()->get('maintenance_active') == "1" && $this->config('config') == 'front' )
        {
            $auth = false ;
            $maintenance_ip = \DB::for_table('param')
                ->where_equal('param_key', 'maintenance_ip')
                ->find_one();

            $list = $maintenance_ip->param_value ;
            $exp  = explode("\n" , $list );

            if ( $exp )
            {
                foreach( $exp as $ip )
                {
                    if ( $ip == $_SERVER['REMOTE_ADDR'] )
                    {
                        $auth = true ;
                    }
                }
            }

            if ( $auth == false )
            {
                $this->viewTemplateError('maintenance') ;
            }
        }
    }

    /* ************************************************** */
    /* ****************     LANG      ******************* */
    /* ************************************************** */

    private function initLang()
    {
        if ( $this->config('config') == 'front' )
        {
            Lang::getInstance()->setFront();
        }
    }

    /* ************************************************** */
    /* ****************   PLUGINS     ******************* */
    /* ************************************************** */

    public function addPlugin( $obj )
    {
        if ( is_object( $obj ) )
        {
            $this->_plugin[] = $obj ;
        }
    }

    private function loadPlugin()
    {
        $this->getSlim()->loadPlugin( $this->getPlugin() ) ;
    }

    /* ************************************************** */
    /* ****************     RUN       ******************* */
    /* ************************************************** */

    public function run( $run = true )
    {
        $this->connectToDatabase() ;
        $this->initMaintenance() ;
        $this->initLang() ;
        $this->initDate() ;
        $this->initSlim() ;
        $this->loadPlugin() ;
        if ( $run ) $this->getSlim()->run() ;
    }

    /* ******************************************************************** */
    /* ****************     CONFIG FILE NOT FOUND       ******************* */
    /* ******************************************************************** */

    protected function configFileNotFound()
    {
        $this->viewTemplateError('configfile') ;
    }

    /* **************************************************** */
    /* ****************     CHMOD       ******************* */
    /* **************************************************** */

    protected function checkChmod()
    {
        $pass = true ;
        $folders = [
            "Project/Lang" => false ,
            "Project/view/front" => false ,
            "Project/Module/Webservice" => false ,
            "Project/Module/Repository/Back" => false ,
            "Project/Module/Repository/Front" => false ,
            "Project/Module/Controller/Back" => false ,
            "Project/Module/Controller/Front" => false ,
            "Project/Controller/Front" => false ,
            "Project/view/front/module" => false ,
            "Project/view/front/page" => false ,
            "cache/back" => false ,
            "cache/front" => false ,
            "cache/save/traduction" => false ,
            "web/uploads" => false ,
            "web/images" => false ,
            "web/images/_lib" => false ,
            "web/images/_lib/t" => false ,
            "web/documents" => false
		] ;

        foreach( $folders as $folder => $passFolder )
        {
            if ( ! is_dir( _PATH_ . "/" . $folder ) ) @mkdir( _PATH_ . "/" . $folder ) ;

            if ( is_dir( _PATH_ . "/" . $folder ) )
            {
                $result = @file_put_contents( _PATH_ . "/" . $folder . "/test.txt" , "Hello!" ) ;

                if ( $result === false )
                {
                    if ( @chmod( _PATH_ . "/" . $folder , 0777 ) === true ) $folders[ $folder ] = true ;
                    else                                                                    $pass = false ;
                }
                else
                {
                    $folders[ $folder ] = true ;
                    @unlink( _PATH_ . "/" . $folder . "/test.txt" ) ;
                }
            }
        }

        if ( $pass == false )
        {
            $this->viewTemplateError('chmod' , [
                'folderTab' => $folders
            ]) ;
        }
    }

    protected function viewTemplateError( $tpl , $arg = [] )
    {
        $this->setParserExtension(new TwigFront);

        if ( $this->config('config') == 'back' && defined('TEMPLATES_COMMON_TECH_PATH') )
        {
            $this->getSlim()->setTemplateFolder(TEMPLATES_COMMON_TECH_PATH);
        }

        $this->initSlim() ;

        $twig = AppContext::twig();
        if ($twig === null) {
            die('Une erreur est survenue');
        }

        $env = $twig->getEnvironment();
        foreach (['errors/' . $tpl . '.twig', 'errors/' . $tpl . '.twig.html'] as $template) {
            if ($env->getLoader()->exists($template)) {
                echo $env->render($template, $arg);
                die;
            }
        }

        die('Une erreur est survenue');
    }
}