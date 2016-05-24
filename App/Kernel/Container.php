<?php

namespace App\Kernel;

class Container
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $factory = NULL ;
    private $entity = [] ;
    private $_var = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function set( $key , $value )
    {
        $this->_var[ $key ] = $value;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function get( $key )
    {
        if ( array_key_exists( $key , $this->_var ) )   return $this->_var[ $key ] ;
        else                                            return NULL ;
    }

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Container;
        return self::$instance ;
    }

    /* ***************************************************** */
    /* ****************     BOOTSTRAP    ******************* */
    /* ***************************************************** */

    private function bootstrap()
    {
        if ( $this->get('em') === NULL )
        {
            $config = new \Doctrine\ORM\Configuration();

            $driverImpl = $config->newDefaultAnnotationDriver([
                ENTITIES_PATH,
                ENTITIES_PROJECT_PATH
            ]);
            $config->setMetadataDriverImpl( $driverImpl );

            $cache = DEBUG ? new Cache\ArrayCache : new Cache\ApcCache;
            $config->setMetadataCacheImpl( $cache );
            $config->setQueryCacheImpl( $cache );

            $config->setProxyDir( PROXIES_PATH );
            $config->setProxyNamespace('\App\Proxies');

            $connectionOptions = [
                'driver'    => 'pdo_sqlite',
                'host'      => DB_HOST,
                'user'      => DB_USER,
                'dbname'    => DB_DATABASE,
                'password'  => DB_PASSWORD,
            ];

            $this->set('em', EntityManager::create( $connectionOptions, $config ) );
        }
    }

    /* *************************************************** */
    /* ****************     FACTORY    ******************* */
    /* *************************************************** */

    public function factory()
    {
        if ( $this->factory === NULL ) $this->factory = new Factory;
        return $this->factory ;
    }

    /* *************************************************** */
    /* ****************     ENTITY     ******************* */
    /* *************************************************** */

    public function getEntity( $name )
    {
        if ( array_key_exists( $name , $this->entity ) )
        {
            return $this->entity[ $name ]['entity'] ;
        }
        else
        {
            return $this->setEntity( $name ) ;
        }
    }

    public function getRepository( $name )
    {
        if ( array_key_exists( $name , $this->entity ) )
        {
            return $this->entity[ $name ]['repository'] ;
        }
        else
        {
            $this->setEntity( $name ) ;
            return $this->entity[ $name ]['repository'] ;
        }
    }

    private function setEntity( $name )
    {
        if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $name ) . '.php' ))  $ControllerClass = "\Project\Module\Controller\Front\\" . ucfirst( $name );
        else																		    $ControllerClass = '\App\Kernel\Front\Controller' ;

        $Controller = new $ControllerClass;
        $Controller->setEntityName( $name );
        $result = $Controller->loadEntity();

        if ( $result )
        {
            $this->entity[ $name ]['entity'] = $Controller ;
            $this->entity[ $name ]['repository'] = $this->get('em')->getRepository("\Project\Module\Entity\Class\\" . ucfirst( $name ) );
            return $this->entity[ $name ]['entity'] ;
        }
        else
        {
            return $this->entity[ $name ]['entity'] = NULL ;
        }
    }
}





