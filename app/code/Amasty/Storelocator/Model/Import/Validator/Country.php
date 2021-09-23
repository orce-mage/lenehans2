<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import\Validator;

use Amasty\Storelocator\Model\Import\Location as Location;
use Magento\Directory\Model\Config\Source\Country as ConfigCountry;
use Magento\Directory\Model\CountryFactory;

class Country extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var ConfigCountry
     */
    private $configCountry;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    public function __construct(
        ConfigCountry $configCountry,
        CountryFactory $countryFactory
    ) {
        $this->configCountry = $configCountry;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;

        if (isset($value[Location::COL_COUNTRY]) && !empty($value[Location::COL_COUNTRY])) {
            $valid = $this->isCountryValid($value[Location::COL_COUNTRY]);
        }

        if (!$valid) {
            $this->_addMessages([self::ERROR_COUNTRY_IS_EMPTY]);
        }

        return $valid;
    }

    /**
     * Validate by country
     *
     * @param string $value
     *
     * @return bool
     */
    private function isCountryValid($value)
    {
        $countries = $this->configCountry->toOptionArray();

        foreach ($countries as $countryKey => $country) {
            if ($value == $countryKey || $value == $country) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $countryName
     *
     * @return string
     */
    public function getCountryByName($countryName)
    {
        $countries = $this->configCountry->toOptionArray();

        foreach ($countries as $country) {
            if (isset($country['label']) && $country['label'] == $countryName
                || isset($country['value']) && $country['value'] == $countryName
            ) {
                return isset($country['value']) ? $country['value'] : '';
            }
        }

        return '';
    }

    /**
     * @param mixed $regionName
     * @param string $country
     *
     * @return int|string
     */
    public function getRegionByName($regionName, $country)
    {
        $countryCode = $this->getCountryByName($country);

        if ($countryCode) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $regions = $country->getRegions();

            /** @var \Magento\Directory\Model\Region $region */
            foreach ($regions as $region) {
                if ($region->getId() == $regionName
                    || $region->getName() == $regionName
                ) {
                    return $region->getId();
                }
            }
        }

        return '';
    }
}
