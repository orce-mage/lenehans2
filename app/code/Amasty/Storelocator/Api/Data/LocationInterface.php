<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


declare(strict_types=1);

namespace Amasty\Storelocator\Api\Data;

/**
 * @TODO: specify all fields, getters and setters
 */
interface LocationInterface
{
    const ID = 'id';
    const NAME = 'name';
    const COUNTRY = 'country';
    const CITY = 'city';
    const ZIP = 'zip';
    const ADDRESS = 'address';
    const STATUS = 'status';
    const LAT = 'lat';
    const LNG = 'lng';
    const PHOTO = 'photo';
    const MARKER = 'marker';
    const POSITION = 'position';
    const STATE = 'state';
    const DESCRIPTION = 'description';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const WEBSITE = 'website';
    const CATEGORY = 'category';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const STORE_IMG = 'store_img';
    const STORES = 'stores';
    const SCHEDULE = 'schedule';
    const MARKER_IMG = 'marker_img';
    const SHOW_SCHEDULE = 'show_schedule';
    const URL_KEY = 'url_key';
    const META_TITLE = 'meta_title';
    const META_DESCRIPTION = 'meta_description';
    const META_ROBOTS = 'meta_robots';
    const SHORT_DESCRIPTION = 'short_description';
    const CANONICAL_URL = 'canonical_url';
    const CONDITION_TYPE = 'condition_type';
    const CURBSIDE_ENABLED = 'curbside_enabled';
    const CURBSIDE_CONDITIONS_TEXT = 'curbside_conditions_text';

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * @return string|null
     */
    public function getCountry(): ?string;

    /**
     * @param string|null $country
     * @return void
     */
    public function setCountry(?string $country): void;

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     * @return void
     */
    public function setCity(?string $city): void;

    /**
     * @return string|null
     */
    public function getZip(): ?string;

    /**
     * @param string|null $zip
     * @return void
     */
    public function setZip(?string $zip): void;

    /**
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * @param string|null $address
     * @return void
     */
    public function setAddress(?string $address): void;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void;

    /**
     * @return string|null
     */
    public function getLat(): ?string;

    /**
     * @param string|null $lat
     * @return void
     */
    public function setLat(?string $lat): void;

    /**
     * @return string|null
     */
    public function getLng(): ?string;

    /**
     * @param string|null $lng
     * @return void
     */
    public function setLng(?string $lng): void;

    /**
     * @return string|null
     */
    public function getPhoto(): ?string;

    /**
     * @param string|null $photo
     * @return void
     */
    public function setPhoto(?string $photo): void;

    /**
     * @return string|null
     */
    public function getMarker(): ?string;

    /**
     * @param string|null $marker
     * @return void
     */
    public function setMarker(?string $marker): void;

    /**
     * @return string|null
     */
    public function getPosition(): ?string;

    /**
     * @param string|null $position
     * @return void
     */
    public function setPosition(?string $position): void;

    /**
     * @return string|null
     */
    public function getState(): ?string;

    /**
     * @param string|null $state
     * @return void
     */
    public function setState(?string $state): void;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void;

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @param string|null $phone
     * @return void
     */
    public function setPhone(?string $phone): void;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string|null $email
     * @return void
     */
    public function setEmail(?string $email): void;

    /**
     * @return string|null
     */
    public function getWebsite(): ?string;

    /**
     * @param string|null $website
     * @return void
     */
    public function setWebsite(?string $website): void;

    /**
     * @return string|null
     */
    public function getCategory(): ?string;

    /**
     * @param string|null $category
     * @return void
     */
    public function setCategory(?string $category): void;

    /**
     * @return string|null
     */
    public function getActionsSerialized(): ?string;

    /**
     * @param string|null $actionsSerialized
     * @return void
     */
    public function setActionsSerialized(?string $actionsSerialized): void;

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string;

    /**
     * @param string|null $storeImg
     * @return void
     */
    public function setStoreImg(?string $storeImg): void;

    /**
     * @return string|null
     */
    public function getStores(): ?string;

    /**
     * @param string|null $stores
     * @return void
     */
    public function setStores(?string $stores): void;

    /**
     * @return string|null
     */
    public function getSchedule(): ?string;

    /**
     * @param string|null $schedule
     * @return void
     */
    public function setSchedule(?string $schedule): void;

    /**
     * @return string|null
     */
    public function getMarkerImg(): ?string;

    /**
     * @param string|null $markerImg
     * @return void
     */
    public function setMarkerImg(?string $markerImg): void;

    /**
     * @return string|null
     */
    public function getShowSchedule(): ?string;

    /**
     * @param string|null $showSchedule
     * @return void
     */
    public function setShowSchedule(?string $showSchedule): void;

    /**
     * @return string|null
     */
    public function getUrlKey(): ?string;

    /**
     * @param string|null $urlKey
     * @return void
     */
    public function setUrlKey(?string $urlKey): void;

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string;

    /**
     * @param string|null $metaTitle
     * @return void
     */
    public function setMetaTitle(?string $metaTitle): void;

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * @param string|null $metaDescription
     * @return void
     */
    public function setMetaDescription(?string $metaDescription): void;

    /**
     * @return string|null
     */
    public function getMetaRobots(): ?string;

    /**
     * @param string|null $metaRobots
     * @return void
     */
    public function setMetaRobots(?string $metaRobots): void;

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string;

    /**
     * @param string|null $shortDescription
     * @return void
     */
    public function setShortDescription(?string $shortDescription): void;

    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string;

    /**
     * @param string|null $canonicalUrl
     * @return void
     */
    public function setCanonicalUrl(?string $canonicalUrl): void;

    /**
     * @return int
     */
    public function getConditionType(): int;

    /**
     * @param int $conditionType
     * @return void
     */
    public function setConditionType(int $conditionType): void;

    /**
     * @return bool
     */
    public function getCurbsideEnabled(): bool;

    /**
     * @param bool $curbsideEnabled
     * @return void
     */
    public function setCurbsideEnabled(bool $curbsideEnabled): void;

    /**
     * @return string|null
     */
    public function getCurbsideConditionsText(): ?string;

    /**
     * @param string|null $curbsideConditionsText
     * @return void
     */
    public function setCurbsideConditionsText(?string $curbsideConditionsText): void;
}
