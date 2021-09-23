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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Service\Rate;

use Magento\Variable\Model\VariableFactory;
use Mirasvit\CacheWarmer\Model\Config;
use Magento\Framework\Shell\Driver;
use Mirasvit\Core\Service\CompatibilityService;

class ServerLoadRateService extends AbstractRate
{
    const VARIABLE_CODE = 'mst_cache_warmer_server_load_rate_v2';

    /**
     * @var Driver
     */
    private $driver;

    /**
     * ServerLoadRateService constructor.
     * @param VariableFactory $variableFactory
     * @param Config $config
     * @param Driver $driver
     */
    public function __construct(
        VariableFactory $variableFactory,
        Config $config,
        Driver $driver
    ) {
        $this->driver = $driver ?: CompatibilityService::getObjectManager()->get(Driver::class);
        parent::__construct($variableFactory, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getRate()
    {
        if ($this->isWin()) {
            $rate = ($this->getWinServerLoadFirst()) ? : $this->getWinServerLoadSecond();
        } else {
            $rate = sys_getloadavg();
            $rate = round($rate[0] / $this->getNumCores() * 100);
        }

        if ($rate > 100) {
            $rate = 50;
        }

        return $rate;
    }

    /**
     * @return bool
     */
    private function isWin()
    {
        if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
            return true;
        }

        return false;
    }

    /**
     * @return int|bool
     */
    private function getWinServerLoadFirst()
    {
        $load = false;
        if ($output = $this->getOutputFromExecCommand('wmic cpu get', 'loadpercentage /all 2>&1')) {
            $output = explode(PHP_EOL, $output);
        }

        if (!$output) {
            return $load;
        }
        foreach ($output as $line) {
            if ($line && preg_match("/^[0-9]+\$/", $line)) {
                $load = $line;
                break;
            }
        }

        return $load;
    }

    /**
     * @return int
     */
    private function getWinServerLoadSecond()
    {
        /** @var mixed $wmi */
        $wmi    = new \COM("Winmgmts://");
        $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");

        $cpuNum    = 0;
        $loadTotal = 0;

        foreach ($server as $cpu) {
            $cpuNum++;
            $loadTotal += $cpu->loadpercentage;
        }

        $load = round($loadTotal / $cpuNum);

        return $load;
    }

    /**
     * @return int
     */
    private function getNumCores()
    {
        $num = false;
        if (file_exists('/proc/cpuinfo')
            && is_readable('/proc/cpuinfo')
            && is_file('/proc/cpuinfo')
        ) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');

            preg_match_all('/^processor/m', $cpuinfo, $matches);

            $num = count($matches[0]);
        } elseif ($this->isWin()) {
            $output = $this->getOutputFromExecCommand('wmic cpu get', 'NumberOfCores');

            if (!$output) {
                return 1;
            }

            $num = intval($output);
        } else {
            $output = $this->getOutputFromExecCommand('sysctl', '-a');

            if (!$output) {
                return 1;
            }

            preg_match('/hw.ncpu: (\d+)/', $output, $matches);

            if ($matches) {
                $num = intval($matches[1][0]);
            }
        }

        $num = intval($num);

        return $num ? $num : 1;
    }

    /**
     * {@inheritdoc}
     */
    public function saveToHistory($rate)
    {
        return parent::saveRateToHistory($rate, self::VARIABLE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getHistory()
    {
        return parent::getRateHistory(self::VARIABLE_CODE);
    }

    /**
     * @param string $command
     * @param  string|array $arguments
     * @return bool|string
     */
    private function getOutputFromExecCommand($command, $arguments)
    {
        $output = false;

        try {
            if (is_executable($command)) {
                $response = $this->driver->execute($command, $arguments);
                $output = $response ? $response->getOutput() : false;
            }
        } catch (\Exception $e) {
            return $output;
        }

        return $output;
    }
}
