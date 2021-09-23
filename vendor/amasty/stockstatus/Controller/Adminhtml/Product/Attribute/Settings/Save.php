<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Product\Attribute\Settings;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\RemoveStockstatusSettingsIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\UploadStockstatusIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::advanced';

    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UploadStockstatusIconFileInterface
     */
    private $uploadStockstatusIconFile;

    /**
     * @var RemoveStockstatusSettingsIconFileInterface
     */
    private $removeStockstatusSettingsIconFile;

    public function __construct(
        Context $context,
        UploadStockstatusIconFileInterface $uploadStockstatusIconFile,
        RemoveStockstatusSettingsIconFileInterface $removeStockstatusSettingsIconFile,
        StockstatusSettingsRepositoryInterface $settingsRepository,
        LoggerInterface $logger
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->logger = $logger;
        $this->uploadStockstatusIconFile = $uploadStockstatusIconFile;
        $this->removeStockstatusSettingsIconFile = $removeStockstatusSettingsIconFile;

        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $request = $this->getRequest();
        $optionId = (int)$request->getParam(StockstatusSettingsInterface::OPTION_ID);
        $storeId = (int)$request->getParam(Store::ENTITY, Store::DEFAULT_STORE_ID);
        $uploadedImage = $request->getFiles(StockstatusSettingsInterface::IMAGE_PATH);
        $settingModel = $this->getSettingModel($optionId, $storeId);

        try {
            $filteredData = $this->filterData($request->getPostValue());
            $settingModel->addData($filteredData);

            if ($uploadedImage && !empty($uploadedImage['name'])) {
                $iconPath = $this->uploadStockstatusIconFile->execute($uploadedImage, $optionId, $storeId);
                $settingModel->setImagePath($iconPath);
            }

            if ($request->getParam('delete_image', false)) {
                $this->removeStockstatusSettingsIconFile->execute($optionId, $storeId);
                $settingModel->setImagePath('');
            }

            $this->settingsRepository->save($settingModel);
            $this->messageManager->addSuccessMessage(__('Settings was saved successfully.'));
        } catch (CouldNotSaveException|ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to save Stock Status setting. Check logs for more information.')
            );
            $this->logger->error($e->getMessage());
        }

        /** @var Forward $forward**/
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $forward->setController('product_attribute');

        return $forward->forward('settings');
    }

    private function filterData(array $data): array
    {
        $resultData = $data;
        $useDefaultFields = $data['use_default'] ?? [];

        foreach ($useDefaultFields as $field) {
            $resultData[$field] = null;
        }

        return $resultData;
    }

    private function getSettingModel(int $optionId, int $storeId): StockstatusSettingsInterface
    {
        return $this->settingsRepository->getByOptionIdAndStoreId($optionId, $storeId);
    }
}
