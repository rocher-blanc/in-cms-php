<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 */
class Permission
{
    /**
     * @var integer
     */
    private $permissionId;

    /**
     * @var integer
     */
    private $permissionGroupId;

    /**
     * @var string
     */
    private $permissionValue;

    /**
     * @var integer
     */
    private $permissionExtensionId;

    /**
     * @var integer
     */
    private $permissionModuleId;


    /**
     * Get permissionId
     *
     * @return integer 
     */
    public function getPermissionId()
    {
        return $this->permissionId;
    }

    /**
     * Set permissionGroupId
     *
     * @param integer $permissionGroupId
     * @return Permission
     */
    public function setPermissionGroupId($permissionGroupId)
    {
        $this->permissionGroupId = $permissionGroupId;

        return $this;
    }

    /**
     * Get permissionGroupId
     *
     * @return integer 
     */
    public function getPermissionGroupId()
    {
        return $this->permissionGroupId;
    }

    /**
     * Set permissionValue
     *
     * @param string $permissionValue
     * @return Permission
     */
    public function setPermissionValue($permissionValue)
    {
        $this->permissionValue = $permissionValue;

        return $this;
    }

    /**
     * Get permissionValue
     *
     * @return string 
     */
    public function getPermissionValue()
    {
        return $this->permissionValue;
    }

    /**
     * Set permissionExtensionId
     *
     * @param integer $permissionExtensionId
     * @return Permission
     */
    public function setPermissionExtensionId($permissionExtensionId)
    {
        $this->permissionExtensionId = $permissionExtensionId;

        return $this;
    }

    /**
     * Get permissionExtensionId
     *
     * @return integer 
     */
    public function getPermissionExtensionId()
    {
        return $this->permissionExtensionId;
    }

    /**
     * Set permissionModuleId
     *
     * @param integer $permissionModuleId
     * @return Permission
     */
    public function setPermissionModuleId($permissionModuleId)
    {
        $this->permissionModuleId = $permissionModuleId;

        return $this;
    }

    /**
     * Get permissionModuleId
     *
     * @return integer 
     */
    public function getPermissionModuleId()
    {
        return $this->permissionModuleId;
    }
}
