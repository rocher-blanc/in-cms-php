<?php

namespace App\Kernel\Middleware;

class SessionCrypt extends \Slim\Middleware\SessionCookie
{
    protected function getKey()
    {
       return substr( md5( base64_encode( $this->app->request()->getUrl() ) ) , 0 , 24 ) ;
    }

    /**
     * Load session
     */
    protected function loadSession()
    {
        if ( session_id() === '' ) session_start() ;

        $value = mcrypt_decrypt(MCRYPT_3DES, $this->getKey(), $this->app->getCookie($this->settings['name']), MCRYPT_MODE_ECB);

        if ( $value ) {
            try {
                $_SESSION = unserialize( base64_decode( $value ) ) ;
            } catch (\Exception $e) {
                $this->app->getLog()->error('Error unserializing session cookie value! ' . $e->getMessage());
            }
        } else {
            $_SESSION = [];
        }
    }

    /**
     * Save session
     */
    protected function saveSession()
    {
        $value = mcrypt_encrypt(MCRYPT_3DES, $this->getKey(), base64_encode(serialize($_SESSION)), MCRYPT_MODE_ECB);

        if (strlen($value) > 4096) {
            $this->app->getLog()->error('WARNING! Slim\Middleware\SessionCookie data size is larger than 4KB. Content save failed.');
        } else {
            $this->app->setCookie(
                $this->settings['name'],
                $value,
                ( time() + SESSION_LIFETIME ),
                $this->settings['path'],
                $this->settings['domain'],
                $this->settings['secure'],
                $this->settings['httponly']
            );
        }
    }
}