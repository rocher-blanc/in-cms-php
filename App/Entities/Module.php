<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Module
 */
class Module
{
    /**
     * @var integer
     */
    private $moduleId;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $moduleClassName;

    /**
     * @var boolean
     */
    private $moduleActive;

    /**
     * @var string
     */
    private $moduleIcon;

    /**
     * @var integer
     */
    private $moduleModuleGroupId;

    /**
     * @var integer
     */
    private $moduleOrder;

    /**
     * @var boolean
     */
    private $moduleDefault;

    /**
     * @var float
     */
    private $modulePriority;


    /**
     * Get moduleId
     *
     * @return integer 
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Set moduleName
     *
     * @param string $moduleName
     * @return Module
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * Get moduleName
     *
     * @return string 
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set moduleClassName
     *
     * @param string $moduleClassName
     * @return Module
     */
    public function setModuleClassName($moduleClassName)
    {
        $this->moduleClassName = $moduleClassName;

        return $this;
    }

    /**
     * Get moduleClassName
     *
     * @return string 
     */
    public function getModuleClassName()
    {
        return $this->moduleClassName;
    }

    /**
     * Set moduleActive
     *
     * @param boolean $moduleActive
     * @return Module
     */
    public function setModuleActive($moduleActive)
    {
        $this->moduleActive = $moduleActive;

        return $this;
    }

    /**
     * Get moduleActive
     *
     * @return boolean 
     */
    public function getModuleActive()
    {
        return $this->moduleActive;
    }

    /**
     * Set moduleIcon
     *
     * @param string $moduleIcon
     * @return Module
     */
    public function setModuleIcon($moduleIcon)
    {
        $this->moduleIcon = $moduleIcon;

        return $this;
    }

    /**
     * Get moduleIcon
     *
     * @return string 
     */
    public function getModuleIcon()
    {
        return $this->moduleIcon;
    }

    /**
     * Set moduleModuleGroupId
     *
     * @param integer $moduleModuleGroupId
     * @return Module
     */
    public function setModuleModuleGroupId($moduleModuleGroupId)
    {
        $this->moduleModuleGroupId = $moduleModuleGroupId;

        return $this;
    }

    /**
     * Get moduleModuleGroupId
     *
     * @return integer 
     */
    public function getModuleModuleGroupId()
    {
        return $this->moduleModuleGroupId;
    }

    /**
     * Set moduleOrder
     *
     * @param integer $moduleOrder
     * @return Module
     */
    public function setModuleOrder($moduleOrder)
    {
        $this->moduleOrder = $moduleOrder;

        return $this;
    }

    /**
     * Get moduleOrder
     *
     * @return integer 
     */
    public function getModuleOrder()
    {
        return $this->moduleOrder;
    }

    /**
     * Set moduleDefault
     *
     * @param boolean $moduleDefault
     * @return Module
     */
    public function setModuleDefault($moduleDefault)
    {
        $this->moduleDefault = $moduleDefault;

        return $this;
    }

    /**
     * Get moduleDefault
     *
     * @return boolean 
     */
    public function getModuleDefault()
    {
        return $this->moduleDefault;
    }

    /**
     * Set modulePriority
     *
     * @param float $modulePriority
     * @return Module
     */
    public function setModulePriority($modulePriority)
    {
        $this->modulePriority = $modulePriority;

        return $this;
    }

    /**
     * Get modulePriority
     *
     * @return float 
     */
    public function getModulePriority()
    {
        return $this->modulePriority;
    }
}
