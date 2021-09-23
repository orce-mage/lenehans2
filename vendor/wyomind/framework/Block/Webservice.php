<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block;

/**
 *
 */
class Webservice extends \Magento\Framework\View\Element\Template
{


    /**
     * @var
     */
    protected $_cacheManager = null;

    /**
     * @var
     */
    protected $_session = null;

    /**
     * @var string
     */
    protected $_message = "";


    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor = null;

    /**
     * @var \Wyomind\Framework\Model\ResourceModel\Config
     */
    protected $_configResourceModel = null;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;


    /**
     * Webservice constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Model\Context $contextBis
     * @param \Wyomind\Framework\Helper\License $license
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Wyomind\Framework\Model\ResourceModel\Config $configResourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Model\Context $contextBis,
        \Wyomind\Framework\Helper\License $license,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Wyomind\Framework\Model\ResourceModel\Config $configResourceModel,
        array $data = []
    ) {
    

        parent::__construct($context, $data);


        $this->_cacheManager = $contextBis->getCacheManager();
        $this->_session = $context->getSession();

        $this->license = $license;
        $this->logger = $license->getLogger();
        $this->_encryptor = $encryptor;
        $this->_configResourceModel = $configResourceModel;


        if ($this->getRequest()->getParam("namespace")) {
            list($foo, $namespace) = explode("/", $this->getRequest()->getParam("namespace"));
            $wgsActivationKey = $this->getRequest()->getParam("wgs_activation_key");
            $wgsStatus = $this->getRequest()->getParam("wgs_status");
            $wgsVersion = $this->getRequest()->getParam("wgs_version");
            $wgsActivation = $this->getRequest()->getParam("wgs_activation");
            $wgsMessage = $this->getRequest()->getParam("wgs_message");
        } else {
            $this->_message = "<div class='message message-error error'>" . __("Invalid data.") . "</div>";
        }

        if (isset($wgsActivationKey)) {
            if (isset($wgsStatus)) {
                switch ($wgsStatus) {
                    case "success":
                        $this->logger->notice("---------------------------------");
                        $this->logger->notice("Manual activation for " . $namespace . " (frontend) => success");
                        $this->logger->notice("Activation key: " . $wgsActivationKey);
                        $this->logger->notice("Version: " . $wgsVersion);
                        $this->logger->notice("License code: " . $wgsActivation);
                        $this->license->setDefaultConfig("$namespace/license/extension_version", $wgsVersion);
                        $this->license->setDefaultConfigCrypted("$namespace/license/activation_key", $wgsActivationKey);
                        $this->license->setDefaultConfigCrypted("$namespace/license/activation_code", $wgsActivation);
                        $this->_session->setData("update_" . $namespace, "false");
                        $this->_cacheManager->clean(['config']);
                        $this->_message = "<div class='message message-success success'>" . $wgsMessage . "</div>";
                        $this->_cacheManager->clean(['config']);
                        break;
                    case "error":
                        $this->logger->notice("---------------------------------");
                        $this->logger->notice("Manual activation for " . $namespace . " (frontend) => error");
                        $this->logger->notice("Activation key: " . $wgsActivationKey);
                        $this->logger->notice("Version: " . $wgsVersion);
                        $this->logger->notice("License code: " . $wgsMessage);
                        $this->_message = "<div class='message message-success success'>" . $wgsMessage . "</div>";
                        $this->license->setDefaultConfig("$namespace/license/activation_code", "");
                        $this->_cacheManager->clean(['config']);
                        break;
                    case "uninstall":
                        $this->logger->notice("---------------------------------");
                        $this->logger->notice("Manual uninstallation for " . $namespace . " (frontend)");
                        $this->logger->notice("Message: " . $wgsMessage);
                        $this->_message = "<div class='message message-success success'>" . $wgsMessage . "</div>";
                        $this->license->setDefaultConfig("$namespace/license/activation_key", "");
                        $this->license->setDefaultConfig("$namespace/license/activation_code", "");
                        $this->_cacheManager->clean(['config']);
                        break;
                    default:
                        $this->logger->notice("---------------------------------");
                        $this->logger->notice("Frontend");
                        $this->logger->notice("Message: " . __("An error occurs while retrieving the license activation (500)"));
                        $this->_message = __("An error occurs while retrieving the license activation (500)");
                        $this->license->setDefaultConfig("$namespace/license/activation_code", "");

                        break;
                }
            } else {
                $this->logger->notice("---------------------------------");
                $this->logger->notice("Frontend");
                $this->logger->notice("Message: " . __("An error occurs while retrieving license activation (404)."));
                $this->_message = "<div class='message message-error error'>" . __("An error occurs while retrieving license activation (404).") . "</div>";
            }
        } else {
            $this->logger->notice("---------------------------------");
            $this->logger->notice("Frontend");
            $this->logger->notice("Message: " . __("Invalid activation key."));
//            $this->_message="<div class='message message-error error'>" . __("Invalid activation key.") . "</div>";
        }
        $this->objectManager = $objectManager;
    }

    /**
     * Log message in the Wyomind_Framework log file
     * @param string $msg
     */
    public function notice($msg)
    {
        if ($this->logEnabled) {
            $this->_logger->notice($msg);
        }
    }

    /**
     * Get the return of the activation process
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}
