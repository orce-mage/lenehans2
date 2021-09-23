<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Observer\EmailAttachment\Adapter;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;

/**
 * Email Attachment Observer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class PdfTemplate implements \Magento\Framework\Event\ObserverInterface
{
    public $dataObjectFactory;

    public $collectionFactory;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Execute observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $collection = $this->collectionFactory->create();

        if ($collection->getSize() == 0) {
            return;
        }

        foreach ($collection as $template) {
            $observer->getCollection()
                ->addItem($this->dataObjectFactory->create()->setData(
                    [
                        'code' => 'pdf_template_'.$template->getId(),
                        'label' => $template->getName().' (Magetrend/PdfTemplates)',
                        'adapter_class' => \Magetrend\PdfTemplates\Model\EmailAttachment\Adapter\PdfTemplate::class
                    ]
                ));
        }
    }
}
