<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Magento\Framework\UrlInterface;

class GetIconUrlByPath
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function execute(string $path): ?string
    {
        return $this->urlBuilder->getDirectUrl(
            GetMediaPath::ICONS_PATH . $path,
            ['_type' => UrlInterface::URL_TYPE_MEDIA]
        );
    }
}
