<?php

namespace App\Kernel\Front;

class User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    protected function getSessionName()
    {
        return 'jcontent_user' ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function post( $key )
    {
        return \App\Kernel\CMS::getInstance()->request()->post( $key ) ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL )
        {
            if ( file_exists( CLASS_PROJECT_PATH . '/User.php' ) )  self::$instance = new \Project\CustomClass\User;
            else                                                    self::$instance = new User;
        }
        return self::$instance ;
    }

    protected function login()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @POST
             * user_login
             * user_password
             */

            // on utilise password_verify()

            $login    = $this->post('user_login') ;
            $password = $this->post('user_password') ;

            if ( $login !== '' && $password !== '' )
            {
                $login = htmlentities($login, ENT_QUOTES) ;

                $user = \DB::for_table('user_front')
                    ->where_equal('user_front_login', $login)
                    ->find_one();

                if ( is_object( $user ) && $user->user_front_login === $login && password_verify( $password , $user->user_front_password ) == true )
                {
                    return $this->save( $user ) ;
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
    }

    protected function save( $user )
    {
        $_SESSION[ $this->getSessionName() ] = [
            'id' 		=> $user->user_front_id,
            'login' 	=> $user->user_front_login,
            'group_id' 	=> $user->user_front_group_id,
            'logged_in' => true,
            'ip' 		=> $this->getIp()
        ];
    }

    protected function logout()
    {
        if ( $this->isLogged() )
        {
            session_destroy();
            $_SESSION[ $this->getSessionName() ] = [];
        }
    }

    protected function register()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @POST
             *
             */

            // on utilise pour le mot de passe : password_hash( $password , PASSWORD_BCRYPT , ['cost' => 9] ) ;
        }
    }

    protected function update()
    {
        if ( $this->isLogged() )
        {

        }
    }

    protected function validation()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @GET
             * token
             */
        }
    }

    protected function lostPassword()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @POST
             * user_login
             */
        }
    }

    protected function isLogged()
    {
        return ( isset( $_SESSION[ $this->getSessionName() ] ) && !empty( $_SESSION[ $this->getSessionName() ] ) );
    }
}