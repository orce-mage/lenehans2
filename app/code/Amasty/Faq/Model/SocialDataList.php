<?php

namespace Amasty\Faq\Model;

class SocialDataList
{
    /**
     * @var SocialData[]
     */
    private $socialList;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SocialData[]|null
     */
    private $activeButtons = null;

    public function __construct(ConfigProvider $configProvider, $socialList = [])
    {
        $this->configProvider = $configProvider;
        $this->socialList = $socialList;
    }

    /**
     * @return SocialData[]
     */
    public function getSocialList()
    {
        return $this->socialList;
    }

    /**
     * @param SocialData $socialButtonData
     *
     * @return $this
     */
    public function addSocialList(SocialData $socialButtonData)
    {
        $this->socialList[] = $socialButtonData;

        return $this;
    }

    /**
     * @return array
     */
    public function getActiveSocials()
    {
        if ($this->activeButtons === null) {
            $config = $this->configProvider->getSocialActiveButtons();
            $allButtons = $this->getSocialList();
            $this->activeButtons = [];
            if (!empty($config)) {
                foreach ($allButtons as $button) {
                    if (in_array($button->getCode(), $config)) {
                        $this->activeButtons[] = $button;
                    }
                }
            }
        }

        return $this->activeButtons;
    }
}
