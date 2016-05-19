<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * PageLang
 */
class PageLang
{
    /**
     * @var integer
     */
    private $pageLangId;

    /**
     * @var integer
     */
    private $pageLangLangId;

    /**
     * @var integer
     */
    private $pageLangPageId;

    /**
     * @var string
     */
    private $pageLangUrl;

    /**
     * @var string
     */
    private $pageLangTitle;

    /**
     * @var string
     */
    private $pageLangDescription;

    /**
     * @var string
     */
    private $pageLangKeyword;


    /**
     * Get pageLangId
     *
     * @return integer 
     */
    public function getPageLangId()
    {
        return $this->pageLangId;
    }

    /**
     * Set pageLangLangId
     *
     * @param integer $pageLangLangId
     * @return PageLang
     */
    public function setPageLangLangId($pageLangLangId)
    {
        $this->pageLangLangId = $pageLangLangId;

        return $this;
    }

    /**
     * Get pageLangLangId
     *
     * @return integer 
     */
    public function getPageLangLangId()
    {
        return $this->pageLangLangId;
    }

    /**
     * Set pageLangPageId
     *
     * @param integer $pageLangPageId
     * @return PageLang
     */
    public function setPageLangPageId($pageLangPageId)
    {
        $this->pageLangPageId = $pageLangPageId;

        return $this;
    }

    /**
     * Get pageLangPageId
     *
     * @return integer 
     */
    public function getPageLangPageId()
    {
        return $this->pageLangPageId;
    }

    /**
     * Set pageLangUrl
     *
     * @param string $pageLangUrl
     * @return PageLang
     */
    public function setPageLangUrl($pageLangUrl)
    {
        $this->pageLangUrl = $pageLangUrl;

        return $this;
    }

    /**
     * Get pageLangUrl
     *
     * @return string 
     */
    public function getPageLangUrl()
    {
        return $this->pageLangUrl;
    }

    /**
     * Set pageLangTitle
     *
     * @param string $pageLangTitle
     * @return PageLang
     */
    public function setPageLangTitle($pageLangTitle)
    {
        $this->pageLangTitle = $pageLangTitle;

        return $this;
    }

    /**
     * Get pageLangTitle
     *
     * @return string 
     */
    public function getPageLangTitle()
    {
        return $this->pageLangTitle;
    }

    /**
     * Set pageLangDescription
     *
     * @param string $pageLangDescription
     * @return PageLang
     */
    public function setPageLangDescription($pageLangDescription)
    {
        $this->pageLangDescription = $pageLangDescription;

        return $this;
    }

    /**
     * Get pageLangDescription
     *
     * @return string 
     */
    public function getPageLangDescription()
    {
        return $this->pageLangDescription;
    }

    /**
     * Set pageLangKeyword
     *
     * @param string $pageLangKeyword
     * @return PageLang
     */
    public function setPageLangKeyword($pageLangKeyword)
    {
        $this->pageLangKeyword = $pageLangKeyword;

        return $this;
    }

    /**
     * Get pageLangKeyword
     *
     * @return string 
     */
    public function getPageLangKeyword()
    {
        return $this->pageLangKeyword;
    }
}
