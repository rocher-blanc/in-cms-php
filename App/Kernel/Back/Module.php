<?php

namespace App\Kernel\Back;

use App\Kernel\Container;
use App\Kernel\Factory;
use App\Kernel\Lang;

class Module
{
    function install( $name , $namespace = null , $folder = null )
    {
        $contentRow = \DB::for_table('module')->create();
        $contentRow->module_name 		= $name ;
        $contentRow->module_class_name 	= $name ;
        $contentRow->module_icon 		= "icon-question" ;
        $contentRow->module_active 		= 1 ;
        $contentRow->save() ;

        $moduleId = $contentRow->module_id;

        if( Container::getInstance()->module( $name )->getEntity()->hasUrl() )
        {
            foreach( Lang::getInstance()->getAll() as $lang )
            {
                $req = \DB::for_table('module_lang')
                    ->where_equal( 'module_lang_lang_id' , $lang->id )
                    ->where_equal( 'module_lang_module_id' , $moduleId )
                    ->find_one();

                if( ! $req )
                {
                    $url = Factory::getInstance()->Url()->encode( $name );
                    $url = Factory::getInstance()->Url()->uniq($url, $lang->id);

                    $req = \DB::for_table('module_lang')->create();
                    $req->module_lang_lang_id   = $lang->id;
                    $req->module_lang_module_id = $moduleId;
                    $req->module_lang_url       = $url;
                    $req->save();
                }
            }
        }

        // On génère le webservice
        $php = '' ;
        $php.= "<"."?"."php\n\n" ;
        $php.= "namespace Project\Module\Webservice;\n\n" ;
        $php.= "use App\Kernel\Front\WebserviceModule;\n\n" ;
        $php.= "class " . $name . " extends WebserviceModule\n" ;
        $php.= "{\n" ;
        $php.= "\t\n" ;
        $php.= "}" ;
        if ( ! file_exists( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" , $php );

        $tab = ["Back","Front"];

        // On génére le repository
        foreach( $tab as $row )
        {
            $php = '' ;
            $php.= "<"."?"."php\n\n" ;
            $php.= "namespace Project\Module\Repository\\" . $row . ";\n\n" ;

            if ( ! file_exists( $folder . "/Repository/" . $row . "/" . $name . ".php" ) )  $php.= "use App\Kernel\\" . $row . "\Repository;\n\n" ;
            else                                                                                    $php.= "use " . $namespace . "\Repository\\" . $row . "\\" . $name . " as Repository;\n\n" ;

            $php.= "class " . $name . " extends Repository\n" ;
            $php.= "{\n" ;
            $php.= "\t\n" ;
            $php.= "}" ;
            if ( ! file_exists( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" , $php );
        }

        // On génére le controller
        foreach( $tab as $row )
        {
            $php = '' ;
            $php.= "<"."?"."php\n\n" ;
            $php.= "namespace Project\Module\Controller\\" . $row . ";\n\n" ;

            if ( ! file_exists( $folder . "/Repository/" . $row . "/" . $name . ".php" ) )  $php.= "use App\Kernel\\" . $row . "\Controller;\n\n" ;
            else                                                                                    $php.= "use " . $namespace . "\Controller\\" . $row . "\\" . $name . " as Controller;\n\n" ;

            $php.= "class " . $name . " extends Controller\n" ;
            $php.= "{\n" ;
            $php.= "\t\n" ;
            $php.= "}" ;
            if ( ! file_exists( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" , $php );
        }

        Container::getInstance()->module( $name )->getRepository( true )->checkDatabase();
        Container::getInstance()->param()->set('key_module_' . $contentRow->module_id , md5_file( ENTITY_PATH . "/" . $contentRow->module_class_name . ".php" ) );

        return $moduleId;
    }
}