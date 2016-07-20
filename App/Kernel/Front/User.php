<?php

namespace App\Kernel\Front;

class User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */

    protected function isAjax()
    {
        return $this->CMS()->request()->isAjax() ;
    }

    protected function isLogged()
    {
        return ( isset( $_SESSION[ $this->getSessionName() ] ) && !empty( $_SESSION[ $this->getSessionName() ] ) );
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    protected function getSessionName()
    {
        return 'jcontent_user' ;
    }

    public static function getInstance()
    {
        if ( self::$instance === NULL )
        {
            if ( file_exists( CLASS_PROJECT_PATH . '/User.php' ) )  self::$instance = new \Project\CustomClass\User;
            else                                                    self::$instance = new User;
        }
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    protected function text( $key )
    {
        return \App\Kernel\Front\Translate::getInstance()->getText( $key ) ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function post( $key )
    {
        return $this->CMS()->request()->post( $key ) ;
    }

    protected function getIp()
    {
        return $this->CMS()->request()->getIp() ;
    }

    protected function returnError( $key , $result = false )
    {
        if ( $this->isAjax() )
        {
            $this->Factory()->Response()->returnJSON( $this->text( $key ) , $result );
        }
        else
        {
            $this->CMS()->view()->appendData([
                'user_error' => [
                    'result' => $result,
                    'msg' => $this->text( $key )
                ]
            ]);
        }
    }

    public function appendVar()
    {
        $this->CMS()->view()->appendData([
            'user' => [
                'isLogged' => $this->isLogged()
            ]
        ]);
    }

    /* ************************************************** */
    /* ****************    ACTIONS    ******************* */
    /* ************************************************** */

    public function login()
    {
        /*
         * @POST
         * user_login
         * user_password
         */

        if ( ! $this->isLogged() )
        {
            $login    = $this->post('user_login') ;
            $password = $this->post('user_password') ;

            if ( $login !== '' && $password !== '' )
            {
                $login = htmlentities( $login, ENT_QUOTES ) ;

                $user = \DB::for_table('user_front')
                    ->where_equal('user_front_login', $login)
                    ->find_one();

                if ( is_object( $user ) && $user->user_front_login === $login && password_verify( $password , $user->user_front_password ) == true )
                {
                    $this->returnError( "user_login_successful" , true ) ;

                    return $this->save( $user ) ;
                }
                else
                {
                    $this->returnError( "user_login_failed" ) ;

                    return false ;
                }
            }
            else
            {
                $this->returnError( "user_login_field_empty" ) ;

                return false ;
            }
        }
    }

    public function logout()
    {
        if ( $this->isLogged() )
        {
            session_destroy();
            $_SESSION[ $this->getSessionName() ] = [];
        }
    }

    public function register()
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

    public function update()
    {
        if ( $this->isLogged() )
        {

        }
    }

    public function validation()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @GET
             * token
             */
        }
    }

    public function lostPassword()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @POST
             * user_login
             */
        }
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

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
}