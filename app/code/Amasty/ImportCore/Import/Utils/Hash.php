<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Magento\Framework\Encryption\Encryptor;

class Hash
{
    /**
     * @var Encryptor
     */
    private $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function hash($data): string
    {
        return (string)$this->encryptor->hash($data, Encryptor::HASH_VERSION_MD5);
    }
}
