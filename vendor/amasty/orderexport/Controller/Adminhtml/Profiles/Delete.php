<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

use Magento\Framework\Exception\LocalizedException;

class Delete extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $profile = $this->profilesRepository->getById($id);
                $this->profilesRepository->delete($profile);
                $this->messageManager->addSuccessMessage(__('This profile is deleted.'));
                $this->_redirect('amasty_orderexport/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);

                return;
            }
        }

        $this->messageManager->addErrorMessage(__('Can\'t find a item to delete.'));
        $this->_redirect('amasty_orderexport/*/');
    }
}
