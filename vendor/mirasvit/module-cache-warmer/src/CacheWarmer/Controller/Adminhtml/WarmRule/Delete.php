<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Controller\Adminhtml\WarmRule;

use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractWarmRule;

class Delete extends AbstractWarmRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(WarmRuleInterface::ID);

        if ($id) {
            try {
                $model = $this->WarmRuleRepository->get($id);
                $this->WarmRuleRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(__('Job Rule was removed'));
        } else {
            $this->messageManager->addErrorMessage(__('Please select a Job Rule'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
