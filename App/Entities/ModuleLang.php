<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModuleLang
 */
class ModuleLang
{
    /**
     * @var integer
     */
    private $moduleLangId;

    /**
     * @var integer
     */
    private $moduleLangLangId;

    /**
     * @var integer
     */
    private $moduleLangModuleId;

    /**
     * @var string
     */
    private $moduleLangUrl;

    /**
     * @var string
     */
    private $moduleLangTitle;

    /**
     * @var string
     */
    private $moduleLangDescription;

    /**
     * @var string
     */
    private $moduleLangKeyword;


    /**
     * Get moduleLangId
     *
     * @return integer 
     */
    public function getModuleLangId()
    {
        return $this->moduleLangId;
    }

    /**
     * Set moduleLangLangId
     *
     * @param integer $moduleLangLangId
     * @return ModuleLang
     */
    public function setModuleLangLangId($moduleLangLangId)
    {
        $this->moduleLangLangId = $moduleLangLangId;

        return $this;
    }

    /**
     * Get moduleLangLangId
     *
     * @return integer 
     */
    public function getModuleLangLangId()
    {
        return $this->moduleLangLangId;
    }

    /**
     * Set moduleLangModuleId
     *
     * @param integer $moduleLangModuleId
     * @return ModuleLang
     */
    public function setModuleLangModuleId($moduleLangModuleId)
    {
        $this->moduleLangModuleId = $moduleLangModuleId;

        return $this;
    }

    /**
     * Get moduleLangModuleId
     *
     * @return integer 
     */
    public function getModuleLangModuleId()
    {
        return $this->moduleLangModuleId;
    }

    /**
     * Set moduleLangUrl
     *
     * @param string $moduleLangUrl
     * @return ModuleLang
     */
    public function setModuleLangUrl($moduleLangUrl)
    {
        $this->moduleLangUrl = $moduleLangUrl;

        return $this;
    }

    /**
     * Get moduleLangUrl
     *
     * @return string 
     */
    public function getModuleLangUrl()
    {
        return $this->moduleLangUrl;
    }

    /**
     * Set moduleLangTitle
     *
     * @param string $moduleLangTitle
     * @return ModuleLang
     */
    public function setModuleLangTitle($moduleLangTitle)
    {
        $this->moduleLangTitle = $moduleLangTitle;

        return $this;
    }

    /**
     * Get moduleLangTitle
     *
     * @return string 
     */
    public function getModuleLangTitle()
    {
        return $this->moduleLangTitle;
    }

    /**
     * Set moduleLangDescription
     *
     * @param string $moduleLangDescription
     * @return ModuleLang
     */
    public function setModuleLangDescription($moduleLangDescription)
    {
        $this->moduleLangDescription = $moduleLangDescription;

        return $this;
    }

    /**
     * Get moduleLangDescription
     *
     * @return string 
     */
    public function getModuleLangDescription()
    {
        return $this->moduleLangDescription;
    }

    /**
     * Set moduleLangKeyword
     *
     * @param string $moduleLangKeyword
     * @return ModuleLang
     */
    public function setModuleLangKeyword($moduleLangKeyword)
    {
        $this->moduleLangKeyword = $moduleLangKeyword;

        return $this;
    }

    /**
     * Get moduleLangKeyword
     *
     * @return string 
     */
    public function getModuleLangKeyword()
    {
        return $this->moduleLangKeyword;
    }
}
