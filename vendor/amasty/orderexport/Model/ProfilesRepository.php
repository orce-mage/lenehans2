<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model;

use Amasty\Orderexport\Model\ProfilesFactory as ProfilesFactory;
use Amasty\Orderexport\Model\ResourceModel\Profiles as ProfilesResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProfilesRepository
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ProfilesResource
     */
    private $profilesResource;

    /**
     * @var ProfilesFactory
     */
    private $profilesFactory;

    public function __construct(
        TimezoneInterface $timezone,
        ProfilesResource $profilesResource,
        ProfilesFactory $profilesFactory
    ) {
        $this->timezone = $timezone;
        $this->profilesResource = $profilesResource;
        $this->profilesFactory = $profilesFactory;
    }

    /**
     * @return Profiles
     */
    public function create()
    {
        return $this->profilesFactory->create();
    }

    /**
     * @param $profileId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($profileId)
    {
        $profile = $this->profilesFactory->create();
        $this->profilesResource->load($profile, $profileId);

        if (!$profile->getId()) {
            throw new NoSuchEntityException(__('Profile with id "%1" does not exist.', $profileId));
        }

        return $profile;
    }

    /**
     * @param Profiles $profile
     * @return Profiles
     * @throws CouldNotSaveException
     */
    public function save(Profiles $profile)
    {
        try {
            $this->profilesResource->save($profile);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $profile;
    }

    /**
     * @param Profiles $profile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Profiles $profile)
    {
        try {
            $this->profilesResource->delete($profile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the profile with id %1: %2',
                $profile->getId(),
                $exception->getMessage()
            ));
        }

        return true;
    }
}
