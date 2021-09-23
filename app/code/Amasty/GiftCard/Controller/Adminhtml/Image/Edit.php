<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractImage
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
        $title = __('New Gift Image');

        if ($imageId = (int)$this->getRequest()->getParam(ImageInterface::IMAGE_ID)) {
            try {
                $model = $this->repository->getById($imageId);
                $title = __('Edit Gift Image %1', $model->getTitle());
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Image no longer exists.'));

                return $this->_redirect('*/*/index');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GiftCard::giftcard_image');
        $resultPage->addBreadcrumb(__('Gift Card Images'), __('Gift Card Images'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
