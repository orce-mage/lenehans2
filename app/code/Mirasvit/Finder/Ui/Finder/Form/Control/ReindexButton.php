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

use Mirasvit\Finder\Api\Data\FinderInterface;

class ReindexButton extends AbstractButton
{
    public function getButtonData()
    {
        $data = [];

        if ($this->getId()) {
            $data = [
                'label'      => __('Reindex'),
                'class'      => 'reindex',
                'on_click'   => sprintf("location.href = '%s';", $this->getReindexUrl()),
                'sort_order' => 15,
            ];
        }

        return $data;
    }

    public function getReindexUrl()
    {
        return $this->getUrl('*/*/reindex', [FinderInterface::ID => $this->getId()]);
    }
}
