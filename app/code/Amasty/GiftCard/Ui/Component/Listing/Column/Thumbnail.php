<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Ui\Component\Listing\Column;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    /**
     * @var FileUpload
     */
    private $fileUpload;

    public function __construct(
        FileUpload $fileUpload,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->fileUpload = $fileUpload;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[ImageInterface::IMAGE_ID])
                    && isset($item[ImageInterface::IMAGE_PATH])
                ) {
                    $imgUrl = $this->fileUpload->getImageUrl(
                        $item[ImageInterface::IMAGE_PATH]
                    );
                    $item[$fieldName . '_src'] = $imgUrl;
                    $item[$fieldName . '_orig_src'] = $imgUrl;
                }
            }
        }
        return $dataSource;
    }
}
