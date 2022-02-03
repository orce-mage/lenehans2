<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EncryptSecretKeys implements DataPatchInterface
{
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string[]
     */
    private $configPathToEncrypt = [
        'aminvisiblecaptcha/setup/captchaSecret',
        'aminvisiblecaptcha/setup/captchaSecretV3',
    ];

    public function __construct(
        ConfigInterface $resourceConfig,
        EncryptorInterface $encryptor
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        foreach ($this->configPathToEncrypt as $path) {
            $this->changeConfigValue($path);
        }
    }

    /**
     * @param string $path
     * @return void
     */
    private function changeConfigValue(string $path): void
    {
        $connection = $this->resourceConfig->getConnection();
        $select = $connection->select()->from($this->resourceConfig->getTable('core_config_data'))
            ->where("path = '{$path}'");

        foreach ($connection->fetchAll($select) as $config) {
            if (!$config['value']) {
                continue;
            }

            $config['value'] = $this->encryptor->encrypt($config['value']);
            $this->resourceConfig->saveConfig($path, $config['value'], $config['scope'], $config['scope_id']);
        }
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
