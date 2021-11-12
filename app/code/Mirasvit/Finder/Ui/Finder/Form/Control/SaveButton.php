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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Ui\Finder\Form\Control;

use Magento\Ui\Component\Control\Container;

class SaveButton extends AbstractButton
{
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save'),
            'class'          => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'mst_finder_finder_form.mst_finder_finder_form',
                                'actionName' => 'save',
                                'params'     => [
                                    true,
                                    [
                                        'back' => 'edit',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class_name'     => Container::SPLIT_BUTTON,
            'options'        => $this->getOptions(),
        ];
    }

    private function getOptions(): array
    {
        return [
            [
                'id_hard'        => 'save_and_close',
                'label'          => __('Save & Close'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'mst_finder_finder_form.mst_finder_finder_form',
                                    'actionName' => 'save',
                                    'params'     => [
                                        true,
                                        [
                                            'back' => 'close',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
