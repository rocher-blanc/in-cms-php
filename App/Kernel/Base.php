<?php

namespace App\Kernel;

class Base
{
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */

	public function insertBase()
    {
        \DB::get_db()->exec( $this->getSql() ) ;
        \DB::get_db()->exec( $this->getTrigger() ) ;
    }

    public function getTrigger()
    {
        return str_replace("\r", "", 'CREATE TRIGGER `after_delete_menu_element` AFTER DELETE ON `menu_element` FOR EACH ROW BEGIN DELETE FROM menu_element_lang WHERE menu_element_lang_menu_element_id = old.menu_element_id; END;
CREATE TRIGGER `after_delete_page` AFTER DELETE ON `page` FOR EACH ROW BEGIN DELETE FROM page_lang WHERE page_lang_page_id = old.page_id; END;
CREATE TRIGGER `after_delete_extension` AFTER DELETE ON `extension` FOR EACH ROW BEGIN DELETE FROM permission WHERE permission_extension_id = old.extension_id; END;
CREATE TRIGGER `after_delete_module_group` AFTER DELETE ON `module_group` FOR EACH ROW BEGIN UPDATE module SET module_module_group_id = NULL WHERE module_module_group_id = old.module_group_id; END;
CREATE TRIGGER `after_delete_module` AFTER DELETE ON `module` FOR EACH ROW BEGIN DELETE FROM param WHERE param_key = CONCAT(\'key_module_\',old.module_id); DELETE FROM module_lang WHERE module_lang_module_id = old.module_id; END;
CREATE TRIGGER `after_delete_user_front` AFTER DELETE ON `user_front` FOR EACH ROW BEGIN DELETE FROM user_front_profile WHERE user_front_profile_user_front_id = old.user_front_id; END;
CREATE TRIGGER `after_delete_menu` AFTER DELETE ON `menu` FOR EACH ROW BEGIN DELETE FROM menu_element WHERE menu_element_menu_id = old.menu_id; END;
CREATE TRIGGER `after_delete_user_group` AFTER DELETE ON `user_group` FOR EACH ROW BEGIN DELETE FROM permission WHERE permission_group_id = old.user_group_id; END;');
    }

    public function getSql()
    {
        $passGuillaume = '$2y$09$RizAnNLsExTvYdridNHjSe3KaY8YT5/2ErA6UMHCoezhEV3vYzpIG' ;
        $passPH = '$2y$09$RizAnNLsExTvYdridNHjSe3KaY8YT5/2ErA6UMHCoezhEV3vYzpIG' ;

        return "
CREATE TABLE `domain` (
  `domain_id` int(11) NOT NULL,
  `domain_name` varchar(255) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `extension` (
  `extension_id` int(11) NOT NULL,
  `extension_technical_name`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `extension_name`  varchar(100) COLLATE utf8_general_ci NOT NULL,
  `extension_perm_add` tinyint(1) NOT NULL DEFAULT '1',
  `extension_perm_update` tinyint(1) NOT NULL DEFAULT '1',
  `extension_perm_delete` tinyint(1) NOT NULL DEFAULT '1',
  `extension_user` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `document` (
  `document_id` int(11) NOT NULL,
  `document_module_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `document_size` int(11) NOT NULL,
  `document_type` varchar(100) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `extension` (`extension_id`, `extension_technical_name`, `extension_name`, `extension_perm_add`, `extension_perm_update`, `extension_perm_delete`, `extension_user`) VALUES
(1, 'user', 'Utilisateurs', 1, 1, 1, 0),
(2, 'group', 'Groupes d\'utilisateurs', 1, 1, 1, 0),
(3, 'langue', 'Langues', 0, 1, 0, 0),
(5, 'menu', 'Menu', 1, 1, 1, 0),
(6, 'page', 'Pages spéciales', 1, 1, 1, 0),
(7, 'parammodule', 'Modules', 0, 1, 0, 0),
(8, 'user_front', 'Utilisateurs', 1, 1, 1, 1),
(9, 'user_front_group', 'Groupes d\'utilisateurs', 1, 1, 1, 1);

CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL,
  `gallery_module_id` int(11) DEFAULT NULL,
  `gallery_element_id` int(11) NOT NULL,
  `gallery_field`  varchar(30) COLLATE utf8_general_ci NOT NULL,
  `gallery_name`  varchar(255) COLLATE utf8_general_ci NOT NULL,
  `gallery_size` int(11) NOT NULL,
  `gallery_type`  varchar(100) COLLATE utf8_general_ci NOT NULL,
  `gallery_position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lang` (
  `lang_id` int(11) NOT NULL,
  `lang_display` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `lang_name` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `lang_url` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `lang_flag` varchar(2) COLLATE utf8_general_ci DEFAULT NULL,
  `lang_locale` varchar(5) COLLATE utf8_general_ci DEFAULT NULL,
  `lang_status` int(11) DEFAULT NULL,
  `lang_front` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `lang` (`lang_id`, `lang_display`, `lang_name`, `lang_url`, `lang_flag`, `lang_locale`, `lang_status`, `lang_front`) VALUES
(1, 'Français', 'Français', 'fr', 'fr', 'fr', 1, 1),
(2, 'English', 'Anglais', 'en', 'gb', 'en', 0, 0),
(3, 'Italiano', 'Italien', 'it', 'it', 'it', 0, 0),
(4, 'Español', 'Espagnol', 'es', 'es', 'es', 0, 0),
(5, 'Deutch', 'Allemand', 'de', 'de', 'de', 0, 0),
(6, 'русский', 'Russe', 'ru', 'ru', 'ru', 0, 0),
(7, '华人', 'Chinois', 'cn', 'cn', 'cn', 0, 0),
(8, 'Português', 'Portugais', 'pt', 'pt', 'pt', 0, 0),
(9, 'Nederlander', 'Néerlandais', 'nl', 'nl', 'nl', 0, 0),
(10, '日本人', 'Japonnais', 'jp', 'jp', 'jp', 0, 0),
(11, 'Polak', 'Polonais', 'pl', 'pl', 'pl', 0, 0),
(12, 'عربي', 'Arabe', 'ar', 'ar', 'ar', 0, 0);

CREATE TABLE `log` (
  `log_id` int(11) NOT NULL,
  `log_date` datetime NOT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_code` tinyint(1) DEFAULT NULL,
  `log_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Info, 2 = Warning, 3 = Alert',
  `log_value`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `media` (
  `media_id` int(11) NOT NULL,
  `media_module_id` int(11) DEFAULT NULL,
  `media_name`  varchar(255) COLLATE utf8_general_ci NOT NULL,
  `media_size` int(11) NOT NULL,
  `media_type`  varchar(100) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `menu_name` varchar(150) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `menu_element` (
  `menu_element_id` int(11) NOT NULL,
  `menu_element_menu_id` int(11) NOT NULL,
  `menu_element_parent_id` int(11) DEFAULT NULL,
  `menu_element_order` int(11) NOT NULL,
  `menu_element_type` enum('module','page','link','section') NOT NULL,
  `menu_element_link_blank` tinyint(1) DEFAULT '0',
  `menu_element_link_href`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `menu_element_module_id` int(11) DEFAULT NULL,
  `menu_element_value_id` int(11) DEFAULT NULL,
  `menu_element_max_level` tinyint(1) DEFAULT NULL,
  `menu_element_has_submenu` tinyint(1) DEFAULT '0',
  `menu_element_option` enum('one','all') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `menu_element_lang` (
  `menu_element_lang_id` int(11) NOT NULL,
  `menu_element_lang_lang_id` int(11) NOT NULL,
  `menu_element_lang_menu_element_id` int(11) NOT NULL,
  `menu_element_lang_label`  varchar(255) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `module` (
  `module_id` int(11) NOT NULL,
  `module_name`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `module_class_name`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `module_active` tinyint(1) NOT NULL DEFAULT '0',
  `module_icon`  varchar(20) COLLATE utf8_general_ci NOT NULL,
  `module_module_group_id` int(11) DEFAULT NULL,
  `module_order` int(11) DEFAULT NULL,
  `module_default` tinyint(1) NOT NULL DEFAULT '0',
  `module_priority` float NOT NULL DEFAULT '0.5',
  `module_index` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `module_group` (
  `module_group_id` int(11) NOT NULL,
  `module_group_name`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `module_group_icon`  varchar(15) COLLATE utf8_general_ci NOT NULL,
  `module_group_order` int(11) NOT NULL,
  `module_group_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `module_lang` (
  `module_lang_id` int(11) NOT NULL,
  `module_lang_lang_id` int(11) NOT NULL,
  `module_lang_module_id` int(11) NOT NULL,
  `module_lang_url`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `module_lang_title`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `module_lang_description`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `module_lang_keyword`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `page` (
  `page_id` int(11) NOT NULL,
  `page_domain_id` int(11) NULL DEFAULT NULL,
  `page_name`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `page_default` tinyint(1) NOT NULL DEFAULT '0',
  `page_active` tinyint(1) NOT NULL DEFAULT '0',
  `page_priority` float NOT NULL DEFAULT '0.5',
  `page_index` tinyint(1) NOT NULL DEFAULT '0',
  `page_access_user` TINYINT(1) NOT NULL DEFAULT '0',
  `page_access_user_group` TEXT NULL DEFAULT NULL,
  `page_access_user_redirect` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `page_lang` (
  `page_lang_id` int(11) NOT NULL,
  `page_lang_lang_id` int(11) NOT NULL,
  `page_lang_page_id` int(11) NOT NULL,
  `page_lang_url`  varchar(255) COLLATE utf8_general_ci NOT NULL,
  `page_lang_title`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `page_lang_description`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `page_lang_keyword`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `param` (
  `param_id` int(11) NOT NULL,
  `param_key`  varchar(50) COLLATE utf8_general_ci NOT NULL,
  `param_value` text 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `param` (`param_id`, `param_key`, `param_value`) VALUES
(1, 'seo_author', 'JWeb Création'),
(18, 'security_lock_ip', '0'),
(5, 'seo_geo_region', NULL),
(6, 'seo_geo_placename', NULL),
(7, 'seo_geo_position', NULL),
(8, 'seo_geo_icbm', NULL),
(9, 'seo_google_webmaster_tools', NULL),
(10, 'seo_bing_webmaster_tools', NULL),
(11, 'seo_google_analytics', NULL),
(12, 'seo_robots', '0'),
(13, 'seo_divers_header', NULL),
(14, 'seo_divers_footer', NULL),
(15, 'server_cdn', NULL),
(19, 'security_list_ip', NULL);

CREATE TABLE `permission` (
  `permission_id` int(11) NOT NULL,
  `permission_group_id` int(11) NOT NULL,
  `permission_value` varchar(5) COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `permission_extension_id` int(11) DEFAULT NULL,
  `permission_module_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `seo` (
  `seo_id` int(11) NOT NULL,
  `seo_module_id` int(11) NOT NULL,
  `seo_element_id` int(11) NOT NULL,
  `seo_lang_id` int(11) NOT NULL,
  `seo_url`  varchar(255) COLLATE utf8_general_ci NOT NULL,
  `seo_title`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `seo_description`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `seo_keyword`  varchar(255) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_group_id` int(11) DEFAULT NULL,
  `user_name` varchar(150) COLLATE utf8_general_ci DEFAULT NULL,
  `user_password` varchar(60) COLLATE utf8_general_ci DEFAULT NULL,
  `user_fname` varchar(150) COLLATE utf8_general_ci DEFAULT NULL,
  `user_lname` varchar(150) COLLATE utf8_general_ci DEFAULT NULL,
  `user_type` int(11) DEFAULT NULL,
  `user_published` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`user_id`, `user_group_id`, `user_name`, `user_password`, `user_fname`, `user_lname`, `user_type`, `user_published`) VALUES
(1, 1, 'Jammye', '" . $passGuillaume . "', 'Guillaume', 'DEVELTER', 1, 1),
(2, 1, 'paul-henri', '" . $passPH . "', 'Paul-Henri', 'Blanc', 1, 1);

CREATE TABLE `user_front` (
  `user_front_id` int(11) NOT NULL,
  `user_front_login` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `user_front_password` varchar(60) COLLATE utf8_general_ci NOT NULL,
  `user_front_token` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `user_front_active` tinyint(1) NOT NULL DEFAULT '0',
  `user_front_user_front_group_id` int(11) NOT NULL,
  `user_front_date_created` datetime DEFAULT NULL,
  `user_front_last_connection` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `user_front_group` (
  `user_front_group_id` int(11) NOT NULL,
  `user_front_group_name` varchar(50) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `user_front_profile` (
  `user_front_profile_id` int(11) NOT NULL,
  `user_front_profile_user_front_id` int(11) NOT NULL,
  `user_front_profile_mod_entreprise_id` int(11) NOT NULL,
  `user_front_profile_nom` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `user_front_profile_prenom` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `user_front_profile_telephone` varchar(30) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `user_group` (
  `user_group_id` int(11) NOT NULL,
  `user_group_name` varchar(150) COLLATE utf8_general_ci DEFAULT NULL,
  `user_group_url` text,
  `user_group_redirect` varchar(250) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user_group` (`user_group_id`, `user_group_name`, `user_group_url`, `user_group_redirect`) VALUES
(1, 'Administrateurs', '/.+;/', '/admin/'),
(3, 'Utilisateurs', '/.+;/', '/admin/');

ALTER TABLE `domain`
  ADD PRIMARY KEY (`domain_id`);

ALTER TABLE `extension`
  ADD PRIMARY KEY (`extension_id`);

ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`);

ALTER TABLE `lang`
  ADD PRIMARY KEY (`lang_id`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`);

ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`);

ALTER TABLE `menu_element`
  ADD PRIMARY KEY (`menu_element_id`);

ALTER TABLE `menu_element_lang`
  ADD PRIMARY KEY (`menu_element_lang_id`);

ALTER TABLE `module`
  ADD PRIMARY KEY (`module_id`);

ALTER TABLE `module_group`
  ADD PRIMARY KEY (`module_group_id`);

ALTER TABLE `module_lang`
  ADD PRIMARY KEY (`module_lang_id`),
  ADD KEY `module_lang_url` (`module_lang_url`);

ALTER TABLE `page`
  ADD PRIMARY KEY (`page_id`);

ALTER TABLE `page_lang`
  ADD PRIMARY KEY (`page_lang_id`);

ALTER TABLE `param`
  ADD PRIMARY KEY (`param_id`);

ALTER TABLE `permission`
  ADD PRIMARY KEY (`permission_id`);

ALTER TABLE `seo`
  ADD PRIMARY KEY (`seo_id`),
  ADD KEY `seo_lang_url` (`seo_url`),
  ADD KEY `seo_lang_lang_id` (`seo_lang_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `user_front`
  ADD PRIMARY KEY (`user_front_id`);

ALTER TABLE `user_front_group`
  ADD PRIMARY KEY (`user_front_group_id`);

ALTER TABLE `user_front_profile`
  ADD PRIMARY KEY (`user_front_profile_id`);

ALTER TABLE `user_group`
  ADD PRIMARY KEY (`user_group_id`);
  
ALTER TABLE `document`
  ADD PRIMARY KEY (`document_id`);

ALTER TABLE `domain`
  MODIFY `domain_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `document`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `extension`
  MODIFY `extension_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lang`
  MODIFY `lang_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu_element`
  MODIFY `menu_element_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu_element_lang`
  MODIFY `menu_element_lang_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `module`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `module_group`
  MODIFY `module_group_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `module_lang`
  MODIFY `module_lang_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `page`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `page_lang`
  MODIFY `page_lang_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `param`
  MODIFY `param_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `permission`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `seo`
  MODIFY `seo_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_front`
  MODIFY `user_front_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_front_group`
  MODIFY `user_front_group_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_front_profile`
  MODIFY `user_front_profile_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `domain`
  MODIFY `domain_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(11) NOT NULL AUTO_INCREMENT;";
    }
}