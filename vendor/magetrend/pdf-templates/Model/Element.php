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

namespace Magetrend\PdfTemplates\Model;

/**
 * Element model class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Element extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ResourceModel\Element\CollectionFactory
     */
    public $elementCollectionFactory;

    /**
     * @var ResourceModel\Attribute\CollectionFactory
     */
    public $attributeCollectionFactory;

    /**
     * Element constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Element\CollectionFactory $elementCollectionFactory
     * @param ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetrend\PdfTemplates\Model\ResourceModel\Element\CollectionFactory $elementCollectionFactory,
        \Magetrend\PdfTemplates\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->elementCollectionFactory = $elementCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\PdfTemplates\Model\ResourceModel\Element');
    }

    /**
     * Returns full to element related data by template ID
     *
     * @param $templateId
     * @return array
     */
    public function getElementsData($templateId)
    {
        $data = [];
        $elementCollection = $this->elementCollectionFactory->create()
            ->addFieldToFilter('template_id', $templateId)
            ->setOrder('sort_order', 'ASC');

        if ($elementCollection->getSize() > 0) {
            foreach ($elementCollection as $element) {
                $elementIds[] = $element->getId();
                $data[$element->getId()] = $element->getData();
            }

            if (!empty($elementIds)) {
                $attributeCollection = $this->attributeCollectionFactory->create()
                    ->addFieldToFilter('element_id', ['in' => $elementIds]);

                if ($attributeCollection->getSize() > 0) {
                    foreach ($attributeCollection as $attribute) {
                        $elementId = $attribute->getElementId();
                        $key = $attribute->getAttributeKey();
                        if (!isset($data[$elementId]['attributes'])) {
                            $data[$elementId]['attributes'] = [];
                        }
                        $data[$elementId]['attributes'][$key] = $attribute->getAttributeValue();
                    }
                }
            }
        }
        return $data;
    }
}
