<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->attributeCollection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $tinterval) {
            $tinterval->delete();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
