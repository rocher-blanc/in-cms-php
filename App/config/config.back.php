<?php
defined('REPOSITORY_PATH') || define('REPOSITORY_PATH', REPOSITORY_PROJECT_PATH . '/Back');
defined('CONTROLLERS_PATH') || define('CONTROLLERS_PATH', APPLICATION_PATH . '/Controller/back');
defined('PROJECT_CONTROLLER_PATH') || define('PROJECT_CONTROLLER_PATH', MODULE_PATH . '/Controller/Back');
defined('CACHE_PATH') || define('CACHE_PATH', _PATH_ . '/cache/back');
defined('TEMPLATES_PATH') || define('TEMPLATES_PATH', APPLICATION_PATH . '/view/back');
defined('VIEW_PROJECT_PATH') || define('VIEW_PROJECT_PATH', PROJECT_PATH . '/view/back');
defined('FORM_PATH') || define('FORM_PATH', KERNEL_PATH . '/Form');
defined('MIDDLEWARE_PROJECT_PATH') || define('MIDDLEWARE_PROJECT_PATH', PROJECT_PATH . '/Middleware/Back');
defined('ASSETS_IMG_PATH') || define('ASSETS_IMG_PATH', WEB_PATH . '/' . \App\Kernel\Install::getAdminFolder() . '/assets/img');
defined('VENDOR_CMS') || define('VENDOR_CMS','cmsmedias');