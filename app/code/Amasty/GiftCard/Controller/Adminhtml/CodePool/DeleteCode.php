<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\Code\Repository;
use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class DeleteCode extends AbstractCodePool
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Action\Context $context,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($codeId = (int)$this->getRequest()->getParam(CodeInterface::CODE_ID)) {
            try {
                $this->repository->deleteById($codeId);
            } catch (LocalizedException $e) {
                $response = ['message' => $e->getMessage(), 'error' => true];
            }
        }

        return $result->setData(['message' => 'Code has been successfully deleted.', 'error' => false]);
    }
}
