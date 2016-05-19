<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User
{
    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $userGroupId;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $userPassword;

    /**
     * @var string
     */
    private $userFname;

    /**
     * @var string
     */
    private $userLname;

    /**
     * @var integer
     */
    private $userType;

    /**
     * @var boolean
     */
    private $userPublished;


    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userGroupId
     *
     * @param integer $userGroupId
     * @return User
     */
    public function setUserGroupId($userGroupId)
    {
        $this->userGroupId = $userGroupId;

        return $this;
    }

    /**
     * Get userGroupId
     *
     * @return integer 
     */
    public function getUserGroupId()
    {
        return $this->userGroupId;
    }

    /**
     * Set userName
     *
     * @param string $userName
     * @return User
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userPassword
     *
     * @param string $userPassword
     * @return User
     */
    public function setUserPassword($userPassword)
    {
        $this->userPassword = $userPassword;

        return $this;
    }

    /**
     * Get userPassword
     *
     * @return string 
     */
    public function getUserPassword()
    {
        return $this->userPassword;
    }

    /**
     * Set userFname
     *
     * @param string $userFname
     * @return User
     */
    public function setUserFname($userFname)
    {
        $this->userFname = $userFname;

        return $this;
    }

    /**
     * Get userFname
     *
     * @return string 
     */
    public function getUserFname()
    {
        return $this->userFname;
    }

    /**
     * Set userLname
     *
     * @param string $userLname
     * @return User
     */
    public function setUserLname($userLname)
    {
        $this->userLname = $userLname;

        return $this;
    }

    /**
     * Get userLname
     *
     * @return string 
     */
    public function getUserLname()
    {
        return $this->userLname;
    }

    /**
     * Set userType
     *
     * @param integer $userType
     * @return User
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * Get userType
     *
     * @return integer 
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Set userPublished
     *
     * @param boolean $userPublished
     * @return User
     */
    public function setUserPublished($userPublished)
    {
        $this->userPublished = $userPublished;

        return $this;
    }

    /**
     * Get userPublished
     *
     * @return boolean 
     */
    public function getUserPublished()
    {
        return $this->userPublished;
    }
}
