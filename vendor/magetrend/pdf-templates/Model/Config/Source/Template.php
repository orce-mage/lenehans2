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

namespace Magetrend\PdfTemplates\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Template list source class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     */
    public $templateType = '';

    /**
     * @var \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Template constructor.
     *
     * @param \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = $this->toArray();
        $optionArray = [];
        foreach ($options as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $opions = [];
        $opions[0] = __('Default Magento Template');

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('type', $this->templateType);
        if ($collection->getSize() == 0) {
            return $opions;
        }

        foreach ($collection as $item) {
            $opions[$item->getId()] = $item->getName();
        }
        return $opions;
    }
}
