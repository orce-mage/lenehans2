<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Adminhtml\Account;

use Amasty\GiftCardAccount\Controller\Adminhtml\AbstractAccount;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountsGenerator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GenerateAccounts extends AbstractAccount
{
    /**
     * @var GiftCardAccountsGenerator
     */
    private $accountsGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        GiftCardAccountsGenerator $accountsGenerator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->accountsGenerator = $accountsGenerator;
        $this->logger = $logger;
    }

    public function execute()
    {
        $qty = (int)$this->getRequest()->getParam('qty');

        try {
            $accountsFormData = new DataObject($this->getRequest()->getPostValue());
            $this->accountsGenerator->generate($accountsFormData, $qty);

            $result = [
                'isError' => false,
                'message' => __('A total of %1 Gift Card Account(s) were successfully generated.', $qty)->render()
            ];
        } catch (LocalizedException $e) {
            $result = ['isError' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $result = [
                'isError' => true,
                'message' => __(
                    'Something went wrong while generating accounts. Please review the error log.'
                )->render()
            ];
            $this->logger->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
