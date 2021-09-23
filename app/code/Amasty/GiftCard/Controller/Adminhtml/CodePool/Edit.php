<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Amasty\GiftCard\Model\CodePool\CodePoolRule;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Edit extends AbstractCodePool
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->registry = $registry;
    }

    public function execute()
    {
        $title = __('New Code Pool');

        if ($codePoolId = (int)$this->getRequest()->getParam(CodePoolInterface::CODE_POOL_ID)) {
            try {
                $model = $this->repository->getById($codePoolId);
                $ruleModel = $model->getCodePoolRule();

                $title = __('Edit Code Pool %1', $model->getTitle());
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Code Pool no longer exists.'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $ruleModel = $this->repository->getEmptyRuleModel();
        }
        $this->registry->register(CodePoolRule::CURRENT_RULE, $ruleModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GiftCard::giftcard_code');
        $resultPage->addBreadcrumb(__('Code Pool'), __('Code Pool'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
