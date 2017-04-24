<?php

namespace App\Kernel\Common;

class Newsletter
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $group_id = NULL ;
    protected $email    = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setGroupId( $var )
    {
        $this->group_id = $var ;
    }

    public function setEmail( $var )
    {
        $this->email = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getGroupId()
    {
        return $this->group_id ;
    }

    public function getEmail()
    {
        return trim( $this->email );
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isEmailValid()
    {
        if ( filter_var( $this->getEmail() , FILTER_VALIDATE_EMAIL ) && $this->getEmail() !== NULL && $this->getEmail() != '' )
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function subscribe()
    {
        if ( filter_var( $this->getEmail() , FILTER_VALIDATE_EMAIL ) )
        {
            if ( ! $this->exist() )
            {
                $this->insert();
            }
            else
            {
                return false ;
            }
        }
        else
        {
            return false ;
        }
    }

    protected function exist()
    {
        $ct = \DB::for_table('newsletter_sub')
                ->where_equal('newsletter_sub_email', $this->getEmail() )
                ->where_equal('newsletter_sub_newsletter_group_sub_id', $this->getGroupId() )
                ->count();

        if ( $ct == 0 ) return false ;
        else            return true ;
    }

    protected function insert()
    {
        $row = \DB::for_table('newsletter_sub')->create();
        $row->newsletter_sub_newsletter_group_sub_id = $this->getGroupId() ;
        $row->newsletter_sub_email = $this->getEmail() ;
        $row->save();
    }
}