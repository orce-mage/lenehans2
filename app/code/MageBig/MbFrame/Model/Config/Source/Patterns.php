<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection.
 */

namespace MageBig\MbFrame\Model\Config\Source;

class Patterns implements \Magento\Framework\Option\ArrayInterface
{

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->_directoryList = $directoryList;
    }

    /**
     * Options getter.
     *
     * @return array
     */
    public function imageList()
    {
        $directry = $this->_directoryList->getPath('media') . '/wysiwyg/magebig/patterns';
        $images   = [];
        if (@is_dir($directry)) {
            if ($dh = @opendir($directry)) {
                while (($file = @readdir($dh)) !== false) {
                    if (@is_file($directry . '/' . $file)) {
                        $filetype = substr($file, -3, 3);
                        switch ($filetype) {
                            case 'jpg':
                            case 'png':
                            case 'gif':
                                $images[] = $file;
                                break;
                        }
                    }
                }
            }
        }

        return $images;
    }

    public function toOptionArray()
    {
        $images  = $this->imageList();
        $options = [];
        foreach ($images as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $value,
            ];
        }

        return $options;
    }
}
