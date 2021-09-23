<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magetrend\Email\Model\TemplateManager;

class Mteditor extends \Magento\Backend\Block\Template
{

    const MEDIA_IMAGE_DIR = 'email';

    public $systemStore = null;

    public $locale = null;

    public $localeResolver = null;

    public $emailConfig = null;

    public $coreRegistry = null;

    public $objectManager = null;

    public $templateFilter = null;

    public $helper = null;

    public $readFactory;

    public $templateManager;

    public $templateCollectionFactory;

    public $templateVarManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Config\Model\Config\Source\Locale $locale,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magetrend\Email\Helper\Data $helper,
        \Magetrend\Email\Model\TemplateManager $templateManager,
        \Magento\Framework\Filesystem\Directory\ReadFactory $read,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
        \Magetrend\Email\Model\TemplateVarManager $templateVarManager,
        array $data
    ) {
        $this->systemStore = $systemStore;
        $this->locale = $locale;
        $this->localeResolver = $localeResolver;
        $this->emailConfig = $emailConfig;
        $this->coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->readFactory = $read;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->templateManager = $templateManager;
        $this->templateVarManager = $templateVarManager;
        parent::__construct($context, $data);
    }

    public function getConfig()
    {
        $config = [
            'data' => $this->getTemplateBlockList(),
            'vars' => $this->getVars(),
            'action' => $this->getActions(),
            'formKey' => $this->formKey->getFormKey(),
            'imageList' => $this->getImageList(),
            'template_id' => $this->getTemplateId(),
            'body' => [
                'css' => $this->getTemplateStyle()
            ],
            'contentHelper' => $this->getContentHelpers(),
            'template' => $this->getTemplateConfig(),
            'fontFamilyOptions' => $this->getFontFamilyOptionArray()
        ];
        return $config;
    }

    public function getFontFamilyOptionArray()
    {
        $fonts = $this->_scopeConfig->getValue('mtemail/mteditor/font');
        $fontsArray = explode("\n", $fonts);
        if (count($fontsArray) > 0) {
            foreach ($fontsArray as $key => $value) {
                if (empty($value)) {
                    unset($fontsArray[$key]);
                }
            }
        }
        return $fontsArray;
    }

    public function getTemplateConfig()
    {
        $config = [];
        $template = $this->getEmailTemplate();
        if ($template) {
            $config['code'] = utf8_encode($template->getTemplateCode());
            $config['subject'] = $template->getTemplateSubject();
            $config['store_id'] = $template->getStoreId();
        }
        return $config;
    }

    public function getContentHelpers()
    {
        return [];
    }

    public function getTemplateStyle()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        if (!$template) {
            return '';
        }
        return $template->getTemplateStyles();
    }

    public function getGlobalCss()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        if (!$template) {
            return '';
        }
        return $this->helper->getGlobalCss($template->getStoreId());
    }

    public function getDesignCss()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        if (!$template) {
            return '';
        }

        return $this->templateVarManager->getDesignCss($template);
    }

    public function getEmailHeadBlockHtml()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        $designName = $this->helper->getDesignName($template);
        if (empty($designName)) {
            return '';
        }

        $block = $this->_layout->createBlock(
            \Magento\Framework\View\Element\Template::class
        )
            ->setArea('frontend')
            ->setEditMode(true)
            ->setTemplate('Magetrend_Email::email/'.$designName.'/head.phtml');

        return $block->toHtml();
    }

    public function addPrefixToCss($css, $prefix = '#email')
    {
        if (empty($css)) {
            return '';
        }

        $parts = explode('}', $css);
        foreach ($parts as &$part) {
            if (empty($part)) {
                continue;
            }

            $subParts = explode(',', $part);
            foreach ($subParts as &$subPart) {
                if (strpos($subPart, '@media') !== false) {
                    continue;
                }
                $subPart = $prefix . ' ' . trim($subPart);
            }

            $part = implode(', ', $subParts);
        }

        $prefixedCss = implode("}\n", $parts);
        return $prefixedCss;
    }

    public function getImageList()
    {
        $list = [];
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::MEDIA_IMAGE_DIR
        );
        $baseUrl = $this->getStore()
            ->getBaseUrl('media').self::MEDIA_IMAGE_DIR.'/';
        $fileList = $this->readFactory->create($path)->read();

        if (count($fileList) > 0) {
            foreach ($fileList as $fileName) {
                $extension = explode('.', $fileName);
                if (in_array(strtolower(end($extension)), ['jpg', 'png', 'jpeg', 'gif'])) {
                    $list[] = $baseUrl.$fileName;
                }
            }
        }
        return $list;
    }

    public function getActions()
    {
        return [
            'back' => $this->getUrl("adminhtml/email_template/index/"),
            'createTemplateUrl' => $this->getUrl("*/*/create/"),
            'initTemplateUrl' => $this->getUrl("*/*/template/"),
            'getTemplateCollectionUrl' => $this->getUrl("*/*/getTemplateList/"),
            'uploadUrl' => $this->getUrl("*/*/upload/"),
            'saveUrl' => $this->getUrl("*/*/save/"),
            'preparePreviewAjaxUrl' => $this->getUrl("*/*/preparePreview/"),
            'previewUrl' => $this->getUrl("*/*/preview/", ['id' => '0']),
            'sendTestEmilUrl' => $this->getUrl("*/*/send/"),
            'saveInfo' => $this->getUrl("*/*/saveInfo/"),
            'deleteTemplateAjax' => $this->getUrl("*/*/delete/"),
            'createNewBlock' => $this->getUrl("*/*/newBlock/"),
            'deleteBlock' => $this->getUrl("*/*/deleteBlock/"),
        ];
    }

    public function getTemplateBlockList()
    {
        $template = $this->getDefaultEmailTemplate();
        if (!$template) {
            return false;
        }

        $blockArray = [];
        $vars = $template->getVariablesOptionArray();

        $currentTemplate = $this->coreRegistry->registry('current_email_template');
        $design = $this->helper->getDesignName($currentTemplate);

        if ($vars) {
            foreach ($vars as $var) {
                if (!is_array($var) || !isset($var['label'])) {
                    continue;
                }
                $blockName = (string)$var['label'];
                if (substr_count($blockName, 'mtemail_') == 1) {
                    $blockArray[$blockName] = [
                        'content' => $var['value'],
                        'image' => $this->_assetRepo
                            ->getUrl(
                                'Magetrend_Email::images/theme/'.$design.'/'.str_replace('mtemail_', '', $blockName).'.png'
                            ),
                        'css' => ''
                    ];
                }
            }
        }

        return $blockArray;
    }

    public function getVars()
    {
        $template = $this->getDefaultEmailTemplate();
        if (!$template) {
            return false;
        }

        $varsArray = [];
        $vars = $template->getVariablesOptionArray();

        if ($vars) {
            foreach ($vars as $var) {
                if (!is_array($var) || !isset($var['label'])) {
                    continue;
                }
                if (substr_count($var['label'], 'mtemail_') == 0) {
                    $varsArray[] = $var;
                }
            }
        }

        return $varsArray;
    }

    public function getJsonConfig()
    {
        return json_encode($this->getConfig());
    }

    public function getTemplateContentHtml()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        if (!$template->getId()) {
            return '';
        }
        $template->setForcedArea($template->getOrigTemplateCode());
        $demoVariables = $this->helper->getDemoVars($template);
        return $template->getProcessedTemplate($demoVariables);
    }

    public function getTemplateList()
    {
        return $this->templateManager->getTemplateList();
    }

    public function getDesignList()
    {
        return $this->templateManager->getDesignList();
    }

    public function getLocaleOptions()
    {
        return $this->locale->toOptionArray();
    }

    public function getCurrentLocale()
    {
        return $this->localeResolver->getLocale();
    }

    public function getStoreOptions()
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }

    public function getTemplateId()
    {
        $template = $this->getEmailTemplate();
        if (!$template) {
            return 0;
        }
        return $template->getId();
    }

    public function getEmailTemplate()
    {
        $template = $this->coreRegistry->registry('current_email_template');
        if (!$template || !$template->getId()) {
            return false;
        }

        return $template;
    }

    public function getDefaultEmailTemplate()
    {
        $template = $this->getEmailTemplate();
        if (!$template || !$template->getId()) {
            return false;
        }

        $templateId = $template->getData('orig_template_code');
        $defaultTemplate = $this->objectManager->create('Magento\Email\Model\Template');
        $defaultTemplate->setForcedArea($templateId);
        $defaultTemplate->loadDefault($templateId);
        return $defaultTemplate;
    }

    public function getTemplateFilter()
    {
        if ($this->templateFilter == null) {
            $this->templateFilter =  $this->objectManager->create('Magento\Email\Model\Template\Filter');
            $this->templateFilter->setUseAbsoluteLinks(true);
        }
        return $this->templateFilter;
    }

    public function getStore()
    {
        $template = $this->getEmailTemplate();
        if (!$template || !$template->getId()) {
            return $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        }

        return $this->objectManager
            ->get('Magento\Store\Model\StoreManagerInterface')->getStore($template->getStoreId());
    }
}
