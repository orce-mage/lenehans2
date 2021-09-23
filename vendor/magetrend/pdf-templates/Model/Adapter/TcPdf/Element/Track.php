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
class Track extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Table
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Track';

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

    public $trackingItems;

    /**
     * Draw Pdf element
     *
     * @param $pdfPage
     * @param $elemetData
     * @param $invoice
     * @param $template
     * @param $elements
     * @param $currentPage
     * @return $this
     */
    public function draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage);

        $attributes = $this->getAttributes();
        if (empty($attributes)) {
            return $this;
        }

        $shipment = $this->getSource();
        $items = $shipment->getAllTracks();

        if (empty($items)) {
            $this->setIsFinished(true);
            $this->lastItemY = $this->removePx($attributes['top']);
            return $this;
        }

        $itemCount = count($items);
        $this->trackingItems = [];
        for ($i = $this->startFromItem; $i < $itemCount; $i++) {
            $this->trackingItems[] = $items[$i];
        }

        if ($currentPage > 1) {
            $this->elementData['attributes']['top'] = $this->template->getHeaderHeight();
            $this->attributes = null;
        }

        $headerHeight = $this->removePx($attributes['table_header_height']);
        $rowHeight = $this->removePx($attributes['table_row_height']);
        if (!$this->isEnoughSpaceForItem(
            $headerHeight+$rowHeight,
            $this->removePx($this->elementData['attributes']['top']),
            true
        )) {
            return $this;
        }

        $this->drawHeader($currentPage);
        $this->setIsFinished($this->drawRows());
        $this->drawTableBorders();
        return $this;
    }

    /**
     * Draw items
     *
     * @return bool
     */
    public function drawRows()
    {
        $attributes = $this->getAttributes();
        $items = $this->getTrackinItems();
        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        $topY = $attributes['top'] + $attributes['table_header_height'] + $attributes['table_top_border_size'];
        $itemsCount = count($items);
        $i = 0;
        foreach ($items as $key => $item) {
            $columnsData = $this->prepareColumnsData($item);
            $rowHeight = $this->getRowHeight($columnsData);

            if (!$this->isEnoughSpaceForItem($rowHeight, $topY, ($itemsCount-1)==$key)) {
                return false;
            }

            $this->drawRowBackground($i, $topY, $rowHeight);
            $this->drawRowHorizontalBorder($i, $topY, $rowHeight);
            $this->drawRowVerticalBorder($topY, $rowHeight);
            $this->drawRowText($columnsData, $topY);

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
                $column['renderer'] = 'Magetrend\PdfTemplates\Model\Pdf\Element\Track\Column\DefaultRenderer';
            }

            $data[$key] = $this->objectManager->get($column['renderer'])
                ->setData([
                    'item' => $item,
                    'order' => $this->order,
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
        $options = $item->getOrderItem()->getProductOptions();
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
    public function getTrackinItems()
    {
        return $this->trackingItems;
    }

    public function getColumnConfig()
    {
        $columns = $this->moduleHelper->getTrackColumnConfig();
        $attributes = $this->getAttributes();
        if (isset($attributes['direction']) && $attributes['direction'] == Direction::RTL) {
            $columns = array_reverse($columns);
        }

        return $columns;
    }

    public function getFieldListToRemovePx()
    {
        $itemsConfig = $this->objectManager->get('Magetrend\PdfTemplates\Model\Pdf\Config\Track')->getConfig();
        $fields = [];
        foreach ($itemsConfig['attributes'] as $key => $attribute) {
            if (isset($attribute['format']) && in_array('remove_px', $attribute['format'])) {
                $fields[] = $key;
            }
        }

        return array_merge($fields, parent::getFieldListToRemovePx());
    }
}
