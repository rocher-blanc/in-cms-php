<?php

namespace App\Kernel;

use App\Kernel\Back\Translate;

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

        $this->checkModule();
    }

    public function getModule()
    {
        return [
            'NewsletterSender' => [
                'name' => Translate::getInstance()->getText('sender'),
                'icon' => "icon-line2-users",
            ],
            'NewsletterGroup' => [
                'name' => Translate::getInstance()->getText('grp_abo' ),
                'icon' => "icon-users",
            ],
            'NewsletterSubscriber' => [
                'name' => Translate::getInstance()->getText('abonnes' ),
                'icon' => "icon-user",
            ],
            'NewsletterCampaignGroup' => [
                'name' => Translate::getInstance()->getText('newsletter_plur' ),
                'icon' => "icon-folder",
            ],
            'NewsletterCampaign' => [
                'name' => Translate::getInstance()->getText('newsletter_planning' ),
                'icon' => "icon-time",
            ],
            'NewsletterModel' => [
                'name' => Translate::getInstance()->getText('gabarit' ),
                'icon' => "icon-picture",
            ],
            'EdAutomation' => [
                'name' => Translate::getInstance()->getText('email_automation' ),
                'icon' => "icon-picture",
            ],
            'EdAutomationVarGroup' => [
                'name' => Translate::getInstance()->getText('grp_variables' ),
                'icon' => "icon-stack2",
            ],
            'EdAutomationVar' => [
                'name' => Translate::getInstance()->getText('variables_environnement' ),
                'icon' => "icon-atom",
            ],
            'EdAutomationModelGroup' => [
                'name' => Translate::getInstance()->getText('grp_gabarits' ),
                'icon' => "icon-stack",
            ],
            'EdAutomationModel' => [
                'name' => Translate::getInstance()->getText('model_gabarits' ),
                'icon' => "icon-vcard",
            ],
            'EdEmail' => [
                'name' => Translate::getInstance()->getText('modeles_email' ),
                'icon' => "icon-news",
            ],
            'EdAutomationHistory' => [
                'name' => Translate::getInstance()->getText('historique_email_automation' ),
                'icon' => "icon-line-clock",
            ],
            'NewsletterCampaignGroupUnsubscribe' => [
                'name' => Translate::getInstance()->getText('desinscription' ),
                'icon' => "icon-enter",
            ],
            'GalleryCategory' => [
                'name' => Translate::getInstance()->getText('categories' ),
                'icon' => "icon-line-grid",
            ]
        ];
    }

    public function checkModule()
    {
        foreach( $this->getModule() as $class => $row )
        {
            $rst = \DB::for_table('module')
                ->where_equal( 'module_class_name' , $class )
                ->where_equal( 'module_kernel' , 1 )
                ->find_one();

            if ( ! $rst ) $rst = \DB::for_table('module')->create();

            $rst->module_class_name = $class ;
            $rst->module_kernel = 1 ;
            $rst->module_active = 1 ;
            $rst->module_name = $row['name'] ;
            $rst->module_icon = $row['icon'] ;
            $rst->save();

            \App\Kernel\Container::getInstance()->module( $class )->getRepository( true )->checkDatabase();
        }
    }

    public function getTrigger()
    {
        return str_replace("\r", "", 'CREATE TRIGGER `after_delete_page` AFTER DELETE ON `page` FOR EACH ROW BEGIN DELETE FROM page_lang WHERE page_lang_page_id = old.page_id; END;
CREATE TRIGGER `after_delete_extension` AFTER DELETE ON `extension` FOR EACH ROW BEGIN DELETE FROM permission WHERE permission_extension_id = old.extension_id; END;
CREATE TRIGGER `after_delete_module_group` AFTER DELETE ON `module_group` FOR EACH ROW BEGIN UPDATE module SET module_module_group_id = NULL WHERE module_module_group_id = old.module_group_id; END;
CREATE TRIGGER `after_delete_module` AFTER DELETE ON `module` FOR EACH ROW BEGIN DELETE FROM param WHERE param_key = CONCAT(\'key_module_\',old.module_id); DELETE FROM module_lang WHERE module_lang_module_id = old.module_id; END;
CREATE TRIGGER `after_delete_user_group` AFTER DELETE ON `user_group` FOR EACH ROW BEGIN DELETE FROM permission WHERE permission_group_id = old.user_group_id; END;');
    }

    public function getSql()
    {
        $passGuillaume = '$2y$09$RizAnNLsExTvYdridNHjSe3KaY8YT5/2ErA6UMHCoezhEV3vYzpIG' ;
        $passJweb = '$2y$09$Qlpl8n.Mzv8yv46kqBrWSuIxb7suyS8iZ1uaZUk3cCfutlRwKQeve' ;

        return "INSERT INTO `extension` (`extension_technical_name`, `extension_name`, `extension_perm_add`, `extension_perm_update`, `extension_perm_delete`, `extension_user`) VALUES
('user', 'Administrateurs', 1, 1, 1, 0),
('group', 'Groupes d\'administrateurs', 1, 1, 1, 0),
('langue', 'Langues', 0, 1, 0, 0),
('page', 'Pages spéciales', 1, 1, 1, 0),
('parammodule', 'Modules', 0, 1, 0, 0),
('user_front', 'Utilisateurs', 1, 1, 1, 1),
('user_front_group', 'Groupes d\'utilisateurs', 1, 1, 1, 1);

INSERT INTO `lang` (`lang_id`, `lang_display`, `lang_name`, `lang_url`, `lang_flag`, `lang_locale`, `lang_status`, `lang_front`, `lang_back`) VALUES
(1, 'Français', 'Français', 'fr', 'fr', 'fr', 1, 1, 1),
(2, 'English', 'Anglais', 'en', 'gb', 'en', 0, 0, 1),
(3, 'Italiano', 'Italien', 'it', 'it', 'it', 0, 0, 0),
(4, 'Español', 'Espagnol', 'es', 'es', 'es', 0, 0, 0),
(5, 'Deutch', 'Allemand', 'de', 'de', 'de', 0, 0, 0),
(6, 'русский', 'Russe', 'ru', 'ru', 'ru', 0, 0, 0),
(7, '华人', 'Chinois', 'cn', 'cn', 'cn', 0, 0, 0),
(8, 'Português', 'Portugais', 'pt', 'pt', 'pt', 0, 0, 0),
(9, 'Nederlander', 'Néerlandais', 'nl', 'nl', 'nl', 0, 0, 0),
(10, '日本人', 'Japonnais', 'jp', 'jp', 'jp', 0, 0, 0),
(11, 'Polak', 'Polonais', 'pl', 'pl', 'pl', 0, 0, 0);
  
INSERT INTO `param` (`param_key`, `param_value`) VALUES
('seo_author', 'JWeb Création'),
('security_lock_ip', '0'),
('el_credits', '0'),
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

INSERT INTO `user` (`user_group_id`, `user_name`, `user_password`, `user_fname`, `user_lname`, `user_published`, `user_lang_id`) VALUES
(1, 'Jammye', '" . $passGuillaume . "', 'Guillaume', 'DEVELTER', 1, 1),
(1, 'jweb', '" . $passJweb . "', 'JWeb', 'JWeb', 1, 1);

INSERT INTO `user_group` (`user_group_id`, `user_group_name`) VALUES
(1, 'Administrateurs'),
(2, 'Utilisateurs');

INSERT INTO `user_front_group` (`user_front_group_id`, `user_front_group_name`) VALUES 
(1, 'Défaut');";
    }

    public function checkDatabase()
    {
        $array = [];

        $rst = \DB::for_table('')->raw_query("SHOW TABLES")->find_many();
        if ( $rst )
        {
            foreach( $rst as $value )
            {
                if ( substr( $value->get( 'Tables_in_' . DB_DATABASE ) , 0 , 4 ) != "mod_" )
                {
                    $array[ $value->get( 'Tables_in_' . DB_DATABASE ) ] = $value->get( 'Tables_in_' . DB_DATABASE ) ;
                }

                // \DB::get_db()->exec('OPTIMIZE TABLE `' . $value->get( 'Tables_in_' . DB_DATABASE ) . '`') ;
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
			"document" => [
				"document_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
				"document_name" => $this->infoColumn( "VARCHAR" , "255" ),
				"document_title" => $this->infoColumn( "VARCHAR" , "255" , NULL , true ),
				"document_size" => $this->infoColumn( "INT" , "11" ),
				"document_type" => $this->infoColumn( "VARCHAR" , "50" ),
				"document_module_id" => $this->infoColumn( "INT" , "11" )
			],
			"domain" => [
				"domain_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
				"domain_name" => $this->infoColumn( "VARCHAR" , "255" )
			],
            "extension" => [
                "extension_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "extension_name" => $this->infoColumn( "VARCHAR" , "100" ),
                "extension_technical_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "extension_perm_add" => $this->infoColumn( "TINYINT" , "1" , 1 ),
                "extension_perm_update" => $this->infoColumn( "TINYINT" , "1" , 1 ),
                "extension_perm_delete" => $this->infoColumn( "TINYINT" , "1" , 1 ),
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
                "lang_back" => $this->infoColumn( "TINYINT" , "1" ),
                "lang_front" => $this->infoColumn( "TINYINT" , "1" ),
            ],
            "log" => [
                "log_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "log_date" => $this->infoColumn( "DATETIME" ),
                "log_user_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "log_module_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "log_element_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "log_code" => $this->infoColumn( "TINYINT" , "1" ),
                "log_type" => $this->infoColumn( "TINYINT" , "1" ),
                "log_value" => $this->infoColumn( "VARCHAR" , "255" , NULL , true )
            ],
            "media" => [
                "media_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "media_module_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "media_field" => $this->infoColumn( "VARCHAR" , "50" , NULL , true ),
                "media_name" => $this->infoColumn( "VARCHAR" , "255" ),
                "media_size" => $this->infoColumn( "INT" , "11" ),
                "media_gallery" => $this->infoColumn( "INT" , "11" , NULL , true ),
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
            "module" => [
                "module_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "module_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_class_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_active" => $this->infoColumn( "TINYINT" , "1" ),
                "module_kernel" => $this->infoColumn( "TINYINT" , "1" , 0 ),
                "module_icon" => $this->infoColumn( "VARCHAR" , "20" ),
                "module_module_column_block_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "module_order" => $this->infoColumn( "INT" , "11" ),
                "module_default" => $this->infoColumn( "TINYINT" , "1" ),
                "module_priority" => $this->infoColumn( "FLOAT" ),
                "module_index" => $this->infoColumn( "TINYINT" , "1" ),
                "module_index_elmt" => $this->infoColumn( "TINYINT" , "1" )
            ],
			"module_column" => [
				"module_column_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
				"module_column_module_group_id" => $this->infoColumn( "INT" , "11" )
			],
			"module_column_block" => [
				"module_column_block_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
				"module_column_block_module_column_id" => $this->infoColumn( "INT" , "11" ),
				"module_column_block_order" => $this->infoColumn( "INT" , "11" ),
				"module_column_block_title" => $this->infoColumn( "VARCHAR" , "100" , NULL , true )
			],
			"module_lang" => [
				"module_lang_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
				"module_lang_lang_id" => $this->infoColumn( "INT" , "11" ),
				"module_lang_module_id" => $this->infoColumn( "INT" , "11" ),
				"module_lang_url" => $this->infoColumn( "VARCHAR" , "255" , NULL , true ),
				"module_lang_title" => $this->infoColumn( "VARCHAR" , "255" , NULL , true ),
				"module_lang_description" => $this->infoColumn( "VARCHAR" , "255" , NULL , true )
			],
            "module_group" => [
                "module_group_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "module_group_name" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_group_order" => $this->infoColumn( "INT" , "11" ),
                "module_group_active" => $this->infoColumn( "TINYINT" , "1" , '0' )
            ],
            "module_table" => [
                "module_table_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "module_table_field" => $this->infoColumn( "VARCHAR" , "50" ),
                "module_table_module_id" => $this->infoColumn( "INT" , "11" ),
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
                "page_lang_title" => $this->infoColumn( "VARCHAR" , "255" , NULL , true ),
                "page_lang_description" => $this->infoColumn( "VARCHAR" , "255" , NULL , true )
            ],
            "param" => [
                "param_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "param_key" => $this->infoColumn( "VARCHAR" , "50" ),
                "param_value" => $this->infoColumn( "TEXT" , NULL , NULL , true )
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
                "redirect_page_id" => $this->infoColumn( "INT" , "11" , NULL , true ),
                "redirect_module_id" => $this->infoColumn( "INT" , "11" ),
                "redirect_element_id" => $this->infoColumn( "INT" , "11" ),
                "redirect_lang_id" => $this->infoColumn( "INT" , "11" ),
                "redirect_url" => $this->infoColumn( "VARCHAR" , "255" )
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
                "user_lang_id" => $this->infoColumn( "INT" , "11" , 1 , false),
                "user_published" => $this->infoColumn( "TINYINT" , "1" )
            ],
            "user_group" => [
                "user_group_id" => $this->infoColumn( "INT" , "11" , NULL , false , true ),
                "user_group_name" => $this->infoColumn( "VARCHAR" , "150" )
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
            ]
        ];
    }
}