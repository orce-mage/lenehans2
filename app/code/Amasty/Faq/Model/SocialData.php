<?php

namespace Amasty\Faq\Model;

class SocialData
{
    /**
     * @var string
     */
    private $hrefTemplate;

    /**
     * @var string
     */
    private $imgName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isOpenInNewTab;

    public function __construct($hrefTemplate, $imgName, $name = '', $isOpenInNewTab = true)
    {
        $this->name = $name;
        $this->hrefTemplate = $hrefTemplate;
        $this->imgName = $imgName;
        $this->isOpenInNewTab = $isOpenInNewTab;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return strtolower($this->getName());
    }

    /**
     * @return string
     */
    public function getHrefTemplate()
    {
        return $this->hrefTemplate;
    }

    /**
     * @param string $hrefTemplate
     *
     * @return $this
     */
    public function setHrefTemplate($hrefTemplate)
    {
        $this->hrefTemplate = $hrefTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getImgName()
    {
        return $this->imgName;
    }

    /**
     * @param string $imgName
     *
     * @return $this
     */
    public function setImgName($imgName)
    {
        $this->imgName = $imgName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function isOpenInNewTab()
    {
        return $this->isOpenInNewTab;
    }

    /**
     * @return $this
     */
    public function setIsOpenInNewTab($isOpenInNewTab)
    {
        $this->isOpenInNewTab = $isOpenInNewTab;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
