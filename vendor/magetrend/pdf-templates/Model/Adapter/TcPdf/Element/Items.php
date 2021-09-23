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

namespace Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element;

use Magetrend\PdfTemplates\Model\Config\Source\Direction;

/**
 * Draw pdf element items
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Items extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Table
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Items';

    /**
     * @var array
     */
    private $lines = [];

    /**
     * @var int
     */
    public $startFromItem = 0;

    /**
     * @var null
     */
    public $lastPageFooterHeight = null;

    /**
     * @var int
     */
    public $lastItemY = 0;

    /**
     * @var bool
     */
    public $isFinished = false;

    public $collectionSize = null;

    /**
     * Draw Pdf element
     *
     * @param $pdf
     * @param $elemetData
     * @param $source
     * @param $template
     * @param $elements
     * @param $currentPage
     * @return $this
     */
    public function draw($pdf, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdf, $elemetData, $source, $template, $elements, $currentPage);

        $attributes = $this->getAttributes();
        if (empty($attributes)) {
            return $this;
        }

        $items = $this->getAllItems();
        $itemCount = count($items);

        if ($itemCount == 0) {
            $this->setIsFinished(true);
            $this->lastItemY = $this->removePx($attributes['top']);
            return $this;
        }

        $this->items = [];
        for ($i = $this->startFromItem; $i < $itemCount; $i++) {
            $this->items[] = $items[$i];
        }

        if ($currentPage > 1) {
            $this->elementData['attributes']['top'] = $this->template->getHeaderHeight();
            $this->attributes = null;
        }

        $this->drawHeader($currentPage);
        $this->setIsFinished($this->drawItems());
        $this->drawTableBorders();

        return $this;
    }

    /**
     * Draw items
     *
     * @return bool
     */
    public function drawItems()
    {
        $attributes = $this->getAttributes();
        $items = $this->getItems();

        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        $topY = $this->removePx($attributes['top'])
            + $this->removePx($attributes['table_header_height'])
            + $this->removePx($attributes['table_top_border_size']);
        $itemsCount = count($items);
        $i = 0;

        $lastVisibleItemKey = 0;
        foreach ($items as $key => $item) {
            if ($this->getOrderItem($item)->getParentItem()) {
                continue;
            }
            $lastVisibleItemKey = $key;
        }

        foreach ($items as $key => $item) {
            if ($this->getOrderItem($item)->getParentItem()) {
                $this->startFromItem++;
                continue;
            }
            $columnsData = $this->prepareColumnsData($item);
            $rowHeight = $this->getRowHeight($columnsData);

            if (!$this->isEnoughSpaceForItem($rowHeight, $topY, $lastVisibleItemKey==$key)) {
                return false;
            }

            $this->drawRowBackground($i, $topY, $rowHeight);
            $this->drawRowHorizontalBorder($i, $topY, $rowHeight);
            $this->drawRowVerticalBorder($topY, $rowHeight);
            $this->drawRowText($columnsData, $topY);

            if (isset($attributes['show_image']) && $attributes['show_image'] == 'true') {
                $this->drawProductImage($columnsData, $topY);
            }

            $topY += $rowHeight;
            $this->lastItemY = $topY;
            $this->startFromItem++;
            $i++;
        }

        return true;
    }

    /**
     * Prepare item data
     *
     * @param $item
     * @return array
     */
    public function prepareColumnsData($item)
    {
        $columnConfig = $this->getColumnConfig();
        $data = [];
        $attributes = $this->getAttributes();
        foreach ($columnConfig as $key => $column) {
            if ($this->isColumnHidden($key)) {
                continue;
            }

            if (!isset($column['renderer'])) {
                $column['renderer'] = 'Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\DefaultRenderer';
            }

            $data[$key] = $this->objectManager->get($column['renderer'])
                ->setData([
                    'item' => $item,
                    'order' => $this->order,
                    'item_renderer' => $this->getItemRenderer($item),
                    'column' => $key,
                    'attributes' => $attributes
                ])
                ->getPdfData();
        }

        return $data;
    }

    /**
     * Returns item options
     *
     * @param $item
     * @return string
     */
    public function getItemOptions($item)
    {
        $result = [];
        $options = $this->getOrderItem($item)->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        if (empty($result)) {
            return '';
        }
        $optionsString = '';
        foreach ($result as $option) {
            $optionsString.= $option['label'].': '.$option['value'].', ';
        }

        return rtrim($optionsString, ', ');
    }

    /**
     * Returns invoice items
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns item renderer model
     *
     * @param $item
     * @return \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Invoice\DefaultRenderer
     */
    public function getItemRenderer($item)
    {
        $orderItem = $this->getOrderItem($item);
        $oroductType = $orderItem->getProductType();

        switch ($oroductType) {
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE:
                $renderer = $this->bundleItemRenderer;
                break;
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                $renderer = $this->configurableItemRenderer;
                break;
            default:
                $renderer = $this->defaultItemRenderer;
                break;
        }

        $renderer->setItem($item)
            ->setSource($this->getSource());

        return $renderer;
    }

    public function getColumnConfig()
    {
        $columns = $this->moduleHelper->getColumnConfig($this->template->getType());
        $attributes = $this->getAttributes();

        if (isset($attributes['direction']) && $attributes['direction'] == Direction::RTL) {
            $columns = array_reverse($columns);
        }

        return $columns;
    }

    /**
     * Get all items from source (invoice, shipment, creditmemo)
     * @return mixed
     */
    public function getAllItems()
    {
        return $this->getSource()->getAllItems();
    }

    /**
     * Returns items number
     * @return int|void
     */
    public function getCollectionSize()
    {
        if ($this->collectionSize == null) {
            $this->collectionSize = count($this->getAllItems());
        }

        return $this->collectionSize;
    }

    public function getFieldListToRemovePx()
    {
        $itemsConfig = $this->objectManager->get('Magetrend\PdfTemplates\Model\Pdf\Config\Items')->getConfig();
        $fields = [];
        foreach ($itemsConfig['attributes'] as $key => $attribute) {
            if (isset($attribute['format']) && in_array('remove_px', $attribute['format'])) {
                $fields[] = $key;
            }
        }

        return array_merge($fields, parent::getFieldListToRemovePx());
    }

    public function drawProductImage($columnsData, $topY)
    {
        $attributes = $this->getAttributes();
        $columnConfig = $this->getColumnConfig();
        $columnName = 'product';
        if (!isset($columnsData[$columnName]) || !isset($columnsData[$columnName]['image'])) {
            return;
        }

        $imageConfig = $columnsData[$columnName]['image'];
        if (isset($attributes['direction']) && $attributes['direction'] == Direction::RTL) {
            $x = $attributes['left'] + $attributes['table_width'] - $imageConfig['right'] - $imageConfig['width'];
        } else {
            $x = $attributes['left'] + $imageConfig['left'];
        }

        $topY = $topY + $imageConfig['top'];
        $imagePath = $imageConfig['path'];
        $imageXY = $this->getImagePosition($topY, $x, $imageConfig['width'], $imageConfig['width']);
        $y = $this->toPoint($topY);
        $x = $this->toPoint($x);
        $width = $this->toPoint($imageConfig['width']);
        $height = $this->toPoint($imageConfig['width']);
        $this->pdf->Image($imagePath, $x, $y, $width, $height, '', '', '', false, 92, '', false, false, 0);
    }
}