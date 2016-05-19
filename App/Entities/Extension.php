<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extension
 */
class Extension
{
    /**
     * @var integer
     */
    private $extensionId;

    /**
     * @var string
     */
    private $extensionTechnicalName;

    /**
     * @var string
     */
    private $extensionName;

    /**
     * @var boolean
     */
    private $extensionPermAdd;

    /**
     * @var boolean
     */
    private $extensionPermUpdate;

    /**
     * @var boolean
     */
    private $extensionPermDelete;


    /**
     * Get extensionId
     *
     * @return integer 
     */
    public function getExtensionId()
    {
        return $this->extensionId;
    }

    /**
     * Set extensionTechnicalName
     *
     * @param string $extensionTechnicalName
     * @return Extension
     */
    public function setExtensionTechnicalName($extensionTechnicalName)
    {
        $this->extensionTechnicalName = $extensionTechnicalName;

        return $this;
    }

    /**
     * Get extensionTechnicalName
     *
     * @return string 
     */
    public function getExtensionTechnicalName()
    {
        return $this->extensionTechnicalName;
    }

    /**
     * Set extensionName
     *
     * @param string $extensionName
     * @return Extension
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;

        return $this;
    }

    /**
     * Get extensionName
     *
     * @return string 
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * Set extensionPermAdd
     *
     * @param boolean $extensionPermAdd
     * @return Extension
     */
    public function setExtensionPermAdd($extensionPermAdd)
    {
        $this->extensionPermAdd = $extensionPermAdd;

        return $this;
    }

    /**
     * Get extensionPermAdd
     *
     * @return boolean 
     */
    public function getExtensionPermAdd()
    {
        return $this->extensionPermAdd;
    }

    /**
     * Set extensionPermUpdate
     *
     * @param boolean $extensionPermUpdate
     * @return Extension
     */
    public function setExtensionPermUpdate($extensionPermUpdate)
    {
        $this->extensionPermUpdate = $extensionPermUpdate;

        return $this;
    }

    /**
     * Get extensionPermUpdate
     *
     * @return boolean 
     */
    public function getExtensionPermUpdate()
    {
        return $this->extensionPermUpdate;
    }

    /**
     * Set extensionPermDelete
     *
     * @param boolean $extensionPermDelete
     * @return Extension
     */
    public function setExtensionPermDelete($extensionPermDelete)
    {
        $this->extensionPermDelete = $extensionPermDelete;

        return $this;
    }

    /**
     * Get extensionPermDelete
     *
     * @return boolean 
     */
    public function getExtensionPermDelete()
    {
        return $this->extensionPermDelete;
    }
}
