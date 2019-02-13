<?php

namespace App\Kernel\Common;

use App\Kernel\Front\Data;

class Newsletter
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $group_id = NULL ;
    protected $email    = NULL ;
    protected $error    = NULL ;

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

    public function setError( $var )
    {
        $this->error = $var ;
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

    public function getError()
    {
        return \App\Kernel\Front\Translate::getInstance()->getText( $this->error );
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isEmailValid()
    {
        if ( filter_var( $this->getEmail() , FILTER_VALIDATE_EMAIL ) && $this->getEmail() !== NULL && $this->getEmail() != '' )
        {
            return true ;
        }
        else
        {
            return false ;
        }
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function subscribe()
    {
        if ( $this->isEmailValid() )
        {
            if ( ! $this->exist() )
            {
                $this->insert();

                return true ;
            }
            else
            {
                $this->setError('email_already_exist');
                return false ;
            }
        }
        else
        {
            $this->setError('email_is_invalid');
            return false ;
        }
    }

    protected function exist()
    {
        $data = new Data('NewsletterSubscriber');
        $ct = $data->count([
            'email' => $this->getEmail(),
            'parent' => $this->getGroupId()
        ]);

        if ( $ct == 0 ) return false ;
        else            return true ;
    }

    protected function insert()
    {
        $data = new Data('NewsletterSubscriber');
        $data->create([
            'email' => $this->getEmail(),
            'parent' => $this->getGroupId()
        ]);
        $data->save();
    }
}