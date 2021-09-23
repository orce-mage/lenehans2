<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form\Filename;

class FilenameInput
{
    public function get(string $field, string $dataScope = '', string $label = ''): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => !empty($label) ? $label : __('File Name'),
                        'visible' => true,
                        'dataScope' => !empty($dataScope) ? $dataScope . $field : $field,
                        'dataType' => 'text',
                        'formElement' => 'input',
                        'componentType' => 'input',
                        'comment' => __('Use {{date|format}} to customize filename. ' .
                            'Example: Filename-{{date|Y_m_d_h_i_s}}. ' .
                            '<a href="https://www.php.net/manual/en/function.date.php" target="_blank">Here</a> ' .
                            'you may find more options for date format.'),

                        'service' => ['template' => 'Amasty_ExportCore/form/element/service/comment']
                    ]
                ]
            ]
        ];
    }
}
