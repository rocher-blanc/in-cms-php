<?php

namespace App\Kernel\Back;

class User extends \App\Kernel\Common\User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;
    protected $id           = NULL ;
    protected $email        = '' ;
    protected $active       = 0 ;
    protected $entityName   = '' ;

    /* ************************************************** */
    /* **************    SINGLESTON    ****************** */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL )
        {
            self::$instance = new User;
        }
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    /**
     * @return string
     */
    public function getEntity()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getEntityName() )->getEntity() ;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function isUnique()
    {
        $ct = \DB::for_table('user_front');

        if ( $this->getId() !== NULL )
        {
            $ct = $ct->where_not_equal('user_front_id', $this->getId() );
        }

        $ct = $ct->where_equal('user_front_login', $this->getEmail() )->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }

    public function getGroups()
    {
        $tab = [];
        $rst = \DB::for_table('user_front_group')
            ->order_by_asc('user_front_group_name')
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->user_front_group_id ] = $row->user_front_group_name ;
            }
        }

        return $tab ;
    }
}