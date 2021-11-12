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

namespace Mirasvit\Finder\Ui\Import\Listing\Component;

class ExportButton extends \Magento\Ui\Component\ExportButton
{
    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');

        if (isset($config['options']) && isset($config['options']['cvs'])) {
            // due to typo in core we need to remove wrong option
            unset($config['options']['cvs']);
        }
        if (isset($config['options']) && isset($config['options']['xml'])) {
            // export only in csv
            unset($config['options']['xml']);
        }

        $this->setData('config', $config);

        parent::prepare();
    }
}
