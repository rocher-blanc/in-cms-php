<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Param
 */
class Param
{
    /**
     * @var integer
     */
    private $paramId;

    /**
     * @var string
     */
    private $paramKey;

    /**
     * @var string
     */
    private $paramValue;


    /**
     * Get paramId
     *
     * @return integer 
     */
    public function getParamId()
    {
        return $this->paramId;
    }

    /**
     * Set paramKey
     *
     * @param string $paramKey
     * @return Param
     */
    public function setParamKey($paramKey)
    {
        $this->paramKey = $paramKey;

        return $this;
    }

    /**
     * Get paramKey
     *
     * @return string 
     */
    public function getParamKey()
    {
        return $this->paramKey;
    }

    /**
     * Set paramValue
     *
     * @param string $paramValue
     * @return Param
     */
    public function setParamValue($paramValue)
    {
        $this->paramValue = $paramValue;

        return $this;
    }

    /**
     * Get paramValue
     *
     * @return string 
     */
    public function getParamValue()
    {
        return $this->paramValue;
    }
}
