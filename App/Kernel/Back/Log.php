<?php

namespace App\Kernel\Back;

class Log
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $codeArray = array() ;
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
        return \Slim\Slim::getInstance() ;
    }

    public function getUserId()
    {
        return $this->_user_id ;
    }

    private function getCode()
    {
        return array(
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

            44 => "Modification du thème de l'administration",

            45 => "Le module \"%i\" a été vidé",
            46 => "Le module \"%i\" a été patché",
        );
    }

    private function getType()
    {
        return $this->_data->log_type ;
    }

    private function getDate()
    {
        $date = new \DateTime( $this->_data->log_date ) ;
        return $date->format('d/m/Y - H:i:s') ;
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

    public function log( $type , $code , $value )
    {
        $date = new \DateTime() ;

        $ct = \DB::for_table('log')
            ->where_date('log_date',$date->format('Y-m-d'))
            ->count() ;

        if ( $ct == 0 ) $this->delest() ;

        $log = \DB::for_table('log')->create();
        $log->log_type 		= $type ;
        $log->log_code 		= $code ;
        $log->log_value 	= $value ;
        $log->log_user_id 	= $this->getUserId() ;
        $log->log_date 		= $date->format('Y-m-d H:i:s') ;
        $log->save();
    }

    public function info( $code , $value = NULL )
    {
        return $this->log( 1 , $code , $value ) ;
    }

    public function warning( $code , $value = NULL )
    {
        return $this->log( 2 , $code , $value ) ;
    }

    public function alert( $code , $value = NULL )
    {
        return $this->log( 3 , $code , $value ) ;
    }

    /* ************************************************** */
    /* ****************      VIEW     ******************* */
    /* ************************************************** */

    private function parseCode()
    {
        if ( empty( $this->codeArray ) ) $this->codeArray = $this->getCode() ;

        if ( array_key_exists( $this->_data->log_code , $this->codeArray ) ) 	return str_replace( "%i" , $this->_data->log_value , $this->codeArray[ $this->_data->log_code ] ) ;
        else																	return "Aucun message pour le code erreur : " . $this->_data->log_code . ' / ' . $this->_data->log_value ;

    }

    public function parse()
    {
        $std = new \stdClass;
        $std->msg   = $this->parseCode() ;
        $std->type = $this->getType() ;
        $std->date = $this->getDate() ;
        $std->user = $this->getUser() ;

        return $std ;
    }

}