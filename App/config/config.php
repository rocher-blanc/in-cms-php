<?php

defined('APP_NAME') || define('APP_NAME', 'App');
defined('KERNEL_PATH') || define('KERNEL_PATH', APPLICATION_PATH . '/Kernel');
defined('CONFIG_PATH') || define('CONFIG_PATH', APPLICATION_PATH . '/config');
defined('V_MODULE_PATH') || define('V_MODULE_PATH', APPLICATION_PATH . '/Module');
defined('V_ENTITY_PATH') || define('V_ENTITY_PATH', V_MODULE_PATH . '/Entity');
defined('FACTORY_PATH') || define('FACTORY_PATH', KERNEL_PATH . '/Factory');
defined('PROJECT_PATH') || define('PROJECT_PATH', _PATH_ . '/Project');
defined('LANG_PATH') || define('LANG_PATH', PROJECT_PATH . '/Lang');
defined('CONTROLLER_PROJECT_PATH') || define('CONTROLLER_PROJECT_PATH', PROJECT_PATH . '/Controller/Front');
defined('CONTROLLER_FOLDERS_PATH') || define('CONTROLLER_FOLDERS_PATH', serialize( [ CONTROLLER_PROJECT_PATH ] ) );
defined('CONFIG_PROJECT_PATH') || define('CONFIG_PROJECT_PATH', PROJECT_PATH . '/config');
defined('MODULE_PATH') || define('MODULE_PATH', PROJECT_PATH . '/Module');
defined('FORM_PROJECT_PATH') || define('FORM_PROJECT_PATH', PROJECT_PATH . '/Form');
defined('ENTITY_PATH') || define('ENTITY_PATH', MODULE_PATH . '/Entity');
defined('ENTITIES_PROJECT_PATH') || define('ENTITIES_PROJECT_PATH', ENTITY_PATH . '/Class');
defined('REPOSITORY_PROJECT_PATH') || define('REPOSITORY_PROJECT_PATH', MODULE_PATH . '/Repository');
defined('WEBSERVICE_PROJECT_PATH') || define('WEBSERVICE_PROJECT_PATH', MODULE_PATH . '/Webservice');
defined('TEMPLATES_COMMON_TECH_PATH') || define('TEMPLATES_COMMON_TECH_PATH', APPLICATION_PATH . '/view/front/Common' );
defined('FORM_PATH') || define('FORM_PATH', KERNEL_PATH . '/Form');
defined('SAVE_PATH') || define('SAVE_PATH', _PATH_ . '/cache/save');


/* COMMON */
defined('CLASS_PROJECT_COMMON_PATH') || define('CLASS_PROJECT_COMMON_PATH', PROJECT_PATH . '/CustomClass/Common');
defined('VIEW_PROJECT_COMMON_PATH') || define('VIEW_PROJECT_COMMON_PATH', PROJECT_PATH . '/view/common');
defined('TEMPLATES_COMMON_PATH') || define('TEMPLATES_COMMON_PATH', APPLICATION_PATH . '/view/common');

defined('WEB_PATH') || define('WEB_PATH', _PATH_ . '/web');
defined('ASSET_PATH') || define('ASSET_PATH', WEB_PATH . '/assets');
defined('VENDOR_PATH') || define('VENDOR_PATH', ASSET_PATH . '/vendor');
defined('BOWER_PATH') || define('BOWER_PATH', VENDOR_PATH . '/bower-asset');

defined('IMAGE_PATH') || define('IMAGE_PATH', WEB_PATH . '/images');
defined('DOCUMENT_PATH') || define('DOCUMENT_PATH', WEB_PATH . '/documents');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', WEB_PATH . '/uploads');

$configFileProject          = PROJECT_PATH . '/config/config.php' ;
$configFileProjectDomain    = PROJECT_PATH . '/config/config.' . $_SERVER['HTTP_HOST'] . '.php' ;
$configFileProjectDomainWww = PROJECT_PATH . '/config/config.www.' . $_SERVER['HTTP_HOST'] . '.php' ;

if ( file_exists( $configFileProject ) )
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',true);
    require $configFileProject ;
}
else if ( file_exists( $configFileProjectDomainWww ) )
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',true);
    require $configFileProjectDomainWww ;
}
else if ( file_exists( $configFileProjectDomain ) )
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',true);
    require $configFileProjectDomain ;
}
else
{
    defined('FILE_CONFIG') || define('FILE_CONFIG',false);
}

if ( file_exists( PROJECT_PATH . '/config/config.common.php' ) )
{
    require PROJECT_PATH . '/config/config.common.php' ;
}

defined('COUNTRY') || define('COUNTRY','fr');
defined('TIMEZONE') || define('TIMEZONE','Europe/Paris');

//
defined('VENDOR_CMS') || define('VENDOR_CMS','cmsmedias');
defined('TECHNO') || define('TECHNO','easyDOOR');

// DEBUG
defined('DEBUG_BAR') || define('DEBUG_BAR', false );
defined('PRODUCTION') || define('PRODUCTION', false );

// SLACK
defined('SLACK_WEBHOOK') || define('SLACK_WEBHOOK', 'https://hooks.slack.com/services/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' );
defined('SLACK_EMOJI') || define('SLACK_EMOJI', ":jweb:" );
defined('SLACK_AUTHORNAME') || define('SLACK_AUTHORNAME', "JWeb" );
defined('SLACK_USERNAME') || define('SLACK_USERNAME', "JWeb-Bot" );
defined('SLACK_COLOR') || define('SLACK_COLOR', "#ffab40" );


// REDIS
defined('REDIS') || define('REDIS', false );
defined('REDIS_SERVER') || define('REDIS_SERVER', '' );
defined('REDIS_PORT') || define('REDIS_PORT', '' );

//RABBIT
defined('RABBIT_QUEUE') || define('RABBIT_QUEUE', serialize([]) );

// SESSION
defined('SESSION_LIFETIME') || define('SESSION_LIFETIME', 1500 );
defined('COOKIE_EXPIRES') || define('COOKIE_EXPIRES', 2592000 ); // 30 jours

// USER
defined('ACTIVE_USER') || define('ACTIVE_USER', false );
defined('ACTIVE_USER_CONNECT_AFTER_REGISTER') || define('ACTIVE_USER_CONNECT_AFTER_REGISTER', false );
defined('ACTIVE_EMAILING') || define('ACTIVE_EMAILING', false );
defined('USER_ACTIVATION_MAIL') || define('USER_ACTIVATION_MAIL', false );

// NEWSLETTER
defined('NEWSLETTER_ACTIVE') || define('NEWSLETTER_ACTIVE', false );

// MAIL
defined('MAIL_HTML') || define('MAIL_HTML', true );
defined('MAIL_SMTP') || define('MAIL_SMTP', false );
defined('SMTP_DEBUG') || define('SMTP_DEBUG', false );
defined('MAIL_SMTP_HOST') || define('MAIL_SMTP_HOST', "" );
defined('MAIL_SMTP_USER') || define('MAIL_SMTP_USER', "" );
defined('MAIL_SMTP_PASSWORD') || define('MAIL_SMTP_PASSWORD', "" );
defined('MAIL_SMTP_PORT') || define('MAIL_SMTP_PORT', 587 );
defined('MAIL_SMTP_SECURE') || define('MAIL_SMTP_SECURE', 'tls' );

// DATABASE
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

if ( ! defined('DEBUG') )
{
    define('SLIM_MODE', 'development');
}
else
{
    define('SLIM_MODE', ( PRODUCTION === true ? 'production' : 'development' ) );
}

require CONFIG_PATH . '/ini.php' ;