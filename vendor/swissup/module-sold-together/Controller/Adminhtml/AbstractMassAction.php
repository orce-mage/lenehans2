<?php

namespace Swissup\SoldTogether\Controller\Adminhtml;


abstract class AbstractMassAction extends \Magento\Backend\App\Action
{

    /**
     * Massactions filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter = null;

    /**
     * Collection factory of records to process
     */
    protected $collectionFactory = null;

    /**
     * Mass delete implementation
     */
    protected function massDelete()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection->getItems() as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccess(
            __('A total of %1 relation(s) have been deleted.', $collectionSize)
        );
    }
}
