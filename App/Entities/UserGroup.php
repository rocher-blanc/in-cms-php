<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserGroup
 */
class UserGroup
{
    /**
     * @var integer
     */
    private $userGroupId;

    /**
     * @var string
     */
    private $userGroupName;

    /**
     * @var string
     */
    private $userGroupUrl;

    /**
     * @var string
     */
    private $userGroupRedirect;


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
     * Set userGroupName
     *
     * @param string $userGroupName
     * @return UserGroup
     */
    public function setUserGroupName($userGroupName)
    {
        $this->userGroupName = $userGroupName;

        return $this;
    }

    /**
     * Get userGroupName
     *
     * @return string 
     */
    public function getUserGroupName()
    {
        return $this->userGroupName;
    }

    /**
     * Set userGroupUrl
     *
     * @param string $userGroupUrl
     * @return UserGroup
     */
    public function setUserGroupUrl($userGroupUrl)
    {
        $this->userGroupUrl = $userGroupUrl;

        return $this;
    }

    /**
     * Get userGroupUrl
     *
     * @return string 
     */
    public function getUserGroupUrl()
    {
        return $this->userGroupUrl;
    }

    /**
     * Set userGroupRedirect
     *
     * @param string $userGroupRedirect
     * @return UserGroup
     */
    public function setUserGroupRedirect($userGroupRedirect)
    {
        $this->userGroupRedirect = $userGroupRedirect;

        return $this;
    }

    /**
     * Get userGroupRedirect
     *
     * @return string 
     */
    public function getUserGroupRedirect()
    {
        return $this->userGroupRedirect;
    }
}
