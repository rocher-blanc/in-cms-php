<?php

namespace App\Kernel;

class Lang
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;

    private $_lang 		= [] ;
    private $_tab 		= [] ;
    private $_url 		= [] ;
    private $_default 	= NULL ;
    private $_active 	= NULL ;
    private $_count 	= 0 ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->loadActiveLang() ;
        $this->setActive( $this->getDefault() ) ;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    private function setLang( $obj )
    {
        if ( is_object( $obj ) )
        {
            $this->_lang[ $obj->id ] = $obj ;
        }
    }

    private function setDefault( $obj )
    {
        $this->_default = $obj ;
    }

    public function setUrl( $id_lang , $url , $preffix = true )
    {
        $this->_url[ $id_lang ] = \App\Kernel\Http::getInstance()->getUrl() . '/' ;

        if ( $url == $this->getDefault()->url && $id_lang == $this->getDefault()->id )  $this->_url[ $id_lang ].= '' ;
        else                                                                            $this->_url[ $id_lang ].= ( $preffix == true ? $this->get( $id_lang )->url . "/" : '' ) . $url ;
    }

    public function setActive( $obj )
    {
        $this->_active = $obj ;
    }

    public function setTabLang( $id )
    {
        $this->_tab[ $id ] = $id ;
    }

    public function setFront()
    {
        $this->_lang = [];
        $this->loadActiveLang( true ) ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getTabLang()
    {
        return $this->_tab ;
    }

    public function getAll()
    {
        return $this->_lang ;
    }

    public function getDefault()
    {
        return $this->_default ;
    }

    public function getActive()
    {
        return $this->_active ;
    }

    public function count()
    {
        return $this->_count ;
    }

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Lang;
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function get( $id )
    {
        return $this->_lang[ $id ] ;
    }

    private function loadActiveLang( $front = false )
    {
        $rows = \DB::for_table('lang')
            ->where_gt('lang_status', '0')
            ->order_by_desc('lang_status');

        if ( $front == true ) $rows = $rows->where_equal('lang_front', 1);

        $rows = $rows->find_many();

        $i = 0;
        foreach( $rows as $r )
        {
            $langObj 		    = new \stdClass();
            $langObj->id	    = $r->lang_id;
            $langObj->url 	    = $r->lang_url;
            $langObj->full_url  = $this->_url[ intval( $r->lang_id ) ] ;
            $langObj->name 	    = $r->lang_display;
            $langObj->locale 	= $r->lang_locale;
            $langObj->flag 	    = $r->lang_flag;

            $this->setTabLang( $r->lang_id ) ;
            $this->setLang( $langObj );

            if ( $i == 0 ) $this->setDefault( $langObj );
            $i++;
        }

        $this->_count = count( $this->getAll() ) ;
    }
}