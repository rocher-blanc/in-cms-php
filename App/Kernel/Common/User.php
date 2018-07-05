<?php

namespace App\Kernel\Common;

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

    public function isLogged()
    {
        return ( isset( $_SESSION[ $this->getSessionName() ] ) && !empty( $_SESSION[ $this->getSessionName() ] ) );
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

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
        return $_SERVER['REMOTE_ADDR'] ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function getNewToken()
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

    protected function uniqToken( $token )
    {
        $ct = \DB::for_table('user_front')
            ->where_equal('user_front_token', $token )
            ->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
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
            if ( ACTIVE_USER_CONNECT_AFTER_REGISTER )
            {
                $user->user_front_last_connection = $date->format('Y-m-d H:i:s');
                $user->save();

                $this->save( $user ) ;
            }

            return $this->returnError( "user_register_successful" , true ) ;
        }
    }

    protected function sendValidationMail( $user )
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
            return false ;
        }
        else
        {
            return true ;
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
            if ( $this->getProfileModule() !== NULL )
            {
                $Module = $this->Container()->module( $this->getProfileModule() )->getController();
                if ( ! $Module->checkForm() )
                {
                    $Entity = $this->Container()->module( $this->getProfileModule() )->getEntity();
                    foreach( $Entity->getField() as $field )
                    {
                        if ( $field->getError() !== NULL ) return $this->returnError( $field->getFrontError() ) ;
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
                    if ( $this->getProfileModule() !== NULL )
                    {
                        $Module = $this->Container()->module( $this->getProfileModule() )->getController();
                        if ( ! $Module->checkForm() )
                        {
                            return $this->returnError( "xxxxxxxxxxxxxxxxxxxxx" ) ;
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
            }
            else
            {
                if ( $this->getProfileModule() !== NULL )
                {
                    $Module = $this->Container()->module( $this->getProfileModule() )->getController();
                    if ( ! $Module->checkForm() )
                    {
                        return $this->returnError( "xxxxxxxxxxxxxxxxxxxxx" ) ;
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
}