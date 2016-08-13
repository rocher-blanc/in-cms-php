<?php

namespace App\Kernel\Front;

class User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    protected $id    = NULL;
    protected $login = NULL;
    protected $group = NULL;
    protected $_var  = [];

    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */

    protected function isAjax()
    {
        return $this->CMS()->request()->isAjax() ;
    }

    public function isLogged()
    {
        return ( isset( $_SESSION[ $this->getSessionName() ] ) && !empty( $_SESSION[ $this->getSessionName() ] ) );
    }

    protected function isBadIp()
    {
        if ( isset( $_SESSION[ $this->getSessionName() ]['ip'] ) && $this->getIp() === $_SESSION[ $this->getSessionName() ]['ip'] ) return false ;
        else																	   													return true ;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    protected function setId( $var )
    {
        $this->setVar( 'id' , $var );
        $this->id = $var ;
    }

    protected function setLogin( $var )
    {
        $this->setVar( 'login' , $var );
        $this->login = $var ;
    }

    protected function setGroup( $var )
    {
        $this->setVar( 'group' , $var );
        $this->group = $var ;
    }

    protected function setVar( $key , $value )
    {
        $this->_var[ $key ] = $value ;
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    protected function getSessionName()
    {
        return 'jcontent_user' ;
    }

    protected function getId()
    {
        return $this->id ;
    }

    protected function getVar()
    {
        return $this->_var ;
    }

    protected function getLogin()
    {
        return $this->login ;
    }

    protected function getGroup()
    {
        return $this->group ;
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

    protected function get( $key )
    {
        return $this->CMS()->request()->get( $key ) ;
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
            die;
        }
        else
        {
            $this->CMS()->view()->appendData([
                'user_error' => [
                    'action' => $this->post('user_action'),
                    'result' => $result,
                    'msg' => $this->text( $key )
                ]
            ]);

            return $result ;
        }
    }

    protected function returnRedirect( $url )
    {
        if ( $this->isAjax() )
        {
            $this->Factory()->Response()->returnJSON( '' , true );
            die;
        }
        else
        {
            $this->Factory()->Response()->redirect( $url );
        }
    }

    public function appendVar()
    {
        $this->CMS()->view()->appendData([
            'user' => array_merge([
                'id'        => $this->getId(),
                'login'     => $this->getLogin(),
                'group'     => $this->getGroup(),
                'isLogged'  => $this->isLogged()
            ], $this->getVar() )
        ]);
    }

    /* ************************************************** */
    /* ****************    ACTIONS    ******************* */
    /* ************************************************** */

    ###################################################################################################################################
    ######################################                  LOGIN                    ##################################################
    ###################################################################################################################################

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
                    $date = new \DateTime();
                    $user->user_front_last_connection = $date->format('Y-m-d H:i:s');
                    $user->save();

                    $this->returnError( "user_login_successful" , true ) ;
                    return $this->save( $user ) ;
                }
                else
                {
                    return $this->returnError( "user_login_failed" ) ;
                }
            }
            else
            {
                return $this->returnError( "user_login_field_empty" ) ;
            }
        }
        else
        {
            $this->returnRedirect('/');
        }
    }

    ###################################################################################################################################
    ######################################                  LOGOUT                   ##################################################
    ###################################################################################################################################

    public function logout()
    {
        if ( $this->isLogged() )
        {
            session_destroy();
            $_SESSION[ $this->getSessionName() ] = [];

            $this->returnRedirect('/');
        }
        else
        {
            $this->returnRedirect('/');
        }
    }

    ###################################################################################################################################
    ######################################                REGISTER                   ##################################################
    ###################################################################################################################################

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

    ###################################################################################################################################
    ######################################                  UPDATE                   ##################################################
    ###################################################################################################################################

    public function update()
    {
        if ( $this->isLogged() )
        {
            /*
             * POST
             *
             */

            $login              = trim( $this->post('user_login') ) ;
            $password           = trim( $this->post('user_password') ) ;
            $newPassword        = trim( $this->post('user_new_password') ) ;
            $newPasswordConfirm = trim( $this->post('user_new_password_confirm') ) ;

            $user               = $this->getById();

            if ( empty( $login ) )
            {
                return $this->returnError( "user_update_login_empty" ) ;
            }
            else if ( ! filter_var( $login, FILTER_VALIDATE_EMAIL ) )
            {
                return $this->returnError( "user_update_login_not_valid" ) ;
            }
            else if ( ! $this->uniqLogin( $login ) )
            {
                return $this->returnError( "user_update_login_not_uniq" ) ;
            }
            else if ( ! empty( $password ) or ! empty( $newPassword ) or ! empty( $newPasswordConfirm ) )
            {
                if ( empty( $password ) )
                {
                    return $this->returnError( "user_update_password_empty" ) ;
                }
                else if ( empty( $newPassword ) )
                {
                    return $this->returnError( "user_update_new_password_empty" ) ;
                }
                else if ( empty( $newPasswordConfirm ) )
                {
                    return $this->returnError( "user_update_new_password_confirm_empty" ) ;
                }
                else if ( $newPassword != $newPasswordConfirm )
                {
                    return $this->returnError( "user_update_new_password_different" ) ;
                }
                else if ( ! password_verify( $password , $user->user_front_password ) )
                {
                    return $this->returnError( "user_update_last_password_invalid" ) ;
                }
                else
                {
                    return $this->loadUpdate( $user  ) ;
                }
            }
            else
            {
                return $this->loadUpdate( $user ) ;
            }
        }
        else
        {
            return $this->returnError( "user_update_not_logged" ) ;
        }
    }

    public function loadUpdate( $user )
    {
        $user->user_front_login = $this->post('user_login') ;
        $user->user_front_token = $this->getNewToken() ;

        if ( $this->post('user_new_password') != '' )
        {
            $user->user_front_password = password_hash( $this->post('user_new_password') , PASSWORD_BCRYPT , ['cost' => 9] ) ;
        }
        $user->save();

        return $this->returnError( "user_update_successful" , true ) ;
    }

    ###################################################################################################################################
    ######################################                VALIDATION                 ##################################################
    ###################################################################################################################################

    public function validation()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @GET
             * token
             */
            if ( $this->get('token') != '' )
            {
                $user = \DB::for_table('user_front')
                    ->where_equal('user_front_token', $this->get('token') )
                    ->where_equal('user_front_active', 0 )
                    ->find_one();

                if ( $user )
                {
                    $user->user_front_token  = $this->getNewToken();
                    $user->user_front_active = 1;
                    $user->save();

                    return $this->returnError( "user_validation_successful" , true ) ;
                }
                else
                {
                    return $this->returnError( "user_validation_failed" , true ) ;
                }
            }
        }
    }

    ###################################################################################################################################
    ######################################               LOST PASSWORD               ##################################################
    ###################################################################################################################################

    public function lostPassword()
    {
        if ( ! $this->isLogged() )
        {
            /*
             * @POST
             * user_login
             */
            $user = \DB::for_table('user_front')
                ->where_equal('user_front_login', $this->post('user_login') )
                ->where_equal('user_front_active', 1 )
                ->find_one();

            if ( $user )
            {
                $pass = $this->generatePassword();
                $user->user_front_password = $this->hashPassword( $pass ) ;
                $user->user_front_token    = $this->getNewToken();
                $user->save();

                // On envoie un email avec le mot de pass

                unset( $pass );
            }
            else
            {
                return $this->returnError( "user_password_failed" , true ) ;
            }
        }
    }

    protected function generatePassword( $length = 10 )
    {
        $alpha          = "abcdefghijklmnopqrstuvwxyz";
        $alpha_upper    = strtoupper($alpha);
        $numeric        = "0123456789";
        $special        = ".-+=_,!@$#*%<>[]{}";

        $chars          = $alpha . $alpha_upper . $numeric;
        $len            = strlen( $chars );
        $pw             = '';

        for ( $i = 0; $i < $length; $i++ )
        {
            $pw .= substr( $chars, rand( 0 , $len - 1 ) , 1 ) ;
        }

        return str_shuffle( $pw );
    }

    protected function hashPassword( $pass )
    {
        return password_hash( $pass , PASSWORD_BCRYPT , ['cost' => 9] ) ;
    }

    ###################################################################################################################################
    ######################################                  OBSERVE                  ##################################################
    ###################################################################################################################################

    public function observe()
    {
        $this->testFunctions();

        if ( $this->isLogged() )
        {
            if ( $this->isBadIp() )
            {
                $this->logout() ;
            }
            else
            {
                $this->pushData() ;
            }
        }
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    protected function testFunctions()
    {
        if ( ! function_exists('mcrypt_create_iv') && ! function_exists('random_bytes') && ! function_exists('openssl_random_pseudo_bytes') )
        {
            $this->Factory()->Response()->error('PHP functions is not available for user management');
        }
    }

    protected function save( $user )
    {
        $this->pushData( $user->user_front_id ) ;

        $_SESSION[ $this->getSessionName() ] = [
            'id'        => $this->getId(),
            'login'     => $this->getLogin(),
            'group'     => $this->getGroup(),
            'isLogged'  => true,
            'ip' 		=> $this->getIp()
        ];
    }

    protected function getNewToken()
    {
        $length = 32 ;
        $uniq   = false ;

        while( $uniq == false )
        {
            if ( function_exists('mcrypt_create_iv') )
            {
                $token = bin2hex(mcrypt_create_iv( $length , MCRYPT_DEV_URANDOM ) );
            }
            else if ( function_exists('random_bytes') )
            {
                $token = bin2hex( random_bytes( $length ) );
            }
            else if ( function_exists('openssl_random_pseudo_bytes') )
            {
                $token = bin2hex( openssl_random_pseudo_bytes( $length ) );
            }

            $uniq = $this->uniqToken( $token ) ;
        }

        return $token ;
    }

    protected function uniqLogin( $login )
    {
        $ct = \DB::for_table('user_front')
            ->where_not_equal('user_front_id', $this->getId() )
            ->where_equal('user_front_login', $login )
            ->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }


    protected function uniqToken( $token )
    {
        $ct = \DB::for_table('user_front')
            ->where_equal('user_front_token', $token )
            ->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }

    protected function getById()
    {
        return \DB::for_table('user_front')
            ->where_equal('user_front_id', $this->getId() )
            ->find_one();
    }

    protected function pushData( $id = NULL )
    {
        $user = \DB::for_table('user_front')
            ->left_outer_join('user_front_profile', ['user_front.user_front_id', '=', 'user_front_profile.user_front_profile_user_front_id'] )
            ->left_outer_join('user_front_group', ['user_front.user_front_user_front_group_id', '=', 'user_front_group.user_front_group_id'] )
            ->where_equal( 'user_front_id' , ( $id !== NULL ? $id : $_SESSION[ $this->getSessionName() ]['id'] ) )
            ->find_one();

        $this->setId( $user->user_front_id );
        $this->setLogin( $user->user_front_login );
        $this->setGroup( $user->user_front_user_front_group_id );

        return $user ;
    }
}