<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

class Varmanager
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    public $objectManager = null;

    /**
     * @var \Magento\Framework\Registry|null
     */
    public $coreRegistry = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    public $storeManager = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|null
     */
    public $scopeConfig = null;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var ResourceModel\Variable\CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Magetrend\Email\Helper\Data|null
     */
    public $helper = null;

    /**
     * @var null
     */
    private $varCollection = null;

    /**
     * @var null
     */
    private $globalVarCollection = null;

    /**
     * @var array
     */
    private $template = [];

    /**
     * @var null
     */
    private $store = null;

    /**
     * @var int|null
     */
    private $templateId = null;

    /**
     * @var int|null
     */
    private $blockId = null;

    /**
     * @var string|null
     */
    private $blockName = null;

    /**
     * @var bool
     */
    private $editFlag = false;

    /**
     * @var null
     */
    private $defaultVariableCollection = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    public $directory = null;

    /**
     * @var \Magento\Framework\Simplexml\Config|null
     */
    public $simpleXml = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * Loaded template array
     * @var array
     */
    public $loadedTemplates = [];

    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    public $localeManager;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    public $localeResolver;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * @var int
     */
    private $currentCollectionTemplateId = 0;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $assetRepo;

    /**
     * Varmanager constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Framework\Registry $coreRegistry
     * @param ResourceModel\Variable\CollectionFactory $collectionFactory
     * @param \Magetrend\Email\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Simplexml\Config $simpleXml
     * @param \Magento\Backend\Model\Locale\Manager $localeManager
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\View\Asset\Repository $repository
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\Email\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magetrend\Email\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Simplexml\Config $simpleXml,
        \Magento\Backend\Model\Locale\Manager $localeManager,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\View\Asset\Repository $repository
    ) {
        $this->objectManager = $objectManagerInterface;
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManagerInterface;
        $this->scopeConfig = $scopeConfigInterface;
        $this->filesystem = $filesystem;
        $this->readFactory = $readFactory;
        $this->simpleXml = $simpleXml;
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->localeManager = $localeManager;
        $this->localeResolver = $localeResolver;
        $this->io = $io;
        $this->assetRepo = $repository;
        if ($coreRegistry->registry('mt_editor_edit_mode') == 1) {
            $this->editFlag = true;
        }
    }

    /**
     * Set Template ID
     * @param $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * Set Block ID
     * @param $blockId
     */
    public function setBlockId($blockId)
    {
        $this->blockId = $blockId;
    }

    /**
     * Set Block Name
     * @param $blockName
     */
    public function setBlockName($blockName)
    {
        $this->blockName = $blockName;
    }

    /**
     * Returns Block ID
     * @return int|null
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Returns Block Name
     * @return null|string
     */
    public function getBlockName()
    {
        return $this->blockName;
    }

    /**
     * Returns Template ID
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Returns Template Store ID
     * @return mixed
     */
    protected function getStoreId()
    {
        return $this->getTemplate()->getStoreId();
    }

    /**
     * Returns Template Object
     * @return mixed|null
     */
    public function getTemplate()
    {
        $templateId = $this->getTemplateId();
        if (!isset($this->template[$templateId])) {
            $template = $this->objectManager->create('Magento\Email\Model\Template');
            $template->load($templateId);
            $template->setForcedArea($template->getOrigTemplateCode());
            $this->template[$templateId] = $template;
        }
        return $this->template[$templateId];
    }

    /**
     * Returns html attribute
     * $attribute html attribute
     * $key unique key
     * $default default value
     * $global value global or local
     */
    public function getHtmlAttribute($attribute, $key, $default = '', $global = true)
    {
        $value = $this->getValue($key, $default, $global);
        $result = ' '.$attribute.'="'.$value.'" ';
        if ($this->editFlag) {
            $result .= ' data-var-'.$attribute.'="'.$key.'"';
        }

        return $result;
    }

    /**
     * Retruns contenteditable attribute
     * @param $key
     * @return string
     */
    public function getTextEditAttribute($key)
    {
        $result = '';
        if ($this->editFlag) {
            $result = ' contenteditable="true" data-var-text="'.$key.'"';
        }
        return $result;
    }

    /**
     * Returns variable value
     * @param $key
     * @param string $default
     * @param bool $global
     * @return mixed
     */
    public function getValue($key, $default = '', $global = true)
    {
        return $this->getVar($key, $default, $global)->getVarValue();
    }

    /**
     * Returns variable object
     * @param $key
     * @param $default
     * @param $global
     * @return mixed
     */
    public function getVar($key, $default, $global)
    {
        $this->loadVarCollection($this->getTemplateId());
        $hash = $this->helper->getHash($key, $this->getBlockName(), $this->getBlockId(), $this->getTemplateId());

        if (!isset($this->varCollection[$hash])) {
            $this->varCollection[$hash] = $this->createNewVariable($key, $default, $global);
        } else {
            $this->varCollection[$hash] = $this->updateVariable($this->varCollection[$hash], $global);
        }
        return $this->varCollection[$hash];
    }

    /**
     * Create new variable
     * Change value if global variable with the same key exist.
     * @param $key
     * @param $default
     * @param $global
     * @return mixed
     */
    public function createNewVariable($key, $default, $global)
    {
        $template = $this->getTemplate();
        $templateId = $template->getId();
        $storeId = $template->getStoreId();
        $origTemplateCode = $template->getOrigTemplateCode();

        $defaultVarValue  = $this->getDefaultValue($key, $default);

        if ($global && $defaultVarValue == $default) {
            $globalDefault = $this->getGlobalVarValue($key, $templateId, $storeId);
            if ($globalDefault) {
                $default = $globalDefault;
            }
        } else {
            $default = $defaultVarValue;
        }

        $newVariable = $this->objectManager->create('Magetrend\Email\Model\Variable');
        $newVariable->setTemplateId($templateId)
            ->setBlockId($this->getBlockId())
            ->setBlockName($this->getBlockName())
            ->setGlobal($global?1:0)
            ->setVarKey($key)
            ->setVarValue($default)
            ->setStoreId($storeId)
            ->setTemplateCode($origTemplateCode)
            ->save();

        return $newVariable;
    }

    /**
     * Update variable information
     * @param $var
     * @param $global
     * @return mixed
     */
    public function updateVariable($var, $global)
    {
        //update var if there are changes
        $varIsGlobal = $var->getGlobal()?true:false;
        if ($varIsGlobal != $global) {
            $var->setGlobal($global?1:0);
            $var->save();
        }
        return $var;
    }

    public function getValueFromConfig($key)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->getDefaultValue($key, '', $storeId);
    }

    public function getDefaultValue($key, $default = '', $storeId = null)
    {
        //check global variable
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if ($default == '') {
            switch ($key) {
                case 'logo_src':
                    return $this->getLogoUrl($storeId);
                case 'logo_href':
                    return $this->getStore($storeId)->getBaseUrl();
                case 'logo_alt':
                    return $this->getLogoAlt($storeId);
                case 'store':
                    return $this->getStore($storeId);
            }
        }

        return $default;
    }

    public function loadVarCollection($templateId)
    {
        if ($this->varCollection == null || $this->currentCollectionTemplateId != $templateId) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('template_id', $templateId);
            $this->varCollection = [];
            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $this->varCollection[$item->getHash()] = $item;
                }
            }
            $this->currentCollectionTemplateId = $templateId;
        }
    }

    protected function getLogoUrl($storeId)
    {
        $fileName = $this->scopeConfig->getValue(
            \Magento\Email\Model\AbstractTemplate::XML_PATH_DESIGN_EMAIL_LOGO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($fileName) {
            $uploadDir = \Magento\Config\Model\Config\Backend\Email\Logo::UPLOAD_DIR;
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            if ($mediaDirectory->isFile($uploadDir . '/' . $fileName)) {
                return $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $uploadDir . '/' . $fileName;
            }
        }

        return $this->objectManager->get('Magento\Email\Model\Template')->getDefaultEmailLogo();
    }

    public function getLogoAlt($storeId)
    {
        $alt = $this->scopeConfig->getValue(
            \Magento\Email\Model\AbstractTemplate::XML_PATH_DESIGN_EMAIL_LOGO_ALT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($alt) {
            return $alt;
        }

        $store = $this->storeManager->getStore($storeId);
        return $store->getFrontendName();
    }

    public function getStore()
    {
        if ($this->store == null) {
            $template = $this->getTemplate();
            $this->store = $this->storeManager->getStore($template->getStoreId());
        }

        return $this->store;
    }

    /**
     * Returns variables from .xml file
     *
     * @param $template
     * @param int $global
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultVarCollection($template, $global = 0)
    {
        $origTemplateCode = $global?'global':$template->getOrigTemplateCode();
        $designName = $this->helper->getDesignName($template);
        $origTemplateCode = str_replace('mt_email_'.$designName.'_', 'mt_email_default_', $origTemplateCode);

        if ($this->defaultVariableCollection == null) {
            $defaultVariableCollection = [];
            $locale = $template->getLocale()?$template->getLocale():'en_us';

            $path = $this->assetRepo->createAsset('Magetrend_Email::data/email/en_us.xml')->getSourceFile();
            $path = str_replace('en_us.xml', '', $path);
            $fileName = strtolower($locale).'.xml';
            $fullPathToFile = $path.$fileName;

            if (!$this->readFactory->create($path)->isExist($fileName)) {
                $fileName = 'en_us.xml';
            }

            $this->io->open(['path'=> $path]);
            $xmlData = $this->io->read($fileName);

            if (empty($xmlData)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Unable to read template data. Please make sure the file exists and has read permissions: %1',
                        $path . $fileName
                    )
                );
            }

            $this->simpleXml->loadString($xmlData);
            if (!$this->simpleXml->getNode()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Unable to read template data. Please make sure the file exists and has read permissions: %1',
                        $path . $fileName
                    )
                );
            }

            $defaults = $this->simpleXml->getNode()->row;
            foreach ($defaults as $row) {
                $templateCode =  (string)$row->global?'global':(string)$row->template_code;
                $defaultVariableCollection[$templateCode][(string)$row->global][] = $row;
            }
            $this->defaultVariableCollection = $defaultVariableCollection;
        }

        if (!isset($this->defaultVariableCollection[$origTemplateCode][$global])) {
            return [];
        }

        return $this->defaultVariableCollection[$origTemplateCode][$global];
    }

    /**
     * Create new variable
     * if variable is global try to find new value
     *
     * @param  array $data (full variable data)
     */
    public function createVariable(array $data)
    {
        $data['var_value'] = $this->prepareValue($data['var_value'], $data['template_id']);
        if ($data['global'] == 1) {
            $globalValue = $this->getGlobalVarValue($data['var_key'], $data['template_id'], $data['store_id']);
            if ($globalValue) {
                $data['var_value'] = $globalValue;
            } else {
                if (empty($data['var_value'])) {
                    $data['var_value'] = $this->getDefaultValue($data['var_key'], "", $data['store_id']);
                }
            }
        }

        $newVar = $this->objectManager->create('Magetrend\Email\Model\Variable');
        $newVar->setData($data)
            ->save();
    }

    public function prepareValue($content, $templateId, $locale = '')
    {
        if (substr_count($content, '{{trans') == 0) {
            return $content;
        }

        if ($locale == '') {
            $template = $this->getTemplateById($templateId);
            if (!$template->getLocale()) {
                return $content;
            }
            $locale = $template->getLocale();
        }

        /**
         * Change locale for translation
         */
        $currentLocale = $this->localeResolver->getLocale();
        $localeSwitched = false;
        if (!$currentLocale != $locale) {
            $this->localeManager->switchBackendInterfaceLocale($locale);
            $localeSwitched = true;
        }

        preg_match_all('/{{trans (.*?)}}/s', $content, $matches);
        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $match) {
                $matchType = $this->getMatchType($match);

                if ($matchType == 'double_quote') {
                    preg_match('/"(.*?)"/s', $match, $translatePart);
                } else {
                    preg_match("/'(.*?)'/s", $match, $translatePart);
                }

                if (!isset($translatePart[0])) {
                    continue;
                }
                $variablePart = str_replace($translatePart[0], '', $match);
                $variablePart = explode(' ', $variablePart);
                $varData = [];
                if (isset($variablePart[0])) {
                    foreach ($variablePart as $variable) {
                        if (empty($variable)) {
                            continue;
                        }
                        $variable = str_replace(' ', '', $variable);
                        $variable = explode('=', $variable);
                        if (!isset($variable['1'])) {
                            continue;
                        }
                        $varData[$variable[0]] = $this->prepareStringValue($variable[1]);
                    }
                }
                $matches[1][$key] = (string)__($translatePart[1], $varData);
            }
            foreach ($matches[0] as $key => $match) {
                $content = str_replace($match, $matches[1][$key], $content);
            }
        }
        /**
         * Reset Locale
         */
        if ($localeSwitched) {
            $this->localeManager->switchBackendInterfaceLocale($currentLocale);
        }
        return $content;
    }

    public function prepareStringValue($value)
    {
        if (substr($value, 0, 1) == '"') {
            $value = ltrim($value, '"');
            $value = str_replace([' |raw', '|raw'], '', $value);
            $value = rtrim($value, '"');
        }

        if (substr($value, 0, 1) == '$') {
            $value = ltrim($value, '$');
        }

        $value = '{{var ' . $value . '}}';
        return $value;
    }

    public function getMatchType($content)
    {
        $pos1 = strpos($content, '"');
        $pos2 = strpos($content, "'");

        if ((!$pos2 && $pos2 !== 0) || $pos1 < $pos2) {
            return 'double_quote';
        }
        return 'single_quote';
    }

    /**
     * Return global variable value
     * @param $key
     * @param $templateId
     * @param $storeId
     * @return bool
     */
    public function getGlobalVarValue($key, $templateId, $storeId)
    {
        $varCollection = $this->loadGlobalVarCollection($storeId, $templateId);
        if (!isset($varCollection[$key])) {
            return false;
        }
        return $varCollection[$key]->getVarValue();
    }

    /**
     * Returns globals variables collection
     * @param $storeId
     * @param null $ignoreTemplateId
     * @return array|null
     */
    public function loadGlobalVarCollection($storeId, $ignoreTemplateId = null)
    {
        if ($this->globalVarCollection == null) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('global', 1)
                ->addFieldToFilter('store_id', $storeId);
            if ($ignoreTemplateId != null) {
                $collection->addFieldToFilter('template_id', ['neq' => $ignoreTemplateId]);
            }
            $this->globalVarCollection = [];
            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    if (isset($this->globalVarCollection[$item->getVarKey()])) {
                        continue;
                    }
                    $this->globalVarCollection[$item->getVarKey()] = $item;
                }
            }
        }

        return $this->globalVarCollection;
    }

    /**
     * Returns template by id
     * @param $templateId
     * @return mixed
     */
    public function getTemplateById($templateId)
    {
        if (!isset($this->loadedTemplates[$templateId])) {
            $template = $this->objectManager->create('Magento\Email\Model\BackendTemplate');
            $template->load($templateId);
            $template->setForcedArea($template->getOrigTemplateCode());
            $this->loadedTemplates[$templateId] = $template;
        }
        return $this->loadedTemplates[$templateId];
    }
}
