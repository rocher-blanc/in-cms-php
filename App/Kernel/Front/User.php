<?php

namespace App\Kernel\Front;

use App\Api\Easyletter;

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
    protected $silence      = false;
    protected $_var         = [];
    protected $_twig        = [];
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

    protected function setTwig( array $array )
    {
        $this->_twig = $array ;
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    protected function getSessionName()
    {
        return 'easydoor_user' ;
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

    public function getTwig()
    {
        return $this->_twig ;
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

    public function silence()
    {
        return $this->silence = true ;
    }

    public function hasSilence()
    {
        return $this->silence ;
    }

    public function hasError()
    {
        return $this->_error ;
    }

    public function getError()
    {
        return $this->_error_msg ;
    }

    protected function returnError( $key , $result = false , $forceView = false )
    {
        if ( $result == false ) $this->_error = true ;

        $this->_error_msg = [
            'action' => $this->post('user_action'),
            'result' => $result,
            'msg' => $this->text( $key )
        ];

        if ( $this->hasSilence() ) return $result ;

        if ( $this->isAjax() )
        {
            if ( $this->getProfileModule() === NULL || $forceView == true || $this->post('ajax') == 1 )
            {
                $tab = [];
                if ( $this->post('redirect' ) != '' )
                {
                    $tab = [
                        'url' => $this->post('redirect' )
                    ];
                }

                $this->Factory()->Response()->returnJSON( $this->text( $key ) , $result , $tab );
            }
        }
        else
        {
            $this->CMS()->view()->appendData([
                'user_error' => $this->_error_msg
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

    protected function getProfileData()
    {
        if ( $this->getProfileModule() !== NULL && $this->isLogged() )
        {
            $data = new Data( $this->getProfileModule() );
            $rst = $data->find([
                'user_front_id' => $this->getId()
            ]);

            if ( $rst )
            {
                $rst = $data->getDataArray() ;
                $array = $rst ;
                unset( $array['id'] );

                $this->_var = array_merge( $this->_var , $array );
                return $rst;
            }
        }
        else
        {
            return [];
        }
    }

    public function appendVar()
    {
        $this->setTwig(array_merge([
            'id'                => $this->getId(),
            'login'             => $this->getLogin(),
            'group'             => $this->getGroup(),
            'fb_url'            => $this->getFacebookUrl(),
            'isLogged'          => $this->isLogged(),
            'profile'           => $this->getProfileData(),
            'first_connection'  => $_SESSION['first_connection'],
        ], $this->getVar() ));

        $this->CMS()->view()->appendData([
            'user' => $this->getTwig()
        ]);

        if ( $_SESSION['first_connection'] == true && $_SESSION['first_connection_counter'] > 0 )
        {
            $_SESSION['first_connection_counter'] = $_SESSION['first_connection_counter'] - 1;
        }
        else
        {
            $_SESSION['first_connection'] = false ;
        }
    }

    /* ************************************************** */
    /* ****************    ACTIONS    ******************* */
    /* ************************************************** */

    ###################################################################################################################################
    ######################################               MODULE USER                 ##################################################
    ###################################################################################################################################

    protected function getProfileModule()
    {
        if ( defined('MODULE_USER') )   return MODULE_USER ;
        else								  return NULL ;
    }

    protected function checkModule()
    {
        if ( $this->getProfileModule() !== NULL )
        {
            $Module = $this->Container()->module( $this->getProfileModule() )->getController();
            if ( ! $Module->checkForm() )
            {
                $Entity = $this->Container()->module( $this->getProfileModule() )->getEntity();
                foreach( $Entity->getField() as $field )
                {
                    if ( $field->getError() !== NULL ) return $this->returnError( $field->getFrontError() , false ) ;
                }
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

                $user = $this->getAccount( $login ) ;

                if ( is_object( $user ) && $this->checkPassword( $user->user_front_password , $password ) == true )
                {
                    $date = new \DateTime();
                    $user->user_front_last_connection = $date->format('Y-m-d H:i:s');
                    $user->save();

                    $saveSession = $this->save( $user ) ;

                    $this->returnError( "user_login_successful" , true , true ) ;

                    return $saveSession;
                }
                else
                {
                    return $this->returnError( "user_login_failed" , false , true ) ;
                }
            }
            else
            {
                return $this->returnError( "user_login_field_empty" , false , true ) ;
            }
        }
        else
        {
            $this->returnError( "user_login_successful" , true , true ) ;
        }
    }

    protected function getAccount( $login )
    {
        return \DB::for_table('user_front')
            ->where_equal('user_front_login', $login)
            ->where_equal('user_front_active', 1)
            ->find_one();
    }

    ###################################################################################################################################
    ######################################                  LOGOUT                   ##################################################
    ###################################################################################################################################

    public function logout()
    {
        if ( $this->isLogged() )
        {
            $_SESSION[ $this->getSessionName() ] = [];
            session_destroy();

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

        if ( USER_ACTIVATION_MAIL )
        {
            $rst = $this->sendValidationMail( $user ) ;

            if ( ! $rst )
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
            $rst = $this->sendWelcomeMail( $user ) ;

            if ( ACTIVE_USER_CONNECT_AFTER_REGISTER )
            {
                $user->user_front_last_connection = $date->format('Y-m-d H:i:s');
                $user->save();

                $_SESSION['first_connection'] = true ;
                $_SESSION['first_connection_counter'] = 2 ;

                $this->save( $user ) ;
            }

            return $this->returnError( "user_register_successful" , true ) ;
        }
    }

    protected function sendWelcomeMail( $user )
    {
        $el = new Easyletter;
        $el->automotion("user_account_welcome" , $user->user_front_login , array_merge([
            'email' => $user->user_front_login
        ], $this->getEmailVariableWelcome() ));

        return true ;
    }

    protected function sendValidationMail( $user )
    {
        $el = new Easyletter;
        $el->automotion("user_account_validation" , $user->user_front_login , array_merge([
            'url_validation' => \App\Kernel\Http::getInstance()->getUrl() . "?user_validation=me&token=" . $user->user_front_token,
            'email' => $user->user_front_login
        ], $this->getEmailVariableValidation() ));

        return true ;
    }

    protected function getEmailVariableWelcome()
    {
        return [];
    }

    protected function getEmailVariableValidation()
    {
        return [];
    }

    protected function getActiveRegister()
    {
        return 1;
    }

    public function getDefaultGroup()
    {
        return 1;
    }

    public function checkRegister()
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
        else if ( $this->formatPasswordRequired( $password ) == false )
        {
            return $this->returnError( "user_register_password_invalid_format" ) ;
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
            return $this->checkModule() ;
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

            $login = trim( $this->post('user_login') ) ;
            $user  = $this->getById();

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
            else
            {
                return $this->checkModule();
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
            $user->save();

            if ( $this->getProfileModule() !== NULL ) $this->updateProfile() ;
            $this->pushData();

            return $this->returnError( "user_update_successful" , true ) ;
        }
    }

    protected function updateProfile()
    {
        /*
        $profile = \DB::for_table('user_front_profile')->where_equal('user_front_profile_user_front_id', $this->getId())->find_one();
        $profile = $this->updateProfileOtherInformation( $profile ) ;
        $profile->save();
        */
    }

    ###################################################################################################################################
    ######################################                 PASSWORD                  ##################################################
    ###################################################################################################################################

    public function updatePassword()
    {
        $password           = trim( $this->post('user_password') ) ;
        $newPassword        = trim( $this->post('user_new_password') ) ;
        $newPasswordConfirm = trim( $this->post('user_new_password_confirm') ) ;

        if ( ! $this->isLogged() )
        {
            return $this->returnError( "user_update_not_logged" ) ;
        }
        else
        {
            $user = $this->getById();

            if ( empty( $password ) )
            {
                return $this->returnError( "user_update_password_empty" , false , true ) ;
            }
            else if ( empty( $newPassword ) )
            {
                return $this->returnError( "user_update_new_password_empty" , false , true ) ;
            }
            else if ( $this->formatPasswordRequired( $newPassword ) == false )
            {
                return $this->returnError( "user_update_new_password_invalid_format" , false , true ) ;
            }
            else if ( empty( $newPasswordConfirm ) )
            {
                return $this->returnError( "user_update_new_password_confirm_empty" , false , true ) ;
            }
            else if ( $newPassword != $newPasswordConfirm )
            {
                return $this->returnError( "user_update_new_password_different" , false , true ) ;
            }
            else if ( ! $this->checkPassword( $user->user_front_password , $password ) )
            {
                return $this->returnError( "user_update_last_password_invalid" , false , true ) ;
            }
            else
            {
                $user->user_front_token     = $this->getNewToken() ;
                $user->user_front_password  = $this->hashPassword( $newPassword ) ;
                $user->save();

                return $this->returnError( "user_update_password_successful" , true , true ) ;
            }
        }
    }

    protected function formatPasswordRequired( $pass )
    {
        return true ;
    }

    protected function checkPassword( $pass , $post )
    {
        return password_verify( $post , $pass ) ;
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

                    $el = new Easyletter;
                    $rstMail = $el->automotion("lost_password" , $login , array_merge([
                        'password' => $pass,
                        'Email' => $login
                    ], $this->getEmailVariablePassword() ));

                    if ( $rstMail === false )
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

    protected function getEmailVariablePassword()
    {
        return [];
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

    public function hashPassword( $pass )
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