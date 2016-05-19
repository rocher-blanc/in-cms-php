<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModuleGroup
 */
class ModuleGroup
{
    /**
     * @var integer
     */
    private $moduleGroupId;

    /**
     * @var string
     */
    private $moduleGroupName;

    /**
     * @var string
     */
    private $moduleGroupIcon;

    /**
     * @var integer
     */
    private $moduleGroupOrder;

    /**
     * @var boolean
     */
    private $moduleGroupActive;


    /**
     * Get moduleGroupId
     *
     * @return integer 
     */
    public function getModuleGroupId()
    {
        return $this->moduleGroupId;
    }

    /**
     * Set moduleGroupName
     *
     * @param string $moduleGroupName
     * @return ModuleGroup
     */
    public function setModuleGroupName($moduleGroupName)
    {
        $this->moduleGroupName = $moduleGroupName;

        return $this;
    }

    /**
     * Get moduleGroupName
     *
     * @return string 
     */
    public function getModuleGroupName()
    {
        return $this->moduleGroupName;
    }

    /**
     * Set moduleGroupIcon
     *
     * @param string $moduleGroupIcon
     * @return ModuleGroup
     */
    public function setModuleGroupIcon($moduleGroupIcon)
    {
        $this->moduleGroupIcon = $moduleGroupIcon;

        return $this;
    }

    /**
     * Get moduleGroupIcon
     *
     * @return string 
     */
    public function getModuleGroupIcon()
    {
        return $this->moduleGroupIcon;
    }

    /**
     * Set moduleGroupOrder
     *
     * @param integer $moduleGroupOrder
     * @return ModuleGroup
     */
    public function setModuleGroupOrder($moduleGroupOrder)
    {
        $this->moduleGroupOrder = $moduleGroupOrder;

        return $this;
    }

    /**
     * Get moduleGroupOrder
     *
     * @return integer 
     */
    public function getModuleGroupOrder()
    {
        return $this->moduleGroupOrder;
    }

    /**
     * Set moduleGroupActive
     *
     * @param boolean $moduleGroupActive
     * @return ModuleGroup
     */
    public function setModuleGroupActive($moduleGroupActive)
    {
        $this->moduleGroupActive = $moduleGroupActive;

        return $this;
    }

    /**
     * Get moduleGroupActive
     *
     * @return boolean 
     */
    public function getModuleGroupActive()
    {
        return $this->moduleGroupActive;
    }
}
