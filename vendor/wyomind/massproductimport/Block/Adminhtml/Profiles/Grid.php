<?php

namespace Wyomind\MassProductImport\Block\Adminhtml\Profiles;

class Grid extends \Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Grid
{

    public $module = "MassProductImport";
    public $_collectionFactory;

    protected function _construct()
    {
        parent::_construct();
        $this->setId('MassProductImportGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareColumns()
    {


        $this->addColumnAfter('profile_method', [
            'header' => __('Method'),
            'align' => 'left',
            'index' => 'profile_method',
            'type' => 'options',
            'options' => [
                \Wyomind\MassProductImport\Helper\Data::UPDATE => __('Update products only'),
                \Wyomind\MassProductImport\Helper\Data::IMPORT => __('Import new products only'),
                \Wyomind\MassProductImport\Helper\Data::UPDATEIMPORT => __('Update products and import new products'),
            ],
                ], "file_type");

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return "";
    }
}
