<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 */
class Log
{
    /**
     * @var integer
     */
    private $logId;

    /**
     * @var \DateTime
     */
    private $logDate;

    /**
     * @var integer
     */
    private $logUserId;

    /**
     * @var boolean
     */
    private $logCode;

    /**
     * @var boolean
     */
    private $logType;

    /**
     * @var string
     */
    private $logValue;


    /**
     * Get logId
     *
     * @return integer 
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * Set logDate
     *
     * @param \DateTime $logDate
     * @return Log
     */
    public function setLogDate($logDate)
    {
        $this->logDate = $logDate;

        return $this;
    }

    /**
     * Get logDate
     *
     * @return \DateTime 
     */
    public function getLogDate()
    {
        return $this->logDate;
    }

    /**
     * Set logUserId
     *
     * @param integer $logUserId
     * @return Log
     */
    public function setLogUserId($logUserId)
    {
        $this->logUserId = $logUserId;

        return $this;
    }

    /**
     * Get logUserId
     *
     * @return integer 
     */
    public function getLogUserId()
    {
        return $this->logUserId;
    }

    /**
     * Set logCode
     *
     * @param boolean $logCode
     * @return Log
     */
    public function setLogCode($logCode)
    {
        $this->logCode = $logCode;

        return $this;
    }

    /**
     * Get logCode
     *
     * @return boolean 
     */
    public function getLogCode()
    {
        return $this->logCode;
    }

    /**
     * Set logType
     *
     * @param boolean $logType
     * @return Log
     */
    public function setLogType($logType)
    {
        $this->logType = $logType;

        return $this;
    }

    /**
     * Get logType
     *
     * @return boolean 
     */
    public function getLogType()
    {
        return $this->logType;
    }

    /**
     * Set logValue
     *
     * @param string $logValue
     * @return Log
     */
    public function setLogValue($logValue)
    {
        $this->logValue = $logValue;

        return $this;
    }

    /**
     * Get logValue
     *
     * @return string 
     */
    public function getLogValue()
    {
        return $this->logValue;
    }
}
