<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Template\Type\Xml;

use Magento\Framework\DataObject;

class Config extends DataObject implements ConfigInterface
{
    const HEADER = 'header';
    const ITEM = 'item';
    const FOOTER = 'footer';

    public function getHeader(): ?string
    {
        return $this->getData(self::HEADER);
    }

    public function setHeader(?string $header): ConfigInterface
    {
        $this->setData(self::HEADER, $header);

        return $this;
    }

    public function getItem(): ?string
    {
        return $this->getData(self::ITEM);
    }

    public function setItem(?string $item): ConfigInterface
    {
        $this->setData(self::ITEM, $item);

        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->getData(self::FOOTER);
    }

    public function setFooter(?string $footer): ConfigInterface
    {
        $this->setData(self::FOOTER, $footer);

        return $this;
    }
}
