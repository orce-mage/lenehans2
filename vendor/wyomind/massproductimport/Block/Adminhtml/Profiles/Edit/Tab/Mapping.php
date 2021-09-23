<?php

namespace Wyomind\MassProductImport\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Class Mapping
 * @package Wyomind\MassProductImport\Block\Adminhtml\Profiles\Edit\Tab
 */
class Mapping extends \Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab\Mapping
{
    /**
     * @var \Wyomind\MassProductImport\Model\Rules
     */
    public $rulesCollectionFactory;

    /**
     * Mapping constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Wyomind\MassStockUpdate\Helper\Data $dataHelper
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Wyomind\MassStockUpdate\Helper\Data $dataHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,

        array $data=[]
    ) {
        parent::__construct($context, $registry, $dataHelper, $formFactory, $data);
        $this->rulesCollectionFactory=$rulesCollectionFactory;
    }

    /**
     * @var string
     */
    public $module="MassProductImport";

    /**
     * @param array $data
     * @return mixed
     */
    public function getRow($data=array())
    {
        $rule=(!isset($data['rule'])) ? "" : $data['rule'];
        $active=($rule == "") ? "" : "active";
        $search="<span class=\"replacement\"></span>";
        $replace=' <span class="cell body tooltip" ><span class="icon rule ' . $active . '" data-value="' . $rule . '"></span><div class="tooltip-content">' . __("Apply a replacement rule") . '</div></span>';

        $html=parent::getRow($data); //
        return str_replace($search, $replace, $html);
    }

    /**
     *
     */
    public function getRules()
    {

        $collection=$this->rulesCollectionFactory->create();
        $html=null;
        foreach ($collection as $item) {

            $html.="<option value='" . $item->getId() . "'>" . $item->getName() . "</option>";
        }
        if ($html == null) {
            $html="<option>" . __("No rule defined.") . "</option>";
        }

        return $html;
    }

}
