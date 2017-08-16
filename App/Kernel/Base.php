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

	public function insertBase( $content = true )
    {
        $this->checkDatabase() ;

        if ( $content )
        {
            \DB::get_db()->exec( $this->getSql() ) ;
            \DB::get_db()->exec( $this->getTrigger() ) ;
        }
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
        $passJweb = '$2y$09$Qlpl8n.Mzv8yv46kqBrWSuIxb7suyS8iZ1uaZUk3cCfutlRwKQeve' ;

        return "INSERT INTO `extension` (`extension_technical_name`, `extension_name`, `extension_perm_add`, `extension_perm_update`, `extension_perm_delete`, `extension_user`) VALUES
('user', 'Utilisateurs', 1, 1, 1, 0),
('group', 'Groupes d\'utilisateurs', 1, 1, 1, 0),
('langue', 'Langues', 0, 1, 0, 0),
('menu', 'Menu', 1, 1, 1, 0),
('page', 'Pages spéciales', 1, 1, 1, 0),
('parammodule', 'Modules', 0, 1, 0, 0),
('user_front', 'Utilisateurs', 1, 1, 1, 1),
('user_front_group', 'Groupes d\'utilisateurs', 1, 1, 1, 1);

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
  
INSERT INTO `param` (`param_key`, `param_value`) VALUES
('seo_author', 'JWeb Création'),
('security_lock_ip', '0'),
('seo_geo_region', NULL),
('seo_geo_placename', NULL),
('seo_geo_position', NULL),
('seo_geo_icbm', NULL),
('seo_google_webmaster_tools', NULL),
('seo_bing_webmaster_tools', NULL),
('seo_google_analytics', NULL),
('seo_robots', '0'),
('seo_divers_header', NULL),
('seo_divers_footer', NULL),
('seo_www', 0),
('seo_ssl', 0),
('server_cdn', NULL),
('maintenance_ip', NULL),
('maintenance_active', 0),
('security_list_ip', NULL);

INSERT INTO `user` (`user_group_id`, `user_name`, `user_password`, `user_fname`, `user_lname`, `user_type`, `user_published`) VALUES
(1, 'Jammye', '" . $passGuillaume . "', 'Guillaume', 'DEVELTER', 1, 1),
(1, 'paul-henri', '" . $passPH . "', 'Paul-Henri', 'Blanc', 1, 1),
(1, 'jweb', '" . $passJweb . "', 'JWeb', 'JWeb', 1, 1);

INSERT INTO `user_group` (`user_group_id`, `user_group_name`, `user_group_url`, `user_group_redirect`) VALUES
(1, 'Administrateurs', '/.+;/', '/admin/'),
(2, 'Utilisateurs', '/.+;/', '/admin/');";
    }

    public function checkDatabase()
    {
        $rst = \DB::for_table('')->raw_query("SHOW TABLES")->find_many();
        if ( $rst )
        {
            $array = [];
            foreach( $rst as $value )
            {
                if ( substr( $value->get( 'Tables_in_' . DB_DATABASE ) , 0 , 4 ) != "mod_" )
                {
                    $array[ $value->get( 'Tables_in_' . DB_DATABASE ) ] = $value->get( 'Tables_in_' . DB_DATABASE ) ;
                }

                \DB::get_db()->exec('OPTIMIZE TABLE `' . $value->get( 'Tables_in_' . DB_DATABASE ) . '`') ;
            }
        }

        foreach( $this->getInformationColumn() as $table => $columns )
        {
            if ( array_key_exists( $table , $array ) )
            {
                // on check les colonnes
                $dbColumns = \DB::getColumnsTable( $table );

                if ( $columns )
                {
                    foreach( $columns as $columnName => $info )
                    {
                        if ( ! array_key_exists( $columnName , $dbColumns ) )
                        {
                            // on créer la colonnes
                            \DB::alterSimpleColumn( $table , $columnName , $info );
                        }/*
                        else
                        {
                            $type = ( $info['value'] != '' ? $info['type'] . "(" . $info['value'] . ")" : $info['type'] )
                            if ( $dbColumns[ $columnName ]['type'] != $type )
                            {
                                // on modifie le type
                            }
                        }*/
                    }
                }
            }
            else
            {
                // on créer la table
                \DB::createSimpleTable( $table , $columns );
            }
        }
    }

    public function infoColumn( $type , $value = NULL , $default = NULL , $empty = false , $increment = false )
    {
        return [
            "type"      => $type,
            "value"     => $value,
            "default"   => $default,
            "empty"     => $empty,
            "increment" => $increment
        ];
    }

    public function getInformationColumn()
    {
        return [
            "domain" => [
                "domain_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "domain_name" => $this->infoColumn( "VARCHAR" , "255" )
            ],
            "extension" => [
                "extension_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "extension_name" => $this->infoColumn( "VARCHAR" , "100" ),
                "extension_technical_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "extension_add" => $this->infoColumn( "TINYINT" , "1" , 1 ),
                "extension_update" => $this->infoColumn( "TINYINT" , "1" , 1 ),
                "extension_delete" => $this->infoColumn( "TINYINT" , "1" , 1 ),
                "extension_user" => $this->infoColumn( "TINYINT" , "1" , 0 )
            ],
            "gallery" => [
                "gallery_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "gallery_module_id" => $this->infoColumn( "INT" , "11" ),
                "gallery_element_id" => $this->infoColumn( "INT" , "11" ),
                "gallery_field" => $this->infoColumn( "VARCHAR" , "30" ),
                "gallery_name" => $this->infoColumn( "VARCHAR" , "255" ),
                "gallery_size" => $this->infoColumn( "INT" , "11" ),
                "gallery_type" => $this->infoColumn( "VARCHAR" , "100" ),
                "gallery_position" => $this->infoColumn( "INT" , "11" )
            ],
            "lang" => [
                "lang_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "lang_display" => $this->infoColumn( "VARCHAR" , "255" ),
                "lang_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "lang_url" => $this->infoColumn( "VARCHAR" , "255" ),
                "lang_flag" => $this->infoColumn( "VARCHAR" , "2" ),
                "lang_locale" => $this->infoColumn( "VARCHAR" , "5" ),
                "lang_status" => $this->infoColumn( "INT" , "11" ),
                "lang_front" => $this->infoColumn( "TINYINT" , "1" )
            ],
            "log" => [
                "log_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "log_date" => $this->infoColumn( "DATETIME" , '' ),
                "log_user_id" => $this->infoColumn( "INT" , "11" ),
                "log_code" => $this->infoColumn( "TINYINT" , "1" ),
                "log_type" => $this->infoColumn( "TINYINT" , "1" ),
                "log_value" => $this->infoColumn( "VARCHAR" , "255" )
            ],
            "media" => [
                "media_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "media_module_id" => $this->infoColumn( "INT" , "11" ),
                "media_name" => $this->infoColumn( "VARCHAR" , "255" ),
                "media_size" => $this->infoColumn( "INT" , "11" ),
                "media_type" => $this->infoColumn( "VARCHAR" , "100" ),
            ],
            "media_alt" => [
                "media_alt_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "media_alt_module_id" => $this->infoColumn( "INT" , "11" ),
                "media_alt_element_id" => $this->infoColumn( "INT" , "11" ),
                "media_alt_lang_id" => $this->infoColumn( "INT" , "11" ),
                "media_alt_field_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "media_alt_value" => $this->infoColumn( "VARCHAR" , "500" ),
            ],
            "menu" => [
                "menu_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "menu_name" => $this->infoColumn( "VARCHAR" , "150" )
            ],
            "menu_element" => [
                "menu_element_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "menu_element_menu_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_parent_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_order" => $this->infoColumn( "INT" , "11" ),
                "menu_element_type" => $this->infoColumn( "ENUM" , "'module', 'page', 'link', 'section'" ),
                "menu_element_link_blank" => $this->infoColumn( "TINYINT" , "1" ),
                "menu_element_link_href" => $this->infoColumn( "VARCHAR" , "255" ),
                "menu_element_module_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_value_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_max_level" => $this->infoColumn( "TINYINT" , "1" ),
                "menu_element_has_submenu" => $this->infoColumn( "TINYINT" , "1" ),
                "menu_element_option" => $this->infoColumn( "ENUM" , "'one,'all'" )
            ],
            "menu_element_lang" => [
                "menu_element_lang_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "menu_element_lang_lang_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_lang_menu_element_id" => $this->infoColumn( "INT" , "11" ),
                "menu_element_lang_label" => $this->infoColumn( "VARCHAR" , "255" )
            ],
            "module" => [
                "module_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "module_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_class_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_active" => $this->infoColumn( "TINYINT" , "255" ),
                "module_icon" => $this->infoColumn( "VARCHAR" , "20" ),
                "module_module_group_id" => $this->infoColumn( "INT" , "11" ),
                "module_order" => $this->infoColumn( "INT" , "11" ),
                "module_default" => $this->infoColumn( "TINYINT" , "1" ),
                "module_priority" => $this->infoColumn( "FLOAT" , "" ),
                "module_index" => $this->infoColumn( "TINYINT" , "1" ),
                "module_index_elmt" => $this->infoColumn( "TINYINT" , "1" )
            ],
            "module_group" => [
                "module_group_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "module_group_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_group_icon" => $this->infoColumn( "VARCHAR" , "15" ),
                "module_group_order" => $this->infoColumn( "INT" , "11" ),
                "module_group_active" => $this->infoColumn( "TINYINT" , "1" , '0' )
            ],
            "newsletter_sender" => [
                "newsletter_sender_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "newsletter_sender_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "newsletter_sender_email" => $this->infoColumn( "VARCHAR" , "150" )
            ],
            "newsletter_sub" => [
                "newsletter_sub_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "newsletter_sub_email" => $this->infoColumn( "VARCHAR" , "255" ),
                "newsletter_sub_state" => $this->infoColumn( "TINYINT" , "1" ),
                "newsletter_sub_newsletter_group_sub_id" => $this->infoColumn( "INT" , "11" )
            ],
            "newsletter_group_sub" => [
                "newsletter_group_sub_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "newsletter_group_sub_name" => $this->infoColumn( "VARCHAR" , "50" )
            ],
            "page" => [
                "page_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "page_domain_id" => $this->infoColumn( "INT" , "11" ),
                "page_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "page_default" => $this->infoColumn( "TINYINT" , "1" ),
                "page_active" => $this->infoColumn( "TINYINT" , "1" ),
                "page_priority" => $this->infoColumn( "FLOAT" ),
                "page_index" => $this->infoColumn( "TINYINT" , "1" ),
                "page_access_user" => $this->infoColumn( "TINYINT" , "1" ),
                "page_access_user_group" => $this->infoColumn( "TEXT" ),
                "page_access_user_redirect" => $this->infoColumn( "INT" , "11" )
            ],
            "page_lang" => [
                "page_lang_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "page_lang_lang_id" => $this->infoColumn( "INT" , "11" ),
                "page_lang_page_id" => $this->infoColumn( "INT" , "11" ),
                "page_lang_url" => $this->infoColumn( "VARCHAR" , "255" ),
                "page_lang_title" => $this->infoColumn( "VARCHAR" , "255" ),
                "page_lang_description" => $this->infoColumn( "VARCHAR" , "255" )
            ],
            "param" => [
                "param_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "param_key" => $this->infoColumn( "VARCHAR" , "50" ),
                "param_value" => $this->infoColumn( "TEXT" )
            ],
            "permission" => [
                "permission_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "permission_group_id" => $this->infoColumn( "INT" , "11" ),
                "permission_value" => $this->infoColumn( "VARCHAR" , "5" ),
                "permission_extension_id" => $this->infoColumn( "INT" , "11" ),
                "permission_module_id" => $this->infoColumn( "INT" , "11" )
            ],
            "redirect" => [
                "redirect_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "domain_name" => $this->infoColumn( "VARCHAR" , "255" )
            ],
            "seo" => [
                "seo_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "seo_module_id" => $this->infoColumn( "INT" , "11" ),
                "seo_element_id" => $this->infoColumn( "INT" , "11" ),
                "seo_lang_id" => $this->infoColumn( "INT" , "11" ),
                "seo_url" => $this->infoColumn( "VARCHAR" , "255" ),
                "seo_title" => $this->infoColumn( "VARCHAR" , "255" ),
                "seo_description" => $this->infoColumn( "VARCHAR" , "255" ),
                "seo_index" => $this->infoColumn( "TINYINT" , "1" )
            ],
            "user" => [
                "user_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_group_id" => $this->infoColumn( "INT" , "11" ),
                "user_name" => $this->infoColumn( "VARCHAR" , "150" ),
                "user_password" => $this->infoColumn( "VARCHAR" , "60" ),
                "user_fname" => $this->infoColumn( "VARCHAR" , "150" ),
                "user_lname" => $this->infoColumn( "VARCHAR" , "150" ),
                "user_type" => $this->infoColumn( "INT" , "11" ),
                "user_published" => $this->infoColumn( "TINYINT" , "1" )
            ],
            "user_group" => [
                "user_group_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_group_name" => $this->infoColumn( "VARCHAR" , "150" ),
                "user_group_url" => $this->infoColumn( "TEXT" ),
                "user_group_redirect" => $this->infoColumn( "VARCHAR" , "250" )
            ],
            "user_front" => [
                "user_front_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_front_login" => $this->infoColumn( "VARCHAR" , "100" ),
                "user_front_password" => $this->infoColumn( "VARCHAR" , "60" ),
                "user_front_token" => $this->infoColumn( "VARCHAR" , "34" ),
                "user_front_active" => $this->infoColumn( "TINYINT" , "1" ),
                "user_front_user_front_group_id" => $this->infoColumn( "INT" , "11" ),
                "user_front_date_created" => $this->infoColumn( "DATETIME" ),
                "user_front_last_connection" => $this->infoColumn( "DATETIME" )
            ],
            "user_front_group" => [
                "user_front_group_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_front_group_name" => $this->infoColumn( "VARCHAR" , "50" )
            ],
            "user_front_profile" => [
                "user_front_profile_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_front_profile_user_front_id" => $this->infoColumn( "INT" , "11" )
            ]
        ];
    }
}