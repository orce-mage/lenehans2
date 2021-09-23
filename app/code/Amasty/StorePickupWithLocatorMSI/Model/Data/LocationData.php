<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Data;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class LocationData extends AbstractSimpleObject implements LocationDataInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->_get(self::ID);
    }

    /**
     * @param int $id
     * @return LocationData|void
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->_get(self::NAME);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->_get(self::COUNTRY);
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->setData(self::COUNTRY, $country);
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->_get(self::CITY);
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->_get(self::ZIP);
    }

    /**
     * @param string|null $zip
     */
    public function setZip(?string $zip): void
    {
        $this->setData(self::ZIP, $zip);
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->_get(self::ADDRESS);
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->_get(self::LAT);
    }

    /**
     * @param string|null $lat
     */
    public function setLat(?string $lat): void
    {
        $this->setData(self::LAT, $lat);
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->_get(self::LNG);
    }

    /**
     * @param string|null $lng
     */
    public function setLng(?string $lng): void
    {
        $this->setData(self::LNG, $lng);
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->_get(self::PHOTO);
    }

    /**
     * @param string|null $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->setData(self::PHOTO, $photo);
    }

    /**
     * @return string|null
     */
    public function getMarker(): ?string
    {
        return $this->_get(self::MARKER);
    }

    /**
     * @param string|null $marker
     */
    public function setMarker(?string $marker): void
    {
        $this->setData(self::MARKER, $marker);
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->_get(self::STATE);
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->setData(self::STATE, $state);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->_get(self::PHONE);
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->_get(self::WEBSITE);
    }

    /**
     * @param string|null $website
     */
    public function setWebsite(?string $website): void
    {
        $this->setData(self::WEBSITE, $website);
    }

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string
    {
        return $this->_get(self::STORE_IMG);
    }

    /**
     * @param string|null $storeImg
     */
    public function setStoreImg(?string $storeImg): void
    {
        $this->setData(self::STORE_IMG, $storeImg);
    }

    /**
     * @return string|null
     */
    public function getMarkerImg(): ?string
    {
        return $this->_get(self::MARKER_IMG);
    }

    /**
     * @param string|null $markerImg
     */
    public function setMarkerImg(?string $markerImg): void
    {
        $this->setData(self::MARKER_IMG, $markerImg);
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->_get(self::SHORT_DESCRIPTION);
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * @return bool
     */
    public function getCurbsideEnabled(): bool
    {
        return (bool)$this->_get(self::CURBSIDE_ENABLED);
    }

    /**
     * @param bool $curbsideEnabled
     * @return void
     */
    public function setCurbsideEnabled(bool $curbsideEnabled): void
    {
        $this->setData(self::CURBSIDE_ENABLED, $curbsideEnabled);
    }

    /**
     * @return string|null
     */
    public function getCurbsideConditionsText(): ?string
    {
        return $this->_get(self::CURBSIDE_CONDITIONS_TEXT);
    }

    /**
     * @param string|null $curbsideConditionsText
     * @return void
     */
    public function setCurbsideConditionsText(?string $curbsideConditionsText): void
    {
        $this->setData(self::CURBSIDE_CONDITIONS_TEXT, $curbsideConditionsText);
    }
}
