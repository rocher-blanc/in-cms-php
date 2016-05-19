<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Seo
 */
class Seo
{
    /**
     * @var integer
     */
    private $seoId;

    /**
     * @var integer
     */
    private $seoModuleId;

    /**
     * @var integer
     */
    private $seoElementId;

    /**
     * @var integer
     */
    private $seoLangId;

    /**
     * @var string
     */
    private $seoUrl;

    /**
     * @var string
     */
    private $seoTitle;

    /**
     * @var string
     */
    private $seoDescription;

    /**
     * @var string
     */
    private $seoKeyword;


    /**
     * Get seoId
     *
     * @return integer 
     */
    public function getSeoId()
    {
        return $this->seoId;
    }

    /**
     * Set seoModuleId
     *
     * @param integer $seoModuleId
     * @return Seo
     */
    public function setSeoModuleId($seoModuleId)
    {
        $this->seoModuleId = $seoModuleId;

        return $this;
    }

    /**
     * Get seoModuleId
     *
     * @return integer 
     */
    public function getSeoModuleId()
    {
        return $this->seoModuleId;
    }

    /**
     * Set seoElementId
     *
     * @param integer $seoElementId
     * @return Seo
     */
    public function setSeoElementId($seoElementId)
    {
        $this->seoElementId = $seoElementId;

        return $this;
    }

    /**
     * Get seoElementId
     *
     * @return integer 
     */
    public function getSeoElementId()
    {
        return $this->seoElementId;
    }

    /**
     * Set seoLangId
     *
     * @param integer $seoLangId
     * @return Seo
     */
    public function setSeoLangId($seoLangId)
    {
        $this->seoLangId = $seoLangId;

        return $this;
    }

    /**
     * Get seoLangId
     *
     * @return integer 
     */
    public function getSeoLangId()
    {
        return $this->seoLangId;
    }

    /**
     * Set seoUrl
     *
     * @param string $seoUrl
     * @return Seo
     */
    public function setSeoUrl($seoUrl)
    {
        $this->seoUrl = $seoUrl;

        return $this;
    }

    /**
     * Get seoUrl
     *
     * @return string 
     */
    public function getSeoUrl()
    {
        return $this->seoUrl;
    }

    /**
     * Set seoTitle
     *
     * @param string $seoTitle
     * @return Seo
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * Get seoTitle
     *
     * @return string 
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return Seo
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string 
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set seoKeyword
     *
     * @param string $seoKeyword
     * @return Seo
     */
    public function setSeoKeyword($seoKeyword)
    {
        $this->seoKeyword = $seoKeyword;

        return $this;
    }

    /**
     * Get seoKeyword
     *
     * @return string 
     */
    public function getSeoKeyword()
    {
        return $this->seoKeyword;
    }
}
