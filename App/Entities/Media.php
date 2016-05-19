<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 */
class Media
{
    /**
     * @var integer
     */
    private $mediaId;

    /**
     * @var integer
     */
    private $mediaModuleId;

    /**
     * @var string
     */
    private $mediaName;

    /**
     * @var integer
     */
    private $mediaSize;

    /**
     * @var string
     */
    private $mediaType;


    /**
     * Get mediaId
     *
     * @return integer 
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * Set mediaModuleId
     *
     * @param integer $mediaModuleId
     * @return Media
     */
    public function setMediaModuleId($mediaModuleId)
    {
        $this->mediaModuleId = $mediaModuleId;

        return $this;
    }

    /**
     * Get mediaModuleId
     *
     * @return integer 
     */
    public function getMediaModuleId()
    {
        return $this->mediaModuleId;
    }

    /**
     * Set mediaName
     *
     * @param string $mediaName
     * @return Media
     */
    public function setMediaName($mediaName)
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    /**
     * Get mediaName
     *
     * @return string 
     */
    public function getMediaName()
    {
        return $this->mediaName;
    }

    /**
     * Set mediaSize
     *
     * @param integer $mediaSize
     * @return Media
     */
    public function setMediaSize($mediaSize)
    {
        $this->mediaSize = $mediaSize;

        return $this;
    }

    /**
     * Get mediaSize
     *
     * @return integer 
     */
    public function getMediaSize()
    {
        return $this->mediaSize;
    }

    /**
     * Set mediaType
     *
     * @param string $mediaType
     * @return Media
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    /**
     * Get mediaType
     *
     * @return string 
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }
}
