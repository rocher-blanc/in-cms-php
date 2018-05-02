<?php
defined('THEME') || define('THEME', 'Default');

defined('REPOSITORY_PATH') || define('REPOSITORY_PATH', REPOSITORY_PROJECT_PATH . '/Front');
defined('CONTROLLERS_PATH') || define('CONTROLLERS_PATH', APPLICATION_PATH . '/Controller/front');
defined('PROJECT_CONTROLLER_PATH') || define('PROJECT_CONTROLLER_PATH', MODULE_PATH . '/Controller/Front');
defined('CACHE_PATH') || define('CACHE_PATH', _PATH_ . '/cache/front');
defined('TEMPLATES_PATH') || define('TEMPLATES_PATH', APPLICATION_PATH . '/view/front/' . THEME );
defined('TEMPLATES_COMMON_TECH_PATH') || define('TEMPLATES_COMMON_TECH_PATH', APPLICATION_PATH . '/view/front/Common' );
defined('VIEW_PROJECT_PATH') || define('VIEW_PROJECT_PATH', PROJECT_PATH . '/view/front' );
defined('CLASS_PROJECT_PATH') || define('CLASS_PROJECT_PATH', PROJECT_PATH . '/CustomClass/Front');
defined('MIDDLEWARE_PROJECT_PATH') || define('MIDDLEWARE_PROJECT_PATH', PROJECT_PATH . '/Middleware/Front');

defined('ADMIN') || define('ADMIN',false);

defined('ASSET_CSS_VAR') || define('ASSET_CSS_VAR',"__asset_css_var__");
defined('ASSET_JS_VAR') || define('ASSET_JS_VAR',"__asset_js_var__");

defined('FB_APP_ID') || define('FB_APP_ID', NULL );
defined('FB_APP_SECRET') || define('FB_APP_SECRET', NULL );
defined('FB_APP_PAGE') || define('FB_APP_PAGE', NULL );