<?php

namespace App\Kernel\Back;

class Log
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $codeArray = [] ;
    private $codeArraySimple = [] ;
    public $_data = NULL ;
    private $_limit = 650 ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {
        $this->setUserId( $_SESSION[ $this->getApp()->config('session') ]['id'] ) ;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    public function setUserId( $id ) {
        $this->_user_id = $id ;
    }

    public function setData( $data ) {
        $this->_data = $data ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Log;

        return self::$instance ;
    }

    private function getApp()
    {
        return \App\Kernel\SlimBridge::getInstance() ;
    }

    public function getUserId()
    {
        return $this->_user_id ;
    }

    private function getCode()
    {
        return [
            1 => "Erreur d'authentification avec le login \"%i\"",
            2 => "Authentification réussie",

            3 => "Suppression de l'utilisateur \"%i\"",
            4 => "Ajout du nouvel utilisateur \"%i\"",
            5 => "Modification de l'utilisateur \"%i\"",

            6 => "Suppression du groupe d'utilisateur \"%i\"",
            7 => "Ajout du nouveau groupe d'utilisateur \"%i\"",
            8 => "Modification du groupe d'utilisateur \"%i\"",
            9 => "Modification de la permission d'ajout (%i)",
            10 => "Modification de la persmission de modification (%i)",
            11 => "Modification de la permission de suppression (%i)",

            12 => "Activation de la langue \"%i\"",
            13 => "Désactivation de la langue \"%i\"",
            14 => "Modification de la langue \"%i\"",
            15 => "Modification de la position (up) pour la langue \"%i\"",
            16 => "Modification de la position (down) pour la langue \"%i\"",

            17 => "Modification du module \"%i\"",
            18 => "Activation du module \"%i\"",
            19 => "Désactivation du module \"%i\"",
            20 => "Suppression du module \"%i\"",
            21 => "Ajout du nouveau module \"%i\"",

            22 => "Suppression du groupe de modules \"%i\"",
            23 => "Ajout du nouveau groupe de modules \"%i\"",
            24 => "Modification du groupe de modules \"%i\"",
            25 => "Activation du groupe de modules \"%i\"",
            26 => "Désactivation du groupe de modules \"%i\"",
            27 => "Modification de la position (up) pour le groupe de modules \"%i\"",
            28 => "Modification de la position (down) pour le groupe de modules \"%i\"",

            29 => "Modification d'une page spéciale \"%i\"",
            30 => "Activation d'une page spéciale \"%i\"",
            31 => "Désactivation d'une page spéciale \"%i\"",
            32 => "Suppression d'une page spéciale \"%i\"",
            33 => "Ajout d'une nouvelle page spéciale \"%i\"",
            34 => "Modification de la page par défaut \"%i\"",

            35 => "Modification d'un menu \"%i\"",
            36 => "Suppression d'un menu \"%i\"",
            37 => "Ajout d'un nouveau menu \"%i\"",

            38 => "Module principal activé \"%i\"",
            39 => "Module principal désactivé \"%i\"",

            40 => "Modification du serveur CDN \"%i\"",

            41 => "Modification des métadonnées",

            42 => "Activation de la langue sur le site \"%i\"",
            43 => "Désactivation de la langue sur le site \"%i\"",

            44 => "Modification du système de maintenance",

            45 => "Le module \"%i\" a été vidé",
            46 => "Le module \"%i\" a été patché",

            47 => "Les images du module \"%i\" viennent d'être regénérées",
            48 => "L'utilisateur front \"%i\" vient d'être supprimé",
            49 => "L'utilisateur front \"%i\" vient d'être modifié",
            50 => "L'utilisateur front \"%i\" vient d'être ajouté",

            51 => "Ajout du domaine \"%i\"",
            52 => "Modification du domaine \"%i\"",
            53 => "Suppression du domaine \"%i\"",

            54 => "Ajout dans l'index d'une page spéciale \"%i\"",
            55 => "Suppression de l'index d'une page spéciale \"%i\"",

            56 => "Ajout (listing) dans l'index du module \"%i\"",
            57 => "Suppression (listing) de l'index du module \"%i\"",
            58 => "Ajout (éléments) dans l'index du module \"%i\"",
            59 => "Suppression (éléments) de l'index du module \"%i\"",

            60 => "Modification des microdatas",

            61 => "Modification des informations RGPD",

            /* MODULES */
            100 => "Ajout d'un nouvel élément \"%i\"",
            101 => "Modification d'un élément \"%i\"",
            102 => "Suppression d'un élément \"%i\"",
            103 => "Activation d'un élément \"%i\"",
            104 => "Désactivation d'un élément \"%i\"",
            105 => "Modification de l'ordre des éléments \"%i\"",

            /* NEWSLETTER */
            /* GROUPES D'ABONNES */
            200 => "Ajout d'un nouveau groupe d'abonné \"%i\"",
            201 => "Modification d'un groupe d'abonné \"%i\"",
            202 => "Suppression d'un groupe d'abonné \"%i\"",
            203 => "Ajout d'abonnés dans un groupe \"%i\"",
            204 => "Suppression d'un abonné dans un groupe \"%i\"",

            205 => "Ajout d'un nouveau gabarit de newsletter \"%i\"",
            206 => "Modification d'un gabarit de newsletter \"%i\"",
            207 => "Suppression d'un gabarit de newsletter \"%i\"",
            208 => "Sauvegarder d'un gabarit de newsletter \"%i\"",

        ];
    }

    private function getCodeSimple()
    {
        return [
            /* MODULES */
            100 => "Ajout",
            101 => "Modification",
            102 => "Suppression",
            103 => "Activation",
            104 => "Désactivation",
            105 => "Modification de l'ordre"
        ];
    }

    private function getType()
    {
        return $this->_data->log_type ;
    }

    private function getDate()
    {
        $time = strtotime( $this->_data->log_date ) ;
        return strftime( "%d %B %Y" , $time ) . " à " . strftime( "%Hh%M" , $time ) ;
    }

    private function getUser()
    {
        if ( !empty( $this->_data->log_user_id ) )
        {
            return \DB::for_table('user')
                ->select('user_name')
                ->select('user_fname')
                ->select('user_lname')
                ->where_equal('user_id',$this->_data->log_user_id)
                ->find_one() ;
        }
        else
        {
            return "" ;
        }
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    private function delest()
    {
        $ct = \DB::for_table('log')->count() ;

        if ( $ct > $this->_limit )
        {
            $delta = $ct - $this->_limit ;

            $min = \DB::for_table('log')
                ->select('log_id')
                ->limit(1)
                ->offset($delta)
                ->order_by_asc('log_id')
                ->find_one();

            \DB::for_table('log')
                ->where_lt( 'log_id' , $min->log_id )
                ->delete_many() ;
        }
    }

    public function log( $type , $code , $value , $module = null , $element = null )
    {
        $date = new \DateTime() ;

        $ct = \DB::for_table('log')
            ->where_date('log_date',$date->format('Y-m-d'))
            ->count() ;

        if ( $ct == 0 ) $this->delest() ;

        $log = \DB::for_table('log')->create();
        $log->log_addr 		    = $_SERVER['REMOTE_ADDR'] ;
        $log->log_host 		    = gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) ;
        $log->log_type 		    = $type ;
        $log->log_code 		    = $code ;
        $log->log_value 	    = $value ;
        $log->log_user_id 	    = $this->getUserId() ;
        $log->log_module_id 	= $module ;
        $log->log_element_id 	= $element ;
        $log->log_date 		    = $date->format('Y-m-d H:i:s') ;
        $log->save();
    }

    public function info( $code , $value = NULL , $module = null , $element = null )
    {
        return $this->log( 1 , $code , $value , $module , $element ) ;
    }

    public function warning( $code , $value = NULL , $module = null , $element = null )
    {
        return $this->log( 2 , $code , $value , $module , $element ) ;
    }

    public function alert( $code , $value = NULL , $module = null , $element = null )
    {
        return $this->log( 3 , $code , $value , $module , $element ) ;
    }

    /* ************************************************** */
    /* ****************      VIEW     ******************* */
    /* ************************************************** */

    private function parseCode( $elmt = false )
    {
        if ( $elmt == false )
        {
            if ( empty( $this->codeArray ) ) $this->codeArray = $this->getCode() ;

            if ( array_key_exists( $this->_data->log_code , $this->codeArray ) ) 	return str_replace( "%i" , $this->_data->log_value , $this->codeArray[ $this->_data->log_code ] ) ;
            else																	return "Aucun message pour le code erreur : " . $this->_data->log_code . ' / ' . $this->_data->log_value ;
        }
        else
        {
            if ( empty( $this->codeArraySimple ) ) $this->codeArraySimple = $this->getCodeSimple() ;

            return $this->codeArraySimple[ $this->_data->log_code ] ;
        }

    }

    public function parse( $elmt = false )
    {
        $std = new \stdClass;
        $std->msg   = $this->parseCode( $elmt ) ;
        $std->type = $this->getType() ;
        $std->date = $this->getDate() ;
        $std->user = $this->getUser() ;

        return $std ;
    }

    public function getElementHistory( $module , $element )
    {
        $logRows = \DB::for_table('log')
            ->where_equal('log_module_id' , $module )
            ->where_equal('log_element_id' , $element )
            ->order_by_desc('log_date')
            ->find_many() ;

        $rows = [] ;
        if ( $logRows )
        {
            foreach( $logRows as $row ) {
                $this->setData( $row ) ;
                $rows[] = $this->parse( true ) ;
            }
        }

        return $rows ;
    }
}