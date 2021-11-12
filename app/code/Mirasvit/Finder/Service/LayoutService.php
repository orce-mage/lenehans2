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

namespace Mirasvit\Finder\Service;

use Mirasvit\Finder\Api\Data\FinderInterface;

class LayoutService
{
    public function getXmlMarkup(FinderInterface $finder): string
    {

        return sprintf(
            '<block class="%s" name="%s">
    <arguments>
        <argument name="finder_id" xsi:type="string">%s</argument>
    </arguments>
</block>',
            \Mirasvit\Finder\Block\Finder::class,
            'finder_form' . $finder->getId(),
            $finder->getId()
        );
    }

    public function getWidgetMarkup(FinderInterface $finder): string
    {
        return sprintf(
            '{{block class="%s" block_id="%s" finder_id="%s"}}',
            \Mirasvit\Finder\Block\Finder::class,
            'finder_form' . $finder->getId(),
            $finder->getId()
        );
    }

    public function getPhpMarkup(FinderInterface $finder): string
    {
        return sprintf(
            '<?=$block->getLayout()->createBlock("%s")->setFinderId(%s)->toHtml() ?>',
            \Mirasvit\Finder\Block\Finder::class,
            $finder->getId()
        );
    }
}
