<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;

class ReadHandler
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CreditmemoExtensionFactory
     */
    private $creditmemoExtensionFactory;

    public function __construct(
        Repository $repository,
        CreditmemoExtensionFactory $creditmemoExtensionFactory
    ) {
        $this->repository = $repository;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     */
    public function loadAttributes(CreditmemoInterface $creditmemo): CreditmemoInterface
    {
        $extension = $creditmemo->getExtensionAttributes();

        if ($extension === null) {
            $extension = $this->creditmemoExtensionFactory->create();
        } elseif ($creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo() !== null) {
            return $creditmemo;
        }
        $creditmemoId = (int)$creditmemo->getId();

        try {
            $giftCardMemo = $this->repository->getByCreditmemoId($creditmemoId);
        } catch (NoSuchEntityException $e) {
            $giftCardMemo = $this->repository->getEmptyCreditmemoModel();
            $giftCardMemo->setCreditmemoId($creditmemoId);
        }
        $extension->setAmGiftcardCreditmemo($giftCardMemo);
        $creditmemo->setExtensionAttributes($extension);

        return $creditmemo;
    }
}
