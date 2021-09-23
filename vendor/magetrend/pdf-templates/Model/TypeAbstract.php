<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-source-pro
 */

namespace Magetrend\PdfTemplates\Model;

abstract class TypeAbstract
{
    public $moduleHelper;

    public $filter = null;

    public $zendProcessor = null;

    public $tcpdfProcessor = null;

    abstract public function getCollection();

    abstract public function getObjectById($objectId);

    abstract public function getType();

    abstract public function getTypeLabel();

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper
    ) {
        $this->moduleHelper = $moduleHelper;
    }

    public function getPreviewObjectCollection()
    {
        $collection = $this->getCollection()
            ->setOrder('created_at', 'DESC')
            ->setPageSize(60);

        return $collection;
    }

    public function getModuleName()
    {
        return 'Magetrend_PdfTemplates';
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getZendProcessor()
    {
        return $this->zendProcessor->create();
    }
    public function getTcPdfProcessor()
    {
        return $this->tcpdfProcessor->create();
    }
}
