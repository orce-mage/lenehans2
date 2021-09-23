<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Controller\Adminhtml\Config;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \MageBig\MbFrame\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $themeModel;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Design\Collection
     */
    protected $collectionDesign;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \MageBig\MbFrame\Model\Config\Structure              $configStructure
     * @param \MageBig\MbFrame\Model\Config                        $backendConfig
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Theme\Model\Theme                           $themeModel
     * @param \Magento\Config\Model\Config                         $config
     * @param \Magento\Theme\Model\ResourceModel\Design\Collection $collectionDesign
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageBig\MbFrame\Model\Config\Structure $configStructure,
        \MageBig\MbFrame\Model\Config $backendConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Theme\Model\Theme $themeModel,
        \Magento\Config\Model\Config $config,
        \Magento\Theme\Model\ResourceModel\Design\Collection $collectionDesign
    ) {
        parent::__construct($context);
        $this->_configStructure  = $configStructure;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry          = $registry;
        $this->themeModel        = $themeModel;
        $this->config            = $config;
        $this->collectionDesign  = $collectionDesign;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');
        $code    = $this->getRequest()->getParam('code');
        $theme   = $this->getRequest()->getParam('theme_id');

        $currentThemeId = $this->getCurrentThemeId();
        if ($theme != $currentThemeId) {
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath(
                'mbframe/config/edit',
                [
                    'theme_id' => $currentThemeId,
                    'code'     => $code,
                    'section'  => $section,
                    'website'  => $website,
                    'store'    => $store,
                ]
            );
        }
        $resultPage = $this->resultPageFactory->create();

        if ($theme) {
            $this->themeModel->load($theme);
            $resultPage->getConfig()->getTitle()->prepend(__($this->themeModel->getThemeTitle()));
            $layout = $resultPage->getLayout();
            if (strpos($this->themeModel->getCode(), 'MageBig') === 0) {
                $layout->addBlock('MageBig\MbFrame\Block\Adminhtml\Config\Tabs', 'adminhtml.system.config.tabs', 'left');
                $layout->addBlock('MageBig\MbFrame\Block\Adminhtml\Config\Edit', 'adminhtml.system.config.edit', 'content');
            }
        }

        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$section]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('MageBig_MbFrame::magebig_config');

        return $resultPage;
    }

    public function getCurrentThemeId()
    {
        $path    = 'design/theme/theme_id';
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');
        $this->config->setData([
            'website' => $website,
            'store'   => $store,
        ]);

        if ($store) {
            $this->collectionDesign->addFieldToFilter('store_id', $store);
            $design         = $this->collectionDesign->getFirstItem();
            $currentThemeId = $design->getDesign();
            if (!$currentThemeId) {
                $currentThemeId = $this->config->getConfigDataValue($path);
            }
        } else {
            $currentThemeId = $this->config->getConfigDataValue($path);
        }

        return $currentThemeId;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_MbFrame::themes_config');
    }
}
