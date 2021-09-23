<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Controller\Adminhtml\Theme;

class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \MageBig\MbFrame\Setup\Model\Page
     */
    protected $pageSetup;

    /**
     * @var \MageBig\MbFrame\Setup\Model\Block
     */
    protected $blockSetup;

    /**
     * @var \MageBig\MbFrame\Setup\Model\Widget
     */
    protected $widgetSetup;

    /**
     * Import constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \MageBig\MbFrame\Setup\Model\Page   $pageSetup
     * @param \MageBig\MbFrame\Setup\Model\Block  $blockSetup
     * @param \MageBig\MbFrame\Setup\Model\Widget $widgetSetup
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageBig\MbFrame\Setup\Model\Page $pageSetup,
        \MageBig\MbFrame\Setup\Model\Block $blockSetup,
        \MageBig\MbFrame\Setup\Model\Widget $widgetSetup
    ) {
        parent::__construct($context);
        $this->pageSetup   = $pageSetup;
        $this->blockSetup  = $blockSetup;
        $this->widgetSetup = $widgetSetup;
    }

    public function execute()
    {
        $override = $this->getRequest()->getParam('override');
        $this->pageSetup->install($override);
        $this->blockSetup->install($override);
        $this->widgetSetup->install($override);
        $this->messageManager->addSuccessMessage(__('Import Success.'));

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setRefererUrl();
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
