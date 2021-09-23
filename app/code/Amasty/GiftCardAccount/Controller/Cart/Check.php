<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Cart;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

class Check extends \Magento\Framework\App\Action\Action
{
    const GIFTCARD_REQUEST_KEY = 'amgiftcard';

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardAccountFormatter
     */
    private $accountFormatter;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    public function __construct(
        Context $context,
        Repository $accountRepository,
        LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Amasty\GiftCardAccount\Model\GiftCardAccountFormatter $accountFormatter
    ) {
        parent::__construct($context);
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
        $this->accountFormatter = $accountFormatter;
        $this->serializer = $serializer;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result = '';

        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new NotFoundException(__('Invalid Request'));
        }
        try {
            $account = $this->accountRepository->getByCode(
                trim($this->getRequest()->getParam(self::GIFTCARD_REQUEST_KEY, ''))
            );
            $result = $this->serializer->serialize($this->accountFormatter->getFormattedData($account));
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $resultJson->setData($result);
    }
}
