<?php

namespace App\Kernel\Middleware;

class CsrfGuard extends \Slim\Middleware
{
    /**
     * CSRF token key name.
     *
     * @var string
     */
    protected $key;

    /**
     * Constructor.
     *
     * @param string    $key        The CSRF token key name.
     * @return void
     */
    public function __construct( $key = 'csrf_token' )
    {
        if ( ! is_string( $key ) || empty( $key ) || preg_match('/[^a-zA-Z0-9\-\_]/', $key ) )
        {
            \App\Kernel\Factory::getInstance()->Response()->error('Invalid CSRF token key "' . $key . '"');
        }
        $this->key = $key;
    }

    /**
     * Call middleware.
     *
     * @return void
     */
    public function call()
    {
        // Attach as hook.
        $this->app->hook( 'slim.before', [$this, 'check'] );

        // Call next middleware.
        $this->next->call();
    }

    /**
     * Check CSRF token is valid.
     * Note: Also checks POST data to see if a Moneris RVAR CSRF token exists.
     *
     * @return void
     */
    public function check()
    {
        // Check sessions are enabled.
        if ( session_id() === '' )
        {
            \App\Kernel\Factory::getInstance()->Response()->error('Sessions are required to use the CSRF Guard middleware.');
        }

        if ( ! isset( $_SESSION[ $this->key ] ) )
        {
            $_SESSION[ $this->key ] = sha1( serialize( $_SERVER ) . rand( 0, 99999999 ) ) ;
        }

        $token = $_SESSION[ $this->key ] ;

        // Validate the CSRF token.
        if ( in_array( $this->app->request()->getMethod() , ['POST', 'PUT', 'DELETE'] ) )
        {
            if ( ! empty( $this->app->request()->headers('Content-Type') ) )
            {
                if ( strpos( $this->app->request()->headers('Content-Type') , 'application/json' ) !== false )
                {
                    $json = file_get_contents('php://input');
                    $_POST = json_decode( $json , true );

                    $userToken = $_POST[ $this->key ];
                }
                else
                {
                    $userToken = $this->app->request()->post( $this->key ) ;
                }
            }
            else
            {
                $userToken = $this->app->request()->post( $this->key ) ;
            }

            if ( $token !== $userToken )
            {
                $this->app->halt(400, "Invalid or missing CSRF token. ---  $token !== $userToken");
            }
        }

        // Assign CSRF token key and value to view.
        $this->app->view()->appendData([
            'csrf_key'      => $this->key,
            'csrf_token'    => $token
        ]);
    }
}