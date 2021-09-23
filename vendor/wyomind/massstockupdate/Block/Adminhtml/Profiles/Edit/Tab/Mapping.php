<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Class Mapping
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab
 * 
 */
class Mapping extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public $module = "MassStockUpdate";
    public $_dataHelper = null;

    /**
     * Mapping constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Wyomind\MassStockUpdate\Helper\Data $dataHelper
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Wyomind\MassStockUpdate\Helper\Data $dataHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
    
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getModel()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model;
    }

    /**
     * Generate the source code for the mapping row
     * @param array $data
     * @return mixed
     */
    public function getRow($data = [])
    {


        $storeviewArray = (!isset($data['storeviews']) || !count($data['storeviews'])) ? [0] : $storeviewArray = $data['storeviews'];
        $id = (!isset($data['id'])) ? "" : $data["id"];
        $source = (!isset($data['source'])) ?: $data['source'];
        $default = (!isset($data['default'])) ? "" : $data['default'];
        $script = (!isset($data['scripting'])) ? "" : $data['scripting'];
        $enabled = (!isset($data['enabled'])) ? "1" : $data['enabled'];
        $color = (!isset($data['color'])) ? "rgba(255,255,255,0.8)" : $data['color'];
        $tag = (!isset($data['tag'])) ? "" : $data['tag'];
        $configurable = (!isset($data['configurable'])) ? 0 : $data['configurable'];
        $importupdate = (!isset($data['importupdate'])) ? "2" : $data['importupdate'];


        $tagClassname = "";
        if ($tag == "") {
            $tagClassname = "invisible";
        }
        $mapping = $this->_dataHelper->getJsonAttributes(false);


        $storeviews = "<ul>";

        foreach ($mapping["storeviews"]["children"] as $website) {
            $storeviews .= '<li class="website"><span class="label_area">' . $website["label"] . '</span>';
            $storeviews .= "<ul>";
            if (array_key_exists('children', $website)) {
                foreach ($website["children"] as $store) {
                    $storeviews .= '<li class="store"><span class="label_area">' . $store["label"] . '</span>';

                    if (array_key_exists('children', $store)) {
                        $storeviews .= "<ul>";
                        foreach ($store["children"] as $view) {
                            $checked = "";
                            if (in_array($view['value'], $storeviewArray)) {
                                $checked = "checked";
                            }
                            $storeviews .= '<li class="store-view"><input ' . $checked . ' name="label_area-'.$id.'" type="checkbox" value = "' . $view["value"] . '"><span class="label_area">' . $view["label"] . '</span>';
                            $storeviews .= '</li>';
                        }
                        $storeviews .= "</ul>";
                    }
                    $storeviews .= '</li>';
                }
            }
            $storeviews .= "</ul>";
            $storeviews .= '</li>';
        }
        $disabled = ($enabled) ? "" : "disabled";
        $select = ' <select  name="attribute-'.$id.'" class="attribute ">';
        $select .= '<option data-options value="Ignored/ignored" >-- ' . __("Select an attribute") . ' --</option>';

        $scopeClassname = "hidden";
        $configurableClassname = "";
        $importupdateClassname = "";

        foreach ($mapping as $label => $attributes) {
            if (!in_array($label, ["storeviews", "Other"])) {
                $select .= '<optgroup  label="' . $label . '" >';

                foreach ($attributes as $attribute) {
                    if (isset($attribute['id'])) {
                        $selected = "";

                        if ($id == $attribute['id']) {
                            if (stristr($attribute["style"], "storeviews-dependent") !== false) {
                                $scopeClassname = "";
                            };
                            if (stristr($attribute["style"], "no-configurable") !== false) {
                                $configurableClassname = "hidden";
                            };

                            $selected = "selected";
                        }
                        $data = "";

                        if (isset($attribute["options"])) {
                            $options = [];
                            if (is_array($attribute['options'])) {
                                foreach ($attribute["options"] as $key => $option) {
                                    $options[$key] = addslashes($option);
                                }
                            }
                            $data = base64_encode(json_encode($options));
                        }
                        $multiple = "";
                        if (isset($attribute["multiple"])) {
                            $multiple = "multiple";
                        }
                        $newable = false;
                        if (isset($attribute["newable"])) {
                            $newable = true;
                        }

                        $select .= '<option data-newable="' . $newable . '" data-multiple="' . $multiple . '" data-options="' . $data . '" class="' . $attribute['style'] . '" ' . $selected . ' value="' . $attribute['id'] . '" >' . addslashes($attribute['label']) . '</option>';
                    }
                }

                $select .= '</optgroup >';
            }
        }

        $select .= '</select>';

        $defaultScopeChecked = "";
        if (in_array(0, $storeviewArray)) {
            $defaultScopeChecked = "checked";
        };

        $storeviews .= "</ul>";


        $active = ($script == "") ? "" : "active";
        $invisible = ($script == "") ? ($source == "") ? "" : "invisible" : "invisible";
        $rand = rand(111111, 999999);

        $configurableChecked_0 = "";
        $configurableChecked_1 = "";
        $configurableChecked_2 = "";

        switch ($configurable) {
            case 1:
                $configurableChecked_1 = "checked";
                break;
            case 2:
                $configurableChecked_2 = "checked";
                break;
            default:
                $configurableChecked_0 = "checked";
        }

        $importupdateChecked_0 = "";
        $importupdateChecked_1 = "";
        $importupdateChecked_2 = "";

        switch ($importupdate) {
            case 1:
                $importupdateChecked_1 = "checked";
                break;
            case 2:
                $importupdateChecked_2 = "checked";
                break;
            default:
                $importupdateChecked_0 = "checked";
        }

        $template = ' <li class="sortable ' . $disabled . '" style="background-color: ' . $color . '">
       
        <input type="hidden" class="aggregate" name="aggregate-'.$id.'" value="{}"/>
        
        <div class="tag-input-box ' . $tagClassname . '" >
        <input type="text" name="tag'.$id.'" value="' . $tag . '"/>
        </div>
       
        <div class="mapping-row">
        
        <span class="cell body tooltip"><span class="icon grip"></span><div class="tooltip-content">' . __("Move this row up/down") . '</div></span>
       
        <span class="cell body tooltip">' . $select . ' 
                <div class="tooltip-content">' . __("Select an attribute") . '</div>
        </span>
        <span class="cell body tooltip"><span class="icon link"></span><div class="tooltip-content">' . __("Disable/enable this row") . '</div></span>
        <span class="cell body tooltip"><select name="aggregate-'.$id.'" class="source"  ><option value = "" >' . $source . '</option></select><div class="tooltip-content">' . __("Select a source field") . '</div></span>
        <span class="cell body tooltip"><select name="options-'.$id.'" ' . $multiple . ' class="default options  ' . $invisible . '" style="display:none"></select><input name="default-'.$id.'" type="text" class="default value ' . $invisible . '" value="' . $default . '" /><div class="tooltip-content">' . __("Apply a fixed value") . '</div></span>
        <span class="replacement"></span>
        <span class="cell body tooltip"><span class="icon code ' . $active . '"></span><div class="tooltip-content">' . __("Apply a custom script") . '</div><textarea name="scripting-'.$id.'" class="scripting hidden">' . ($script) . '</textarea></span>
        <span class="cell body tooltip"><span class="icon color"></span><div class="tooltip-content">' . __("Apply a color to the row") . '</div></span>
        <span class="cell body tooltip"><span class="icon tag"></span><div class="tooltip-content">' . __("Apply a label to the row") . '</div></span>
        <span class="cell body tooltip"><span class="icon trash"></span><div class="tooltip-content">' . __("Delete this row") . '</div></span>
        <span class="cell body tooltip"><span class="icon add"></span><div class="tooltip-content">' . __("Add a new row") . '</div></span>
        
        </div>
       
        <div class="additional-row scope-row ' . $scopeClassname . '">
        <span class="cell body" colspan = "4">
        <a class="link scope-link">
        <div class="icon chevron-right"></div>
        <span class="apply-to scope-apply-to">Apply to </span> <span class="summary scope-summary"></span>
        </a>
        <div class="details scope-details hidden">
        <div class="icon chevron-down"></div> 
        <div class="all-store-views"><input name="scope-'.$id.'" type="checkbox" class="default-scope" value = "0" ' . $defaultScopeChecked . '>' . $mapping["storeviews"]["label"] . ' (' . __("Apply to all store views") . ' )  </div>
        ' . $storeviews . '
       
        </div>
        </span>
        </div>';

        if ($this->module == 'MassProductImport') {
            $template .= '
       
        <div class="additional-row configurableproducts-row ' . $configurableClassname . '">
         <span class="cell body" colspan = "4">
        <a class="link configurable-link">
        <div class="icon chevron-right"></div>
        <span class="apply-to configurable-apply-to">Apply to </span> <span class="summary configurable-summary"></span>
        </a>
        <div class="details configurable-details hidden">
        <div class="icon chevron-down"></div> 
        
       <div class="input"><input ' . $configurableChecked_0 . ' type="radio" id="configurableproducts_input_' . $rand . '_0" name="configurableproducts_input_' . $rand . '" value = "0" ><label for="configurableproducts_input_' . $rand . '_0">' . __("The current product only") . ' </label> </div>
       <div class="input"><input ' . $configurableChecked_1 . ' type="radio" id="configurableproducts_input_' . $rand . '_1" name="configurableproducts_input_' . $rand . '" value = "1" ><label for="configurableproducts_input_' . $rand . '_0">' . __("The configurable product created on the fly only") . '   </label> </div>
       <div class="input"><input ' . $configurableChecked_2 . ' type="radio" id="configurableproducts_input_' . $rand . '_2" name="configurableproducts_input_' . $rand . '" value = "2" ><label for="configurableproducts_input_' . $rand . '_0">' . __("Both, configurable product created on the fly and simple product associated") . '  </label>  </div>
       
        
        </div>
        </span>
        </div>';

            $template .= '
       
        <div class="additional-row importupdate-row ' . $importupdateClassname . '">
         <span class="cell body" colspan = "4">
        <a class="link importupdate-link">
        <div class="icon chevron-right"></div>
        <span class="apply-to importupdate-apply-to">Apply to </span> <span class="summary importupdate-summary"></span>
        </a>
        <div class="details importupdate-details hidden">
        <div class="icon chevron-down"></div> 
        
       <div class="input"><input ' . $importupdateChecked_0 . ' type="radio" id="importupdate_input_' . $rand . '_0" name="importupdate_input_' . $rand . '" value = "0" ><label for="importupdate_input_' . $rand . '_0">' . __("New products only") . ' </label> </div>
       <div class="input"><input ' . $importupdateChecked_1 . ' type="radio" id="importupdate_input_' . $rand . '_1" name="importupdate_input_' . $rand . '" value = "1" ><label for="importupdate_input_' . $rand . '_0">' . __("Existing products only") . '   </label> </div>
       <div class="input"><input ' . $importupdateChecked_2 . ' type="radio" id="importupdate_input_' . $rand . '_2" name="importupdate_input_' . $rand . '" value = "2" ><label for="importupdate_input_' . $rand . '_0">' . __("Both, new and existing products") . '  </label>  </div>
       
        
        </div>
        </span>
        </div>';
        }
        $template .= '</li>';

        return str_replace(["\n", "\r"], "", $template);
    }

    /**
     * Get Profile Id
     * @return mixed
     */
    public function getProfileId()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getId();
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Mapping & rules');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Mapping & rules');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
