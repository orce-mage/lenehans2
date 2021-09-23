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

abstract class AbstractRate
{
    /**
     * @var VariableFactory
     */
    protected $variableFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractRate constructor.
     * @param VariableFactory $variableFactory
     * @param Config $config
     */
    public function __construct(
        VariableFactory $variableFactory,
        Config $config
    ) {
        $this->variableFactory = $variableFactory;
        $this->config          = $config;
    }

    /**
     * @return int [0..100]
     */
    abstract public function getRate();

    /**
     * @param int $rate
     * @return $this
     */
    abstract public function saveToHistory($rate);

    /**
     * @return array
     */
    abstract public function getHistory();

    /**
     * @param int    $rate
     * @param string $variableCode
     * @return $this
     */
    protected function saveRateToHistory($rate, $variableCode)
    {
        $variable = $this->variableFactory->create()
            ->loadByCode($variableCode);

        $value = $variable->getValue();

        if ($value) {
            $value = \Zend_Json::decode($value);
            if (!$value) {
                $value = [];
            }
        } else {
            $value = [];
        }
        $t = $this->getTimeKey();
        $value[$t] = $rate;

        //remove old items
        for ($i =0; $i < 100; $i++) {
            $k = min(array_keys($value));
            if ($k + 25 * 60 * 60 < $t) { //older then 25 hours
                unset($value[$k]);
            } else {
                break;
            }
        }

//        if (count($value) > 600) {
//            $value = array_slice($value, count($value) - 600, 600, true);
//        }

        $variable->setCode($variableCode)
            ->setData('html_value', \Zend_Json::encode($value))
            ->save();

        return $this;
    }

    /**
     * @param string $variableCode
     * @return array
     */
    protected function getRateHistory($variableCode)
    {
        $variable = $this->variableFactory->create()
            ->loadByCode($variableCode);

        $value = $variable->getValue();

        if ($value) {
            $value = \Zend_Json::decode($value);
            if (!$value) {
                $value = [];
            }
        } else {
            $value = [];
        }

        return $value;
    }

    /**
     * @return int
     */
    protected function getTimeKey()
    {
        $dateTime = $this->config->getDateTime();

        return ceil($dateTime->getTimestamp() / 60) * 60;
    }
}
