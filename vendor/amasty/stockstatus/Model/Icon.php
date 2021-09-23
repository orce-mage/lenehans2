<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Magento\Framework\DataObject;

class Icon extends DataObject implements IconInterface
{
    public function getId()
    {
        return (int)$this->_getData(IconInterface::ID);
    }

    public function setId($id)
    {
        $this->setData(IconInterface::ID, $id);
    }

    public function getOptionId(): int
    {
        return (int) $this->_getData(IconInterface::OPTION_ID);
    }

    public function setOptionId(int $optionId): void
    {
        $this->setData(IconInterface::OPTION_ID, $optionId);
    }

    public function getStoreId(): int
    {
        return (int) $this->_getData(IconInterface::STORE_ID);
    }

    public function setStoreId(int $storeId): void
    {
        $this->setData(IconInterface::STORE_ID, $storeId);
    }

    public function getPath(): ?string
    {
        return $this->_getData(IconInterface::PATH);
    }

    public function setPath(string $path): void
    {
        $this->setData(IconInterface::PATH, $path);
    }
}
