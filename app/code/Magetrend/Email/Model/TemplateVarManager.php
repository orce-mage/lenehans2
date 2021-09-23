<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

class TemplateVarManager extends \Magento\Framework\DataObject
{
    private $vars = [];

    private $isCollected = false;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    public $eventManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    public $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    public $fileDriver;

    /**
     * @var \Magento\Framework\View\LayoutInterfaceFactory
     */
    public $layout;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;


    /**
     * TemplateVarManager constructor.
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\View\LayoutInterfaceFactory $layout
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\View\LayoutInterfaceFactory $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Json\Helper\Data $jsonHelper

    ) {
        $this->eventManager = $eventManager;
        $this->readFactory = $readFactory;
        $this->moduleReader = $moduleReader;
        $this->fileDriver = $fileDriver;
        $this->layout = $layout;
        $this->scopeConfig = $scopeConfigInterface;
        $this->jsonHelper = $jsonHelper;
    }

    public function reset()
    {
        $this->vars = [];
        $this->setData([]);
    }

    public function setVariables($vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function getData($key = '', $index = null)
    {
        if ($this->isCollected === false) {
            $this->collect();
            $this->isCollected = true;
        }

        return parent::getData($key, $index);
    }

    public function collect()
    {
        $this->eventManager->dispatch('magetrend_email_collect_additional_vars', [
            'vars' => $this->vars,
            'additional_vars' => $this,
        ]);
    }

    public function getDesignCss($template)
    {
        $origTemplateCode = $template->getOrigTemplateCode();
        if (strpos($origTemplateCode, 'mt_email_') === false) {
            return '';
        }

        $theme = explode('_', $origTemplateCode);
        $theme = $theme[2];

        $cssPath = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Magetrend_Email'
        ).'/frontend/web/css/email/';

        if (!$this->fileDriver->isExists($cssPath.$theme.'.css')) {
            return '';
        }
         $fileContent = $this->readFactory->create($cssPath)
             ->readFile($theme.'.css');

        return $fileContent;
    }

    public function getDesignHeadHtml($template)
    {
        $designName = $this->getDesignName($template);
        if (empty($designName)) {
            return '';
        }

        $block = $this->layout->create()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            'email_head'.rand(0, 9999).'_'.microtime()
        )
            ->setArea('frontend')
            ->setEditMode(false)
            ->setTemplate('Magetrend_Email::email/'.$designName.'/head.phtml');

        return $block->toHtml();
    }

    public function getDesignName($template)
    {
        if (!$template || !$template->getId()) {
            return '';
        }

        $origTemplateCode = $template->getOrigTemplateCode();
        if (strpos($origTemplateCode, 'mt_email_') === false) {
            return '';
        }

        $theme = explode('_', $origTemplateCode);
        $theme = $theme[2];

        return $theme;
    }

    public function getTrackingLink($shipment)
    {
        foreach ($shipment->getAllTracks() as $item) {
            return $this->getTrackinkLinkByItem($item);
        }
    }

    public function getTrackinkLinkByItem($item)
    {
        $link = $this->getTrackingLinkByShippingMethod($item->getCarrierCode());

        if (empty($link)) {
            return $item->getNumber();
        }

        $trackurl = str_replace('{code}', $item->getNumber(), $link);
        return $trackurl;
    }

    public function getTrackingCode($shipment)
    {
        foreach ($shipment->getAllTracks() as $item) {
            return $item->getNumber();
        }
    }

    public function getTrack($shipment)
    {
        foreach ($shipment->getAllTracks() as $item) {
            return $item;
        }
    }

    public function getTrackingLinkByShippingMethod($shippingMethodCode, $store = null)
    {
        $trackingLinks = $this->scopeConfig->getValue(
            \Magetrend\Email\Helper\Data::XML_PATH_EMAIL_SHIPPING_TRACKING_LINK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if (empty($trackingLinks)) {
            return '';
        }

        $trackingLinks = $this->jsonHelper->jsonDecode($trackingLinks);
        if (empty($trackingLinks)) {
            return '';
        }

        foreach ($trackingLinks as $track) {
            if ($track['shipping_method'] == $shippingMethodCode) {
                return $track['track'];
            }
        }

        return '';
    }

}
