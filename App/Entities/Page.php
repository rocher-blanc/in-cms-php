<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 */
class Page
{
    /**
     * @var integer
     */
    private $pageId;

    /**
     * @var string
     */
    private $pageName;

    /**
     * @var boolean
     */
    private $pageDefault;

    /**
     * @var string
     */
    private $pageController;

    /**
     * @var boolean
     */
    private $pageActive;

    /**
     * @var float
     */
    private $pagePriority;


    /**
     * Get pageId
     *
     * @return integer 
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set pageName
     *
     * @param string $pageName
     * @return Page
     */
    public function setPageName($pageName)
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * Get pageName
     *
     * @return string 
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Set pageDefault
     *
     * @param boolean $pageDefault
     * @return Page
     */
    public function setPageDefault($pageDefault)
    {
        $this->pageDefault = $pageDefault;

        return $this;
    }

    /**
     * Get pageDefault
     *
     * @return boolean 
     */
    public function getPageDefault()
    {
        return $this->pageDefault;
    }

    /**
     * Set pageController
     *
     * @param string $pageController
     * @return Page
     */
    public function setPageController($pageController)
    {
        $this->pageController = $pageController;

        return $this;
    }

    /**
     * Get pageController
     *
     * @return string 
     */
    public function getPageController()
    {
        return $this->pageController;
    }

    /**
     * Set pageActive
     *
     * @param boolean $pageActive
     * @return Page
     */
    public function setPageActive($pageActive)
    {
        $this->pageActive = $pageActive;

        return $this;
    }

    /**
     * Get pageActive
     *
     * @return boolean 
     */
    public function getPageActive()
    {
        return $this->pageActive;
    }

    /**
     * Set pagePriority
     *
     * @param float $pagePriority
     * @return Page
     */
    public function setPagePriority($pagePriority)
    {
        $this->pagePriority = $pagePriority;

        return $this;
    }

    /**
     * Get pagePriority
     *
     * @return float 
     */
    public function getPagePriority()
    {
        return $this->pagePriority;
    }
}
