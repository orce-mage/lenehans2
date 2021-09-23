<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Repository;
use Magento\Sales\Api\Data\CreditmemoInterface;

class SaveHandler
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function saveAttributes(CreditmemoInterface $creditmemo): CreditmemoInterface
    {
        if (!$creditmemo->getExtensionAttributes()
            || !$creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo()
        ) {
            return $creditmemo;
        }
        $gCardMemo = $creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo();

        if ($gCardMemo->getCreditmemoId() && $gCardMemo->getGiftAmount() > 0) {
            $this->repository->save($gCardMemo);
        }

        return $creditmemo;
    }
}
