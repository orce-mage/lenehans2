<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Product\Attribute;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetByOptionIdAndStoreIdInterface;
use Amasty\Stockstatus\Controller\Adminhtml\Product\Attribute\Settings\Save;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\Store;

class Settings extends Action
{
    const ERROR_MESSAGES_PARAM = 'error_messages';

    /**
     * @var GetByOptionIdAndStoreIdInterface
     */
    private $getByOptionIdAndStoreId;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Context $context,
        GetByOptionIdAndStoreIdInterface $getByOptionIdAndStoreId,
        Escaper $escaper
    ) {
        $this->getByOptionIdAndStoreId = $getByOptionIdAndStoreId;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var Layout $page **/
        $page = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->showMessages();

        //the provided solution is used due to Magento 2.4 bug associated with broken store switcher template
        if (!$this->isSettingExistsForOption()) {
            $page->getLayout()->unsetElement('store_switcher');
        }

        return $page;
    }

    public function isSettingExistsForOption(): bool
    {
        $optionId = (int)$this->getRequest()->getParam(StockstatusSettingsInterface::OPTION_ID);
        $stockStatusSetting = $this->getByOptionIdAndStoreId->execute($optionId, Store::DEFAULT_STORE_ID);

        return $stockStatusSetting->getId() !== null;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Save::ADMIN_RESOURCE);
    }

    private function showMessages(): void
    {
        $errorMessages = (array)$this->getRequest()->getParam(self::ERROR_MESSAGES_PARAM, []);

        foreach ($errorMessages as $message) {
            $this->messageManager->addErrorMessage($this->escaper->escapeHtml($message));
        }
    }
}
