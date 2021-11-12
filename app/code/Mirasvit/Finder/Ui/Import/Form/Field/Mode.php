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

namespace Mirasvit\Finder\Ui\Import\Form\Field;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    const MODE_OVERWRITE = 'overwrite';
    const MODE_UPDATE    = 'update';

    protected $options;

    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $data = [
            self::MODE_OVERWRITE => (string)__('Overwrite existing data'),
            self::MODE_UPDATE    => (string)__('Update existing data'),
        ];

        $options = [];

        foreach ($data as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        $this->options = $options;

        return $this->options;
    }
}
