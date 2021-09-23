<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Preview;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\GiftCard\EmailPreviewProcessor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class GetPreviewContent extends Action
{
    const CUSTOM_IMAGE_INPUT = 'amgiftcard-userimage-input';

    /**
     * @var EmailPreviewProcessor
     */
    private $emailPreviewProcessor;

    public function __construct(
        Context $context,
        EmailPreviewProcessor $emailPreviewProcessor
    ) {
        parent::__construct($context);
        $this->emailPreviewProcessor = $emailPreviewProcessor;
    }

    public function execute()
    {
        $result = '';
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $data = $this->getRequest()->getParams();

        $file = $this->getRequest()->getFiles(self::CUSTOM_IMAGE_INPUT);

        if ($file) {
            $data[GiftCardOptionInterface::CUSTOM_IMAGE] = $file;
        }

        try {
            $result = $this->emailPreviewProcessor->process($data);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRaw->setContents($result);
    }
}
