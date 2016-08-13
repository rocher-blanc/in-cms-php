<?php

defined('APP_NAME') || define('APP_NAME', 'App');
defined('ENTITIES_PATH') || define('ENTITIES_PATH', APPLICATION_PATH . '/Entities');
defined('PROXIES_PATH') || define('PROXIES_PATH', APPLICATION_PATH . '/Proxies');
defined('KERNEL_PATH') || define('KERNEL_PATH', APPLICATION_PATH . '/Kernel');
defined('CONFIG_PATH') || define('CONFIG_PATH', APPLICATION_PATH . '/config');
defined('FACTORY_PATH') || define('FACTORY_PATH', KERNEL_PATH . '/Factory');
defined('PROJECT_PATH') || define('PROJECT_PATH', _PATH_ . '/Project');
defined('LANG_PATH') || define('LANG_PATH', PROJECT_PATH . '/Lang');
defined('CONTROLLER_PROJECT_PATH') || define('CONTROLLER_PROJECT_PATH', PROJECT_PATH . '/Controller/Front');
defined('CONTROLLER_FOLDERS_PATH') || define('CONTROLLER_FOLDERS_PATH', serialize( [ CONTROLLER_PROJECT_PATH ] ) );
defined('CONFIG_PROJECT_PATH') || define('CONFIG_PROJECT_PATH', PROJECT_PATH . '/config');
defined('MODULE_PATH') || define('MODULE_PATH', PROJECT_PATH . '/Module');
defined('ENTITY_PATH') || define('ENTITY_PATH', MODULE_PATH . '/Entity');
defined('ENTITIES_PROJECT_PATH') || define('ENTITIES_PROJECT_PATH', ENTITY_PATH . '/Class');
defined('REPOSITORY_PROJECT_PATH') || define('REPOSITORY_PROJECT_PATH', MODULE_PATH . '/Repository');
 
defined('WEB_PATH') || define('WEB_PATH', _PATH_ . '/web');
defined('ASSET_PATH') || define('ASSET_PATH', WEB_PATH . '/assets');
defined('VENDOR_PATH') || define('VENDOR_PATH', ASSET_PATH . '/vendor');
defined('BOWER_PATH') || define('BOWER_PATH', VENDOR_PATH . '/bower-asset');

defined('IMAGE_PATH') || define('IMAGE_PATH', WEB_PATH . '/images');
defined('DOCUMENT_PATH') || define('DOCUMENT_PATH', WEB_PATH . '/documents');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', WEB_PATH . '/uploads');

$configFileProject = PROJECT_PATH . '/config/config.php' ;

if ( file_exists( $configFileProject ) )
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',true);
    require $configFileProject ;
}
else
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',false);
}

defined('COUNTRY') || define('COUNTRY','fr');
defined('SESSION_LIFETIME') || define('SESSION_LIFETIME', 1500 );

if ( defined('DB_HOST') && defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_DATABASE') )
{
    if ( DB_HOST == '' || DB_USER == '' || DB_DATABASE == '' )
    {
        defined('DB') || define('DB',false);
    }
    else
    {
        defined('DB') || define('DB',true);
    }
}
else
{
    defined('DB') || define('DB',false);
}

if ( ! defined('ACTIVE_USER') )
{
    define('ACTIVE_USER', false );
}

// if ( ! defined('DB_HOST') ||! defined('DB_USER') ||! defined('DB_PASSWORD') ||! defined('DB_DATABASE') ) \App\Kernel\Factory::getInstance()->Response()->error('No information for database') ;
// if ( ! defined('DEBUG') ) \App\Kernel\Factory::getInstance()->Response()->error('No information for debug') ;
if ( ! defined('DEBUG') )
{
    define('SLIM_MODE', 'development');
}
else
{
    define('SLIM_MODE', ( DEBUG !== true ? 'production' : 'development' ) );
}

require CONFIG_PATH . '/ini.php' ;