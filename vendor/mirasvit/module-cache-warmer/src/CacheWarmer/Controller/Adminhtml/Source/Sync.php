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




namespace Mirasvit\CacheWarmer\Controller\Adminhtml\Source;


use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractSource;

class Sync extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(SourceInterface::ID);

        if ($id) {
            $message = 'The synchronization process can take too much time to perform it in the browser. '
                . 'To synchronize this source please use the following command: '
                . 'bin/magento mirasvit:cache-warmer:sync-source --source-id ' . $id;

            $this->messageManager->addWarningMessage(__($message));
            return $this->resultRedirectFactory->create()->setPath('*/*/edit/source_id/' . $id);
        } else {
            $this->messageManager->addWarningMessage(__('Please save the Source before synchronizing it'));
            return $this->resultRedirectFactory->create()->setPath('*/*/edit');
        }
    }
}
