<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Block\Adminhtml;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Mirasvit\Sorting\Service\SampleService;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

class Criterion extends Container
{
    private $sampleService;

    public function __construct(
        SampleService $sampleService,
        Context $context
    ) {
        $this->sampleService = $sampleService;

        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        $this->buttonList->add('add_new', [
            'id'           => 'add_new',
            'label'        => __('Add New'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => SplitButton::class,
            'options'      => $this->getButtonOptions(),
        ]);

        return parent::_prepareLayout();
    }

    private function getButtonOptions()
    {
        $splitButtonOptions = [
            [
                'label'   => __('New Criteria'),
                'onclick' => "setLocation('" . $this->getCreateUrl() . "')",
                'default' => true,
            ],
        ];

        foreach ($this->sampleService->getCriterionListFromJson() as $sampleData) {
            $splitButtonOptions[] = [
                'label'   => $sampleData['label'],
                'onclick' => "setLocation('" . $this->getCreateUrl($sampleData[CriterionInterface::CODE]) . "')",
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getCreateUrl($code = null)
    {
        if ($code) {
            return $this->getUrl('sorting/criterion/edit', [
                'code' => $code,
            ]);
        } else {
            return $this->getUrl('sorting/criterion/edit');
        }
    }
}
