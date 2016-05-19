<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuElementLang
 */
class MenuElementLang
{
    /**
     * @var integer
     */
    private $menuElementLangId;

    /**
     * @var integer
     */
    private $menuElementLangLangId;

    /**
     * @var integer
     */
    private $menuElementLangMenuElementId;

    /**
     * @var string
     */
    private $menuElementLangLabel;


    /**
     * Get menuElementLangId
     *
     * @return integer 
     */
    public function getMenuElementLangId()
    {
        return $this->menuElementLangId;
    }

    /**
     * Set menuElementLangLangId
     *
     * @param integer $menuElementLangLangId
     * @return MenuElementLang
     */
    public function setMenuElementLangLangId($menuElementLangLangId)
    {
        $this->menuElementLangLangId = $menuElementLangLangId;

        return $this;
    }

    /**
     * Get menuElementLangLangId
     *
     * @return integer 
     */
    public function getMenuElementLangLangId()
    {
        return $this->menuElementLangLangId;
    }

    /**
     * Set menuElementLangMenuElementId
     *
     * @param integer $menuElementLangMenuElementId
     * @return MenuElementLang
     */
    public function setMenuElementLangMenuElementId($menuElementLangMenuElementId)
    {
        $this->menuElementLangMenuElementId = $menuElementLangMenuElementId;

        return $this;
    }

    /**
     * Get menuElementLangMenuElementId
     *
     * @return integer 
     */
    public function getMenuElementLangMenuElementId()
    {
        return $this->menuElementLangMenuElementId;
    }

    /**
     * Set menuElementLangLabel
     *
     * @param string $menuElementLangLabel
     * @return MenuElementLang
     */
    public function setMenuElementLangLabel($menuElementLangLabel)
    {
        $this->menuElementLangLabel = $menuElementLangLabel;

        return $this;
    }

    /**
     * Get menuElementLangLabel
     *
     * @return string 
     */
    public function getMenuElementLangLabel()
    {
        return $this->menuElementLangLabel;
    }
}
