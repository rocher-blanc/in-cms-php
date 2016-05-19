<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuElement
 */
class MenuElement
{
    /**
     * @var integer
     */
    private $menuElementId;

    /**
     * @var integer
     */
    private $menuElementMenuId;

    /**
     * @var integer
     */
    private $menuElementParentId;

    /**
     * @var integer
     */
    private $menuElementOrder;

    /**
     * @var string
     */
    private $menuElementType;

    /**
     * @var boolean
     */
    private $menuElementLinkBlank;

    /**
     * @var string
     */
    private $menuElementLinkHref;

    /**
     * @var integer
     */
    private $menuElementModuleId;

    /**
     * @var integer
     */
    private $menuElementValueId;

    /**
     * @var boolean
     */
    private $menuElementMaxLevel;

    /**
     * @var boolean
     */
    private $menuElementHasSubmenu;

    /**
     * @var string
     */
    private $menuElementOption;


    /**
     * Get menuElementId
     *
     * @return integer 
     */
    public function getMenuElementId()
    {
        return $this->menuElementId;
    }

    /**
     * Set menuElementMenuId
     *
     * @param integer $menuElementMenuId
     * @return MenuElement
     */
    public function setMenuElementMenuId($menuElementMenuId)
    {
        $this->menuElementMenuId = $menuElementMenuId;

        return $this;
    }

    /**
     * Get menuElementMenuId
     *
     * @return integer 
     */
    public function getMenuElementMenuId()
    {
        return $this->menuElementMenuId;
    }

    /**
     * Set menuElementParentId
     *
     * @param integer $menuElementParentId
     * @return MenuElement
     */
    public function setMenuElementParentId($menuElementParentId)
    {
        $this->menuElementParentId = $menuElementParentId;

        return $this;
    }

    /**
     * Get menuElementParentId
     *
     * @return integer 
     */
    public function getMenuElementParentId()
    {
        return $this->menuElementParentId;
    }

    /**
     * Set menuElementOrder
     *
     * @param integer $menuElementOrder
     * @return MenuElement
     */
    public function setMenuElementOrder($menuElementOrder)
    {
        $this->menuElementOrder = $menuElementOrder;

        return $this;
    }

    /**
     * Get menuElementOrder
     *
     * @return integer 
     */
    public function getMenuElementOrder()
    {
        return $this->menuElementOrder;
    }

    /**
     * Set menuElementType
     *
     * @param string $menuElementType
     * @return MenuElement
     */
    public function setMenuElementType($menuElementType)
    {
        $this->menuElementType = $menuElementType;

        return $this;
    }

    /**
     * Get menuElementType
     *
     * @return string 
     */
    public function getMenuElementType()
    {
        return $this->menuElementType;
    }

    /**
     * Set menuElementLinkBlank
     *
     * @param boolean $menuElementLinkBlank
     * @return MenuElement
     */
    public function setMenuElementLinkBlank($menuElementLinkBlank)
    {
        $this->menuElementLinkBlank = $menuElementLinkBlank;

        return $this;
    }

    /**
     * Get menuElementLinkBlank
     *
     * @return boolean 
     */
    public function getMenuElementLinkBlank()
    {
        return $this->menuElementLinkBlank;
    }

    /**
     * Set menuElementLinkHref
     *
     * @param string $menuElementLinkHref
     * @return MenuElement
     */
    public function setMenuElementLinkHref($menuElementLinkHref)
    {
        $this->menuElementLinkHref = $menuElementLinkHref;

        return $this;
    }

    /**
     * Get menuElementLinkHref
     *
     * @return string 
     */
    public function getMenuElementLinkHref()
    {
        return $this->menuElementLinkHref;
    }

    /**
     * Set menuElementModuleId
     *
     * @param integer $menuElementModuleId
     * @return MenuElement
     */
    public function setMenuElementModuleId($menuElementModuleId)
    {
        $this->menuElementModuleId = $menuElementModuleId;

        return $this;
    }

    /**
     * Get menuElementModuleId
     *
     * @return integer 
     */
    public function getMenuElementModuleId()
    {
        return $this->menuElementModuleId;
    }

    /**
     * Set menuElementValueId
     *
     * @param integer $menuElementValueId
     * @return MenuElement
     */
    public function setMenuElementValueId($menuElementValueId)
    {
        $this->menuElementValueId = $menuElementValueId;

        return $this;
    }

    /**
     * Get menuElementValueId
     *
     * @return integer 
     */
    public function getMenuElementValueId()
    {
        return $this->menuElementValueId;
    }

    /**
     * Set menuElementMaxLevel
     *
     * @param boolean $menuElementMaxLevel
     * @return MenuElement
     */
    public function setMenuElementMaxLevel($menuElementMaxLevel)
    {
        $this->menuElementMaxLevel = $menuElementMaxLevel;

        return $this;
    }

    /**
     * Get menuElementMaxLevel
     *
     * @return boolean 
     */
    public function getMenuElementMaxLevel()
    {
        return $this->menuElementMaxLevel;
    }

    /**
     * Set menuElementHasSubmenu
     *
     * @param boolean $menuElementHasSubmenu
     * @return MenuElement
     */
    public function setMenuElementHasSubmenu($menuElementHasSubmenu)
    {
        $this->menuElementHasSubmenu = $menuElementHasSubmenu;

        return $this;
    }

    /**
     * Get menuElementHasSubmenu
     *
     * @return boolean 
     */
    public function getMenuElementHasSubmenu()
    {
        return $this->menuElementHasSubmenu;
    }

    /**
     * Set menuElementOption
     *
     * @param string $menuElementOption
     * @return MenuElement
     */
    public function setMenuElementOption($menuElementOption)
    {
        $this->menuElementOption = $menuElementOption;

        return $this;
    }

    /**
     * Get menuElementOption
     *
     * @return string 
     */
    public function getMenuElementOption()
    {
        return $this->menuElementOption;
    }
}
