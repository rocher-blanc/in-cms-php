<?php

namespace App\Kernel\Front;

class User extends \App\Kernel\Common\User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    protected $id    = NULL;
    protected $tmpId = NULL;
    protected $login = NULL;
    protected $group = NULL;
    protected $facebook_url = NULL;
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

    protected function setTmpId( $var )
    {
        $this->tmpId = $var ;
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
        return 'jcontent_user' ;
    }

    public function getId()
    {
        return $this->id ;
    }

    protected function getTmpId()
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

    protected function getGroup()
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
            if ( file_exists( CLASS_PROJECT_PATH . '/User.php' ) )  self::$instance = new \Project\CustomClass\Front\User;
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
                // echo 'Graph returned an error: ' . $e->getMessage();
                // exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                // echo 'Facebook 1 SDK returned an error: ' . $e->getMessage();
                // exit;
            }

            if ( isset( $accessToken ) )
            {
                $fb->setDefaultAccessToken( $accessToken );
                try {
                    $response = $fb->get('/me?fields=email,name,first_name,last_name');
                    $userNode = $response->getGraphUser();
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    // echo 'Graph returned an error: ' . $e->getMessage();
                    // exit;
                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    // echo 'Facebook 3 SDK returned an error: ' . $e->getMessage();
                    // exit;
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
                $url_redirect = \App\Kernel\Http::getInstance()->getUrl() . '/' . $this->Factory()->Url()->page( FB_APP_PAGE ) ;
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
    ######################################                REGISTER                   ##################################################
    ###################################################################################################################################

    /**
     * @return bool
     */
    public function register()
    {
        /*
         * @POST
         *
         */

        if ( $this->checkRegister() )
        {
            $login    = trim( $this->post('user_login') ) ;
            $password = trim( $this->post('user_password') ) ;

            return $this->registerInBase( $login , $password ) ;
        }
    }

    protected function registerInBase( $login , $password , $fb_id = NULL )
    {
        $date = new \DateTime();

        $active = ( USER_ACTIVATION_MAIL ? 0 : $this->getActiveRegister() ) ;
        if ( $fb_id !== NULL ) $active = 1;

        $user = \DB::for_table('user_front')->create();
        $user->user_front_token                 = $this->getNewToken();
        $user->user_front_login                 = $login;
        $user->user_front_password              = $this->hashPassword( $password );
        $user->user_front_date_created          = $date->format('Y-m-d H:i:s');
        $user->user_front_active                = $active;
        $user->user_front_user_front_group_id   = $this->getDefaultGroup() ;
        if ( $fb_id !== NULL ) $user->user_front_user_fb_id = $fb_id ;

        $user->save();

        $this->setTmpId( $user->user_front_id ) ;
        $this->addProfile();

        if ( USER_ACTIVATION_MAIL )
        {
            $mail = new Mail;
            $mail->add( $user->user_front_login )
                ->setSubject( $this->text('user_mail_subjet_validation') )
                ->parse('validation', [
                    'token' => $user->user_front_token,
                    'login' => $user->user_front_login,
                ]);

            if ( ! $mail->send() )
            {
                return $this->returnError( "user_register_send_mail_error" ) ;
            }
            else
            {
                return $this->returnError( "user_register_send_mail_successful" , true ) ;
            }
        }
        else
        {
            return $this->returnError( "user_register_successful" , true ) ;
        }
    }

    protected function getActiveRegister()
    {
        return 1;
    }

    protected function getDefaultGroup()
    {
        return 0;
    }

    protected function addProfile()
    {
        $profile = \DB::for_table('user_front_profile')->create();
        $profile->user_front_profile_user_front_id = $this->getTmpId();
        $profile = $this->addProfileOtherInformation( $profile ) ;
        $profile->save();
    }

    protected function addProfileOtherInformation( $profile )
    {
        return $profile ;
    }

    protected function checkRegister()
    {
        /*
         * @POST
         *
         */
        $login              = trim( $this->post('user_login') ) ;
        $password           = trim( $this->post('user_password') ) ;
        $passwordConfirm    = trim( $this->post('user_password_confirm') ) ;

        if ( $this->isLogged() )
        {
            return $this->returnError( "user_register_logged" ) ;
        }
        else if ( empty( $login ) )
        {
            return $this->returnError( "user_register_login_empty" ) ;
        }
        else if ( ! filter_var( $login, FILTER_VALIDATE_EMAIL ) )
        {
            return $this->returnError( "user_register_login_not_valid" ) ;
        }
        else if ( ! $this->uniqLogin( $login ) )
        {
            return $this->returnError( "user_register_login_not_uniq" ) ;
        }
        else if ( empty( $password ) )
        {
            return $this->returnError( "user_register_password_empty" ) ;
        }
        else if ( empty( $passwordConfirm ) )
        {
            return $this->returnError( "user_register_confirm_password_empty" ) ;
        }
        else if ( $password != $passwordConfirm )
        {
            return $this->returnError( "user_register_password_different" ) ;
        }
        else
        {
            return true ;
        }
    }

    ###################################################################################################################################
    ######################################                  UPDATE                   ##################################################
    ###################################################################################################################################

    protected function checkUpdate()
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
                    return true ;
                }
            }
            else
            {
                return true ;
            }
        }
        else
        {
            return $this->returnError( "user_update_not_logged" ) ;
        }
    }

    public function update()
    {
        if ( $this->checkUpdate() )
        {
            $user = $this->getById();

            $user->user_front_login = $this->post('user_login') ;
            $user->user_front_token = $this->getNewToken() ;

            if ( $this->post('user_new_password') != '' )
            {
                $user->user_front_password = password_hash( $this->post('user_new_password') , PASSWORD_BCRYPT , ['cost' => 9] ) ;
            }
            $user->save();

            $this->updateProfile() ;

            return $this->returnError( "user_update_successful" , true ) ;
        }
    }

    protected function updateProfile()
    {
        $profile = \DB::for_table('user_front_profile')->where_equal('user_front_profile_user_front_id', $this->getId())->find_one();
        $profile = $this->updateProfileOtherInformation( $profile ) ;
        $profile->save();
    }

    protected function updateProfileOtherInformation( $profile )
    {
        return $profile ;
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