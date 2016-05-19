<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lang
 */
class Lang
{
    /**
     * @var integer
     */
    private $langId;

    /**
     * @var string
     */
    private $langDisplay;

    /**
     * @var string
     */
    private $langName;

    /**
     * @var string
     */
    private $langUrl;

    /**
     * @var string
     */
    private $langFlag;

    /**
     * @var string
     */
    private $langLocale;

    /**
     * @var integer
     */
    private $langStatus;

    /**
     * @var boolean
     */
    private $langFront;


    /**
     * Get langId
     *
     * @return integer 
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * Set langDisplay
     *
     * @param string $langDisplay
     * @return Lang
     */
    public function setLangDisplay($langDisplay)
    {
        $this->langDisplay = $langDisplay;

        return $this;
    }

    /**
     * Get langDisplay
     *
     * @return string 
     */
    public function getLangDisplay()
    {
        return $this->langDisplay;
    }

    /**
     * Set langName
     *
     * @param string $langName
     * @return Lang
     */
    public function setLangName($langName)
    {
        $this->langName = $langName;

        return $this;
    }

    /**
     * Get langName
     *
     * @return string 
     */
    public function getLangName()
    {
        return $this->langName;
    }

    /**
     * Set langUrl
     *
     * @param string $langUrl
     * @return Lang
     */
    public function setLangUrl($langUrl)
    {
        $this->langUrl = $langUrl;

        return $this;
    }

    /**
     * Get langUrl
     *
     * @return string 
     */
    public function getLangUrl()
    {
        return $this->langUrl;
    }

    /**
     * Set langFlag
     *
     * @param string $langFlag
     * @return Lang
     */
    public function setLangFlag($langFlag)
    {
        $this->langFlag = $langFlag;

        return $this;
    }

    /**
     * Get langFlag
     *
     * @return string 
     */
    public function getLangFlag()
    {
        return $this->langFlag;
    }

    /**
     * Set langLocale
     *
     * @param string $langLocale
     * @return Lang
     */
    public function setLangLocale($langLocale)
    {
        $this->langLocale = $langLocale;

        return $this;
    }

    /**
     * Get langLocale
     *
     * @return string 
     */
    public function getLangLocale()
    {
        return $this->langLocale;
    }

    /**
     * Set langStatus
     *
     * @param integer $langStatus
     * @return Lang
     */
    public function setLangStatus($langStatus)
    {
        $this->langStatus = $langStatus;

        return $this;
    }

    /**
     * Get langStatus
     *
     * @return integer 
     */
    public function getLangStatus()
    {
        return $this->langStatus;
    }

    /**
     * Set langFront
     *
     * @param boolean $langFront
     * @return Lang
     */
    public function setLangFront($langFront)
    {
        $this->langFront = $langFront;

        return $this;
    }

    /**
     * Get langFront
     *
     * @return boolean 
     */
    public function getLangFront()
    {
        return $this->langFront;
    }
}
