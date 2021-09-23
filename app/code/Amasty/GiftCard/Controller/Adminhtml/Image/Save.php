<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends AbstractImage
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Action\Context $context,
        Repository $repository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $id = (int)$this->getRequest()->getParam(ImageInterface::IMAGE_ID);
            try {
                if ($id) {
                    $model = $this->repository->getById($id);
                } else {
                    $model = $this->repository->getEmptyImageModel();
                }
                $this->saveImage($model, $data);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('amgcard/*/edit', [ImageInterface::IMAGE_ID => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->saveFormDataAndRedirect($data, $id);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the image data. Please review the error log.')
                );

                return $this->saveFormDataAndRedirect($data, $id);
            }
        }

        return $this->_redirect('amgcard/*/');
    }

    /**
     * @param ImageInterface $model
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveImage(ImageInterface $model, array $data)
    {
        if (isset($data['image'][0])) {
            $data[ImageInterface::IMAGE_PATH] = $data['image'][0]['name'];
            unset($data['image']);
        }

        if (isset($data['baking_data'])) {
            $bakingInfo = [];

            foreach ($data['baking_data'] as $name => $bakingData) {
                $bakingInfoModel = $this->repository->getEmptyImageBakingInfoModel();
                $bakingInfoModel->setData($bakingData);
                $bakingInfoModel->setName($name);
                $bakingInfo[$name] = $bakingInfoModel;
            }
            $model->setBakingInfo($bakingInfo);
        }

        $model->addData($data);
        $model->setIsUserUpload(false);
        $this->repository->save($model);

        $this->messageManager->addSuccessMessage('The Image has been saved.');
        $this->dataPersistor->clear(\Amasty\GiftCard\Model\Image\Image::DATA_PERSISTOR_KEY);
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function saveFormDataAndRedirect(array $data, int $id)
    {
        $this->dataPersistor->set(\Amasty\GiftCard\Model\Image\Image::DATA_PERSISTOR_KEY, $data);
        if (!empty($id)) {
            return $this->_redirect('amgcard/*/edit', [ImageInterface::IMAGE_ID => $id]);
        } else {
            return $this->_redirect('amgcard/*/create');
        }
    }
}
