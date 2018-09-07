<?php

namespace App\Kernel\Back;

class User extends \App\Kernel\Common\User
{
    protected $id           = NULL ;
    protected $email        = '' ;
    protected $active       = 0 ;
    protected $entityName   = '' ;

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

    public function isFormated()
    {
        return filter_var( $this->getEmail(), FILTER_VALIDATE_EMAIL );
    }

    public function add()
    {
        $rst = \DB::for_table('user_front')->create();

        if ( $rst )
        {
            $date = new \DateTime();

            $rst->user_front_login = $this->getEmail() ;
            $rst->user_front_token = $this->getNewToken() ;
            $rst->user_front_active = $this->getEmail() ;
            $rst->user_front_user_front_group_id = $this->getEmail() ;
            $rst->user_front_date_created = $date->format('Y-m-d H:i:s');
            $rst->save();
        }
    }

    public function update()
    {
        $rst = \DB::for_table('user_front')
            ->where_not_equal('user_front_id', $this->getId() )
            ->find_one();

        if ( $rst )
        {
            $rst->user_front_login = $this->getEmail() ;
            $rst->save();
        }
    }

    public function delete()
    {
        \DB::for_table('user_front')->where_id_is( $this->getId() )->delete();
    }
}