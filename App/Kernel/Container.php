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
        $this->_var[ $key ] = $value ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new CMS;
        return self::$instance ;
    }

    public function module( $name )
    {
        if ( array_key_exists( $name , $this->entity ) )
        {
            return $this->entity[ $name ] ;
        }
        else
        {
            return $this->entity[ $name ] = new \App\Kernel\Entity\Container( $name );
        }
    }

    public function get( $key )
    {
        if ( array_key_exists( $name , $this->_var ) )
        {
            return $this->_var[ $name ] ;
        }
        else
        {
            return NULL;
        }
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
}





