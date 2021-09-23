<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model;

/**
 * License backend Notifications
 */
class Notifications extends \Magento\AdminNotification\Model\System\Message
{
    /**
     * Notifications constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     * @param \Wyomind\Framework\Helper\Module $license
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Wyomind\Framework\Helper\Module $license,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);


        if (class_exists("\Magento\Framework\Serialize\Serializer\Serialize")) {
            $this->serializer = $objectManager->get("\Magento\Framework\Serialize\Serializer\Serialize");
        } else {
            $this->serializer = null;
        }


        $this->cacheManager = $context->getCacheManager();
        $this->session = $session;
        $this->license = $license;
        $this->refreshCache = false;


        if ($request->getParam("isAjax") !== "true") {
            $res = $this->license->getValues();

            foreach ($res as $ext) {
                $this->license->checkActivation($ext);
            }
            $this->warnings = $this->license->getWarnings();
            if ($this->refreshCache) {
                $this->cacheManager->clean(['config']);
            }
            // Mage >= 2.2
            if ($this->serializer) {
                $session->setData("wyomind_framework_warnings", $this->serializer->serialize($this->warnings));
            } else {
                $serialize = "serialize";
                $session->setData("wyomind_framework_warnings", $serialize($this->warnings));
            }
        } else {
            // Mage >= 2.2
            if ($this->serializer) {
                $this->warnings = $this->serializer->unserialize($session->getData("wyomind_framework_warnings"));
            } else {
                $unserialize = "unserialize";
                $this->warnings = $unserialize($session->getData("wyomind_framework_warnings"));
            }
        }

    }


    /**
     * Transform XML to array
     * @param string $xml
     * @return array
     */
    public function XML2Array($xml)
    {
        $newArray = [];
        $array = (array)$xml;
        foreach ($array as $key => $value) {
            $value = (array)$value;
            if (isset($value [0])) {
                $newArray [$key] = trim($value [0]);
            } else {
                $newArray [$key] = $this->XML2Array($value, true);
            }
        }
        return $newArray;
    }


    /**
     * @return string
     */
    public function getIdentity()
    {
        $md5 = "md5";
        return $md5($this->getText());
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * @return string
     */
    public function getText()
    {
        $html = null;
        $count = count($this->warnings);

        for ($i = 0; $i < $count; $i++) {
            $html .= "<div style='padding-bottom:5px;" . (($i != 0) ? "margin-top:5px;" : "") . "" . (($i < $count - 1) ? "border-bottom:1px solid gray;" : "") . "'>" . $this->warnings[$i] . "</div>";
        }
        return $html;
    }

    /**
     * @return boolean
     */
    public function isDisplayed()
    {
        return count($this->warnings) > 0;
    }
}
