<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $profile = $this->profilesRepository->getById($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('amasty_orderexport/*');

                return;
            }
        } else {
            $profile = $this->profilesRepository->create();
        }

        $data = $this->backendSession->getPageData(true);

        if (!empty($data)) {
            $profile->addData($data);
        }

        $this->_coreRegistry->register('current_amasty_orderexport', $profile);
        $title = $id ? __('Edit Amasty Efficient Order Export Profile') : __('New Profile');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('System'), __('System'))
                   ->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }

        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()->prepend($id ? $profile->getName() : __('New Profile'));
        $this->_view->renderLayout();
    }
}
