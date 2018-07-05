<?php

namespace App\Kernel\Front;

class User extends \App\Kernel\Common\User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    protected $id           = NULL;
    protected $tmpId        = NULL;
    protected $login        = NULL;
    protected $date_join    = NULL;
    protected $group        = NULL;
    protected $facebook_url = NULL;
    protected $_var         = [];
    protected $_error       = false;

    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */

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

    protected function setTmpId( $var )
    {
        $this->tmpId = $var ;
    }

    protected function setLogin( $var )
    {
        $this->setVar( 'login' , $var );
        $this->login = $var ;
    }

    protected function setDateJoin( $var )
    {
        $this->setVar( 'date_join' , $var );
        $this->date_join = $var ;
    }

    protected function setGroup( $var )
    {
        $this->setVar( 'group' , $var );
        $this->group = $var ;
    }

    protected function setFacebookUrl( $var )
    {
        $this->facebook_url = $var ;
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
        return 'easydoor_user' ;
    }

    protected function getProfileModule()
    {
        if ( defined('MODULE_USER') )  return MODULE_USER ;
        else								 return NULL ;
    }

    public function getId()
    {
        return $this->id ;
    }

    public function getTmpId()
    {
        return $this->tmpId ;
    }

    protected function getVar()
    {
        return $this->_var ;
    }

    public function getProfile( $key )
    {
        return $this->_var[ $key ] ;
    }

    public function getLogin()
    {
        return $this->login ;
    }

    public function getDateJoin()
    {
        return $this->date_join ;
    }

    public function getGroup()
    {
        return $this->group ;
    }

    protected function getFacebookUrl()
    {
        $this->parseFacebookState();
        return $this->facebook_url ;
    }

    public static function getInstance()
    {
        if ( self::$instance === NULL )
        {
            if ( file_exists( CLASS_PROJECT_PATH . '/User.php' ) )     self::$instance = new \Project\CustomClass\Front\User;
            else                                                                self::$instance = new User;
        }
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    public function hasError()
	{
		return $this->_error ;
	}

    protected function returnError( $key , $result = false )
    {
        if ( $result == false ) $this->_error = true ;

        if ( $this->isAjax() )
        {
            $this->Factory()->Response()->returnJSON( $this->text( $key ) , $result );
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
                'fb_url'    => $this->getFacebookUrl(),
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
                    ->where_equal('user_front_active', 1)
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
    ######################################          CONNECT WITH FACEBOOK            ##################################################
    ###################################################################################################################################

    /**
     * @return bool
     */
    public function connectWithFacebook()
    {
        if ( FB_APP_ID === NULL && FB_APP_SECRET === NULL && FB_APP_PAGE === NULL ) return false ;

        if ( ! $this->isLogged() )
        {
            $fb = new \Facebook\Facebook([
                'app_id' => FB_APP_ID,
                'app_secret' => FB_APP_SECRET,
                'default_graph_version' => 'v2.4'
            ]);

            $helper = $fb->getRedirectLoginHelper();
            // Callback
            try {
                $accessToken = $helper->getAccessToken();
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                if ( DEBUG )
                {
                    echo 'Graph returned an error: ' . $e->getMessage();
                    exit;
                }
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                if ( DEBUG )
                {
                    echo 'Facebook 1 SDK returned an error: ' . $e->getMessage();
                    exit;
                }
            }

            if ( isset( $accessToken ) )
            {
                $fb->setDefaultAccessToken( $accessToken );
                try {
                    $response = $fb->get('/me?fields=email,name,first_name,last_name');
                    $userNode = $response->getGraphUser();
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    if ( DEBUG )
                    {
                        echo 'Graph returned an error: ' . $e->getMessage();
                        exit;
                    }
                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    if ( DEBUG )
                    {
                        echo 'Facebook 1 SDK returned an error: ' . $e->getMessage();
                        exit;
                    }
                }

                if ( is_object( $userNode ) )
                {
                    $user = \DB::for_table('user_front')
                        ->where_equal('user_front_login' , $userNode->getId() )
                        ->find_one();

                    if ( ! $user )
                    {
                        $user = \DB::for_table('user_front')
                            ->where_equal('user_front_fb_id' , $userNode->getEmail() )
                            ->find_one();

                        if ( ! $user )
                        {
                            // on creer l'utilisateur
                            $pass = $this->generatePassword();
                            $this->registerInBase( $userNode->getEmail() , $pass , $userNode->getId() ) ;

                            $user = \DB::for_table('user_front')
                                ->where_equal('user_front_login', $userNode->getEmail())
                                ->where_equal('user_front_active', 1)
                                ->find_one();

                            $this->returnError( "user_login_successful" , true ) ;
                            return $this->save( $user );
                        }
                        else
                        {

                        }
                    }
                    else
                    {
                        // on check si son FB ID est présent

                    }
                }
                else
                {
                    return $this->returnError( "user_connect_facebook_error" ) ;
                }
            }
            else
            {
                $url_redirect = $this->Factory()->Url()->page( FB_APP_PAGE , true ) ;
                $loginUrl = $helper->getLoginUrl( $url_redirect , $this->getScopeFacebook() );
                $this->setFacebookUrl( $loginUrl );
            }
        }
    }

    protected function getScopeFacebook()
    {
        return ['email'];
    }

    protected function parseFacebookState()
    {
        if ( $_SESSION )
        {
            foreach( $_SESSION as $k => $v )
            {
                if ( strpos( $k , "FBRLH_" ) !== false )
                {
                    $this->CMS()->getApp()->setCookie(
                        "fb_state",
                        $_SESSION[$k],
                        ( time() + COOKIE_EXPIRES ),
                        $this->settings['path'],
                        $this->settings['domain'],
                        $this->settings['secure'],
                        $this->settings['httponly']
                    );
                }
            }
        }
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
                    if ( $this->connectWithValidation() )
                    {
                        $date = new \DateTime();
                        $user->user_front_last_connection = $date->format('Y-m-d H:i:s');
                    }

                    $user->user_front_token  = $this->getNewToken();
                    $user->user_front_active = 1;
                    $user->save();

                    if ( $this->connectWithValidation() )
                    {
                        $this->save( $user ) ;
                    }

                    return $this->returnError( "user_validation_successful" , true ) ;
                }
                else
                {
                    return $this->returnError( "user_validation_failed" , true ) ;
                }
            }
        }
    }

    protected function connectWithValidation()
    {
        return false ;
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

            $login = trim( $this->post('user_login') ) ;

            if ( empty( $login ) )
            {
                return $this->returnError( "user_lost_password_login_empty" ) ;
            }
            else
            {
                $user = \DB::for_table('user_front')
                    ->where_equal('user_front_login', $login )
                    ->where_equal('user_front_active', 1 )
                    ->find_one();

                if ( $user )
                {
                    $pass = $this->generatePassword();
                    $user->user_front_password = $this->hashPassword( $pass ) ;
                    $user->user_front_token    = $this->getNewToken();
                    $user->save();

                    $mail = new Mail;
                    $mail->add( $login )
                        ->setSubject( $this->text('user_mail_subjet_lost_password') )
                        ->parse('lost-password', [
                            'password' => $pass,
                            'login' => $login,
                        ]);

                    if ( ! $mail->send() )
                    {
                        return $this->returnError( "user_lost_password_send_mail_error" ) ;
                    }
                    else
                    {
                        return $this->returnError( "user_lost_password_send_mail_successful" , true ) ;
                    }
                }
                else
                {
                    return $this->returnError( "user_lost_password_failed" ) ;
                }
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

    protected function passwordIsSecured( $pass )
    {
        if ( strlen( $pass ) < 8 )
        {
            return false ;
        }

        return true ;
    }

    protected function uniqLogin( $login )
    {
        $ct = \DB::for_table('user_front');

        if ( $this->getId() !== NULL )
        {
            $ct = $ct->where_not_equal('user_front_id', $this->getId() );
        }

        $ct = $ct->where_equal('user_front_login', $login )->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }

    protected function getById()
    {
        return \DB::for_table('user_front')
            ->where_equal('user_front_id', $this->getId() )
            ->find_one();
    }

    public function getOne( $id )
    {
        return \DB::for_table('user_front')
            ->select('user_front_id')
            ->select('user_front_login')
            ->where_equal('user_front_id', $id )
            ->find_one();
    }

    protected function pushData( $id = NULL )
    {
        $user = \DB::for_table('user_front')
            ->left_outer_join('user_front_group', ['user_front.user_front_user_front_group_id', '=', 'user_front_group.user_front_group_id'] )
            ->where_equal( 'user_front_id' , ( $id !== NULL ? $id : $_SESSION[ $this->getSessionName() ]['id'] ) )
            ->find_one();

        if ( $user )
        {
            $this->setId( $user->user_front_id );
            $this->setLogin( $user->user_front_login );
            $this->setDateJoin( $user->user_front_date_created );
            $this->setGroup( $user->user_front_user_front_group_id );

            return $user ;
        }
        else
        {
            $this->logout() ;

            return NULL ;
        }
    }
}