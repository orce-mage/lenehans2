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

namespace Mirasvit\Finder\Ui\Finder\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

class TemplateSource implements OptionSourceInterface
{
    private $filesystem;

    private $dir;

    public function __construct(
        Filesystem $filesystem,
        Dir $dir
    ) {
        $this->filesystem = $filesystem;
        $this->dir        = $dir;
    }

    public function toOptionArray(): array
    {
        $options = [
            [
                'label' => __('-'),
                'value' => '',
            ],
        ];

        $appDir = $this->filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
        $toScan = [
            'Mirasvit_Finder'           => [
                $appDir . 'design/frontend/*/*/Mirasvit_Finder/templates/finder/*.phtml',
                $this->dir->getDir('Mirasvit_Finder', 'view') . '/frontend/templates/finder/*.phtml',
            ],
            'Mirasvit_FinderSampleData' => [
                $this->dir->getDir('Mirasvit_FinderSampleData', 'view') . '/frontend/templates/finder/*.phtml',
            ],
        ];

        foreach ($toScan as $module => $dirs) {
            foreach ($dirs as $dirPattern) {
                $filenames = glob($dirPattern);
                if ($filenames) {
                    foreach ($filenames as $filename) {
                        $basename = pathinfo($filename)['basename'];
                        $name     = pathinfo($filename)['filename'];

                        $options[$name] = [
                            'value' => $module . '::finder/' . $basename,
                            'label' => ucfirst($name),
                        ];
                    }
                }
            }
        }

        return array_values($options);
    }
}
