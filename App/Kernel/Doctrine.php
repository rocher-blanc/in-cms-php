<?php

namespace App\Kernel;

use Doctrine\Common\Cache;
use Doctrine\ORM\EntityManager;

class Doctrine
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $_em = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->load();
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Doctrine;
        return self::$instance ;
    }

    public function getEntityManager()
    {
        return $this->_em;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    private function load()
    {
        $config = new \Doctrine\ORM\Configuration();

        $driverImpl = $config->newDefaultAnnotationDriver([ ENTITIES_PATH ]);
        $config->setMetadataDriverImpl( $driverImpl );

        $cache = DEBUG ? new Cache\ArrayCache : new Cache\ApcCache;
        $config->setMetadataCacheImpl( $cache );
        $config->setQueryCacheImpl( $cache );

        $config->setProxyDir( PROXIES_PATH );
        $config->setProxyNamespace('App\Proxies');

        $connectionOptions = array(
            'driver'    => 'pdo_mysql',
            'user'      => DB_USER,
            'dbname'    => DB_DATABASE,
            'host'      => DB_HOST,
            'password'  => DB_PASSWORD
        );

        $this->_em = EntityManager::create($connectionOptions, $config);
    }
}