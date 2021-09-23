<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;

class Template
{

    const DEFAULT_LOCALE = 'en_us';

    const IMAGE_DIR = 'email';

    public $objectManager = null;

    public $helper = null;

    public $scopeConfig = null;

    public $coreRegistry = null;

    private $templateFilter = null;

    public $variableCollection = null;

    public $transportBuilderDp = null;
    
    public $transportBuilder = null;

    public $storeManager = null;

    public $session  = null;

    public $simpleXml = null;

    public $directory = null;

    public $emailConfig = null;

    private $templateData;

    private $templateVars;

    public $tmpFlag = null;

    /**
     * @var Varmanager|null
     */
    private $varManager = null;

    /**
     * @var array
     */
    private $createdVarsList = [];

    /**
     * @var array
     */
    private $idMap = [];

    /**
     * @var null
     */
    private $template = null;

    public $localeResolver;

    public $localeManager;

    public $resourceConfig;

    public $templateFactory;

    public $cacheManager;

    public $resourceConnection;

    public $productMetadata;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magetrend\Email\Helper\Data $helper,
        \Magetrend\Email\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magetrend\Email\Model\Template\TransportBuilderDp $transportBuilderDp,
        \Magetrend\Email\Model\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Simplexml\Config $simpleXml,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magetrend\Email\Model\Varmanager $varManager,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManagerInterface;
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->variableCollection = $collectionFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->transportBuilderDp = $transportBuilderDp;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManagerInterface;
        $this->session  = $session;
        $this->simpleXml = $simpleXml;
        $this->emailConfig = $emailConfig;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->varManager = $varManager;
        $this->localeResolver = $localeResolver;
        $this->resourceConfig = $resourceConfig;
        $this->templateFactory = $templateFactory;
        $this->cacheManager = $cacheManager;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
    }

    public function createNewBock($template, $newBlockId, $content)
    {
        //copy vars for new block
        $tmpBlockName = explode('block_name="', $content);
        $tmpBlockName = explode('"', $tmpBlockName[1]);
        $blockName = $tmpBlockName[0];

        //prepare new block
        $content = str_replace('block_id=0', 'block_id='.$newBlockId, $content);

        //for not saved content
        $this->setVarTempFlag(true);
        $blockList = [$content];

        //copy private vars from xml data file
        $this->copyDefaultVars($template, $blockList, true, 1, 0);
        //copy global vars from xml data file
        //update var value exits new value
        $this->copyDefaultVars($template, $blockList, false, 1, 1);

        $vars = $this->helper->getDemoVars($template);
        $vars['this'] = $template;
        $templateProcessed = $this->getProcessedContent($template, $content, $vars);

        return $templateProcessed;
    }

    public function saveTemplate($templateId, $templateContent, $vars, $css, $applyToAll)
    {
        $template = $this->coreRegistry->registry('current_email_template');
        $template->setTemplateText($templateContent);
        $template->setTemplateStyles($css);
        $template->save();
        $this->saveVars($vars, $templateId, $applyToAll);
    }

    public function saveVars($vars, $templateId, $applyToAll, $isNew = 0)
    {
        if (count($vars) > 0) {
            foreach ($vars as $blockName => $blockIds) {
                if ($blockIds) {
                    foreach ($blockIds as $blockId => $varList) {
                        $this->saveVarList($blockName, $blockId, $varList, $templateId, $applyToAll, $isNew);
                    }
                }
            }
        }
    }

    public function saveVarList($blockName, $blockId, $varList, $templateId, $applyToAll, $isNew)
    {
        if ($varList) {
            foreach ($varList as $key => $value) {
                if ($isNew) {
                    $this->createVar($blockName, $blockId, $templateId, $key, $value);
                } else {
                    $this->updateVar($blockName, $blockId, $templateId, $key, $value, $applyToAll);
                }
            }
        }
    }

    /**
     * Create new variable
     * It is using for preview vars
     *
     * @param $blockName
     * @param $blockId
     * @param $templateId
     * @param $key
     * @param $value
     * @return bool
     */
    public function createVar($blockName, $blockId, $templateId, $key, $value)
    {
        $template = $this->getTemplate($templateId);
        $storeId = $template->getStoreId();
        $templateCode = $template->getOrigTemplateCode();

        $this->varManager->createVariable([
            'block_name' => $blockName,
            'var_key' => $key,
            'var_value' => $value,
            'global' => 0,
            'block_id' => $blockId,
            'template_id' => $templateId,
            'store_id' => $storeId,
            'template_code' => $templateCode,
            'tmp' => 0,
        ]);

        return true;
    }

    /**
     * Variable update
     * Do not create if it is not exist
     *
     * @param $blockName
     * @param $blockId
     * @param $templateId
     * @param $key
     * @param $value
     * @param int $applyToAll
     * @return bool
     */
    public function updateVar($blockName, $blockId, $templateId, $key, $value, $applyToAll = 0)
    {
        $storeId =  $storeId = $this->getTemplate($templateId)->getStoreId();
        $hash = $this->helper->getHash($key, $blockName, $blockId, $templateId);
        $variable = $this->objectManager->create('Magetrend\Email\Model\Variable')
            ->loadByHash($hash);

        if (!$variable->getId()) {
            return false;
        }

        if ($applyToAll == 1 && $variable->getGlobal() == 1) {
            //update all store variable with same key
            $variableCollection = $this->variableCollection->create()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('var_key', $key);
            if ($variableCollection->getSize() > 0) {
                foreach ($variableCollection as $varItem) {
                    $varItem->setVarValue($value)
                        ->setTmp(0);
                }
                $variableCollection->walk('save');
            }
        } else {
            $variable->setVarValue($value);
            $variable->setTmp(0);
            $variable->save();
        }

        return true;
    }

    public function deleteBlock($templateId, $blockIds)
    {
        if (is_array($blockIds) && count($blockIds) > 0) {
            $this->variableCollection->create()
                ->addFieldToFilter('block_id', ['in' => array_keys($blockIds)])
                ->addFieldToFilter('template_id', $templateId)
                ->walk('delete');
        }

        return true;
    }

    public function getProcessedContent($template, $content, $vars)
    {
        $templateId = $template->getData('orig_template_code');
        $tmpTemplate = $this->objectManager->create('Magento\Email\Model\Template')
            ->load($template->getId());
        $tmpTemplate->setForcedArea($templateId);
        $tmpTemplate->setTemplateText($content);
        return $tmpTemplate->getProcessedTemplate($vars);
    }

    public function getTemplateFilter($template)
    {
        if ($this->templateFilter == null) {
            $this->templateFilter = $template->getTemplateFilter()
                ->setUseSessionInUrl(false)
                ->setPlainTemplateMode(false)
                ->setIsChildTemplate(false)
                ->setDesignParams($template->getDesignParams());

            $this->templateFilter->setUseAbsoluteLinks(true);
        }

        return $this->templateFilter;
    }

    /**
     * It will prepare template for preview
     * @param $template
     * @param $content
     * @param $vars
     * @param $css
     * @return array
     */
    public function preparePreview($template, $content, $vars, $css)
    {
        $tmpTemplate = $this->prepareTemplate($template, $content, $css, $vars);
        $demoVars = $this->helper->getDemoVars($tmpTemplate);
        $contentHtml = $tmpTemplate->getProcessedTemplate($demoVars);
        $this->deleteTemplate($tmpTemplate);
        return [
            'content' => $contentHtml,
            'css' => $css
        ];
    }

    public function preparePreviewTemplate($template, $content, $vars, $css)
    {
        $this->templateData = $template->getData();
        $template->setTemplateText($content);
        $template->setTemplateStyles($css);
        $template->save();
        $this->removePreviewVars();
        $this->saveVars($vars, 0, false);
    }

    /**
     * It will send test email
     *
     * @param $email
     * @param $originTemplate
     * @param $content
     * @param $vars
     * @param $css
     */
    public function sendTestEmail($email, $originTemplate, $content, $vars, $css)
    {
        $storeId = $originTemplate->getStoreId();
        $templateStore = $this->storeManager->getStore($storeId);
        $currentStore = $this->storeManager->getStore();

        $this->storeManager->setCurrentStore($templateStore);
        $template = $this->prepareTemplate($originTemplate, $content, $css, $vars);

        $contentVars = $this->helper->getDemoVars($template);
        $contentVars['template_styles'] = $template->getTemplateStyles();

        if ($this->productMetadata->getVersion() >= '2.3.3') {
            $transportBuilder = $this->transportBuilder;
        } else {
            $transportBuilder = $this->transportBuilderDp;
        }

        $transportBuilder->setTemplateData([
            'template_subject' => $template->getProcessedTemplateSubject($contentVars),
            'template_text' => $template->getProcessedTemplate($contentVars),
            'template_styles' => $template->getTemplateStyles(),
            'template_filter' => $template->getTemplateFilter(),
            'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
            ])
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])->setTemplateVars($contentVars)
            ->setFrom([
                'email' => $this->scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                'name' =>$this->scopeConfig->getValue(
                    'trans_email/ident_general/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            ])->addTo($email);

        $transport = $transportBuilder->getTransport();
        $transport->sendMessage();
        $this->deleteTemplate($template);
        $this->storeManager->setCurrentStore($currentStore);
    }

    /**
     * It will prepare template for preview or for test email
     *
     * @param $origTemplate
     * @param $content
     * @param $css
     * @param $vars
     * @return \Magento\Email\Model\Template
     */
    public function prepareTemplate($origTemplate, $content, $css, $vars)
    {
        $template = $this->getTmpTemplate();
        $templateData = $origTemplate->getData();
        unset($templateData['template_id']);
        unset($templateData['template_code']);
        $template->addData($templateData)
            ->save();

        $this->templateData = $template->getData();
        $template->setTemplateText($content)
            ->setTemplateStyles($css)
            ->setForcedArea($template->getData('orig_template_code'))
            ->save();

        $varCollection = $this->variableCollection->create()
            ->addFieldToFilter('template_id', $template->getId());

        if ($varCollection->getSize() > 0) {
            $varCollection->walk('delete');
        }

        $this->saveVars($vars, $template->getId(), 0, 1);
        return $template;
    }

    public function deleteTemplate($template)
    {
        $this->deleteTemplateVars($template);
        $template->delete();
        return true;
    }

    public function deleteTemplateVars($template)
    {
        $this->variableCollection->create()
            ->addFieldToFilter('template_id', $template->getId())
            ->walk('delete');

        return true;
    }

    public function setVarTempFlag($flag = true)
    {
        $this->coreRegistry->register('mtemail_var_tmp_flag', $flag?1:0);
    }

    public function deleteTmpVariables($template)
    {
        $this->variableCollection->create()
            ->addFieldToFilter('template_id', $template->getId())
            ->addFieldToFilter('tmp', 1)
            ->walk('delete');
    }

    public function assignUniqueIds($content)
    {
        $newBlockId = $this->helper->getUniqueBlockId();
        $this->idMap = [];
        $tmpContent = explode('}}', $content);
        if (count($tmpContent) > 0) {
            $i = 0;
            foreach ($tmpContent as $key => $tmpBlock) {
                if (substr_count($tmpBlock, 'block_id=') == 1) {
                    $tmp1 = explode('block_id=', $tmpBlock);
                    $tmp2 = explode(' ', $tmp1[1]);
                    $newBlockId++;
                    $this->idMap[$tmp2[0]] = $newBlockId;
                    $tmp2[0] = $newBlockId;
                    $tmp2 = implode(' ', $tmp2);
                    $tmpBlock = $tmp1[0]. ' block_id='.$tmp2;
                    $tmpContent[$key] = $tmpBlock;
                    $i++;
                } else {
                    break;
                }
            }
            $content = implode('}}', $tmpContent);
        }
        return $content;
    }

    public function getTemplateList($design = null)
    {
        $list = $this->emailConfig->getAvailableTemplates();
        $mtEmailList = [];
        foreach ($list as $template) {
            if ($template['group'] != 'Magetrend_Email') {
                continue;
            }

            if ($design && strpos($template['value'], 'mt_email_'.$design.'_') === false) {
                continue;
            }

            $mtEmailList[] = $template;
        }

        return $mtEmailList;
    }

    public function createTemplate($templateCode, $name = null, $subject = null, $storeId = 0, $locale = null)
    {
        if ($locale == null) {
            $locale = self::DEFAULT_LOCALE;
        }

        if ($name == null) {
            $name = $templateCode.'_'.$storeId;
        }

        $template = $this->objectManager->create('Magento\Email\Model\BackendTemplate');
        $template->setForcedArea($templateCode);
        $template->loadDefault($templateCode);
        $template->setTemplateCode($name);
        $template->setOrigTemplateCode($templateCode);
        $template->setStoreId($storeId);
        $template->setIsMtEmail(1);
        $template->setIsLegacy(1);
        $template->setLocale($locale);
        $template->setId(null);

        if ($subject != null) {
            $template->setTemplateSubject($subject);
        }

        $template->save();
        $this->prepareNewTemplate($template, $locale);
        return $template;
    }

    public function copyTemplate($templateId, $name = null, $subject = null, $storeId = 0, $locale = null)
    {
        if ($locale == null) {
            $locale = self::DEFAULT_LOCALE;
        }

        if ($name == null) {
            $name = $templateCode.'_'.$storeId;
        }

        $template = $this->templateFactory->create()
            ->load($templateId);

        $blockList = $this->parseBlockList($template->getTemplateText());
        $variableCollection = $this->variableCollection->create()
            ->addFieldToFilter('template_id', $templateId);

        if (!empty($blockList)) {
            $templateText = $template->getTemplateText();
            $templateText = str_replace(array_keys($blockList), array_values($blockList), $templateText);
            $template->setTemplateText($templateText);
        }

        $template->setTemplateCode($name)
            ->setStoreId($storeId)
            ->setTemplateSubject($subject)
            ->setLocale($locale)
            ->setId(null)
            ->save();

        $newTemplateId = $template->getId();

        if ($variableCollection->getSize() > 0) {
            foreach ($variableCollection as $variable) {
                if (isset($blockList[$variable->getBlockId()])) {
                    $variable->setBlockId($blockList[$variable->getBlockId()])
                        ->setId(null)
                        ->setHash(null)
                        ->setStoreId($storeId)
                        ->setTemplateId($newTemplateId);
                }
            }
            $variableCollection->walk('save');
        }
        return $template;
    }

    public function parseBlockList($templateText)
    {
        $blockList = [];
        $templateText = explode('block_id=', $templateText);
        $newBlockId = $this->helper->getUniqueBlockId();
        foreach ($templateText as $block) {
            $block = explode(' ', $block);
            if (is_numeric($block[0])) {
                $blockList[$block[0]] = $newBlockId;
                $newBlockId++;
            }
        }
        return $blockList;
    }

    public function getTemplateBlockNameIdList($content)
    {
        if (substr_count($content, 'block_name="')==0) {
            return false;
        }

        $tmpContent = explode('}}', $content);
        $data = [];
        foreach ($tmpContent as $tmpBlock) {
            if (substr_count($tmpBlock, 'block_name="') == 0 || substr_count($tmpBlock, 'block_id=') == 0) {
                continue;
            }
            $tmpBlock1 = explode('block_name="', $tmpBlock);
            $tmpBlock2 = explode('"', $tmpBlock1[1]);

            $tmpBlock3 = explode('block_id=', $tmpBlock);
            $tmpBlock4 = explode(' ', $tmpBlock3[1]);

            $data[$tmpBlock4[0]] = $tmpBlock2[0];
        }

        if (count($data) == 0) {
            return false;
        }

        return $data;
    }

    /**
     * Returns template object
     * @param $templateId
     * @return null
     */
    public function getTemplate($templateId)
    {
        if ($this->template == null) {
            $template = $this->objectManager->create('Magento\Email\Model\BackendTemplate');
            $template->load($templateId);
            $template->setForcedArea($template->getOrigTemplateCode());
            $this->template  = $template;
        }

        return $this->template;
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Prepare new template and variables
     *
     * @param \Magento\Email\Model\Template $template
     */
    public function prepareNewTemplate($template)
    {
        $newContent = $this->assignUniqueIds($template->getTemplateText());
        $template->setTemplateText($newContent);
        $template->save();

        $blockList = $this->helper->parseBlockList($template);
        //copy private vars from xml data file
        $this->copyDefaultVars($template, $blockList, true, 0, 0);
        //copy global vars from xml data file
        //update var value exits new value
        $this->copyDefaultVars($template, $blockList, false, 0, 1);
    }

    /**
     * Copy data from language file to template
     *
     * @param \Magento\Email\Model\Template $template
     * @param array $blockList
     * @param bool $blockIdFilter
     * @param int $isTmp
     * @param int $global
     * @return bool
     */
    public function copyDefaultVars($template, $blockList, $blockIdFilter = false, $isTmp = 0, $global = 0)
    {
        $templateId = $template->getId();
        $storeId = $template->getStoreId();
        $templateCode = $template->getOrigTemplateCode();
        $defaultVarCollection = $this->varManager->getDefaultVarCollection($template, $global);

        if (count($defaultVarCollection) > 0) {
            foreach ($blockList as $block) {
                $blockData = $this->helper->parseBlockData($block);
                $uniqueBlockId = $blockData['block_id'];
                $blockName = $blockData['block_name'];
                $this->copyDefaultVarCollection(
                    $defaultVarCollection,
                    $blockName,
                    $blockIdFilter,
                    $uniqueBlockId,
                    $templateId,
                    $templateCode,
                    $storeId,
                    $isTmp
                );
            }
        }

        return true;
    }

    /**
     * @param $defaultVarCollection
     * @param $blockName
     * @param $blockIdFilter
     * @param $uniqueBlockId
     * @param $templateId
     * @param $templateCode
     * @param $storeId
     * @param $isTmp
     */
    public function copyDefaultVarCollection(
        $defaultVarCollection,
        $blockName,
        $blockIdFilter,
        $uniqueBlockId,
        $templateId,
        $templateCode,
        $storeId,
        $isTmp
    ) {
        $createdVars = $this->createdVarsList;
        foreach ($defaultVarCollection as $defaultVar) {
            if ($defaultVar->block_name == $blockName) {
                $varKey = (string)$defaultVar->var_key;
                $blockId = (string)$defaultVar->block_id;

                //block ID filter for new template
                if ($blockIdFilter) {
                    if (!isset($this->idMap[$blockId])) {
                        continue;
                    }
                } else {
                    //avoid duplication
                    if (isset($createdVars[$blockName][$varKey])) {
                        continue;
                    }
                }

                $this->varManager->createVariable([
                    'block_name' => $blockName,
                    'var_key' => (string)$defaultVar->var_key,
                    'var_value' => (string)$defaultVar->var_value,
                    'global' => (string)$defaultVar->global,
                    'block_id' => $uniqueBlockId,
                    'template_id' => $templateId,
                    'store_id' => $storeId,
                    'template_code' => $templateCode,
                    'tmp' => $isTmp,
                ]);
            }
        }
        $this->createdVarsList = $createdVars;
    }

    /**
     * Returns temporary template
     *
     * @return \Magento\Email\Model\Template
     */
    public function getTmpTemplate()
    {

        $templateId = $this->getTmpTemplateId();
        $template = $this->templateFactory->create()
            ->load($templateId);

        if (!$template->getId()) {
            $connection = $this->resourceConnection->getConnection('core_write');
            $connection->insert(
                $this->resourceConnection->getTableName('email_template'),
                [
                    'template_id' => $templateId,
                    'template_code' => 'tmp_template_'.$templateId
                ]
            );
            $template->load($templateId);
        }

        return $template;
    }

    /**
     * Returns temporary template reservated id
     *
     * @return int
     */
    public function getTmpTemplateId()
    {
        $reservedTemplateId = $this->scopeConfig->getValue(
            'mtemail/demo/template_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            0
        );

        if (!$reservedTemplateId || empty($reservedTemplateId)) {
            $template = $this->templateFactory->create()
                ->setTemplateCode("tmp_template_for_preview")
                ->save();
            $reservedTemplateId = $template->getId();
            $template->delete();

            $this->resourceConfig->saveConfig(
                'mtemail/demo/template_id',
                $reservedTemplateId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                0
            );
            $this->cacheManager->clean([CacheTypeConfig::CACHE_TAG]);
        }

        return $reservedTemplateId;
    }

}
