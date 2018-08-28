<?php

namespace App\Kernel\Lib;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class Rabbit
{
    private $conn = NULL ;
    private $channel = NULL ;

    private static $instance = NULL ;

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Rabbit;
        return self::$instance ;
    }

    function __construct()
    {
        $this->conn = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->conn->channel();

        $tab = unserialize(RABBIT_QUEUE ) ;
        if ( $tab )
        {
            foreach( $tab as $row )
            {
                $this->channel->queue_declare($row, false, true, false, false);
            }
        }
    }

    public function disconnect()
    {
        $this->channel->close();
        $this->conn->close();
    }

    public function Channel()
    {
        return $this->channel ;
    }
}