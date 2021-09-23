<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

class Run extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = null;
        $download = $this->getRequest()->getParam('download', false);
        if ($download && $this->getRequest()->getParam(Filter::SELECTED_PARAM) !== 'false') {
            $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
            $collection = $this->filter->getCollection($this->orderCollectionFactory->create());
            $ids = $collection->getAllIds();
        } else {
            $collection = $this->orderCollectionFactory->create();
            $ids = $collection->getAllIds();
        }

        if ($id) {
            try {
                $profile = $this->profilesRepository->getById($id);
                $this->_coreRegistry->register('amorderexport_manual_run_triggered', true, true);
                $result = $profile->setData('enabled', true)->run($ids);

                if (!$download) {
                    $this->messageManager->addSuccessMessage(__('Profile Run Success.'));
                }

                if ($result) {
                    if ($download) {
                        $this->_redirect('amasty_orderexport/history/download/', ['id' => $result]);

                        return;
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Profile Run Failed.'));
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('amasty_orderexport/*');

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if (!is_null($ids) && $download) {
            $this->_redirect('sales/order/index');
        } else {
            $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);
        }
    }
}
