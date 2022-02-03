<?php

namespace Amasty\Faq\Setup;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Faq\Model\ConfigProvider;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class InstallData implements InstallDataInterface
{
    /**
     * @var TemplateFactory
     */
    private $emailTemplate;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    public function __construct(
        TemplateFactory $emailTemplate,
        WriterInterface $configWriter,
        State $appState,
        TypeListInterface $typeList
    ) {
        $this->emailTemplate = $emailTemplate;
        $this->configWriter = $configWriter;
        $this->appState = $appState;
        $this->typeList = $typeList;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'saveAndSetEmails']);
    }

    /**
     * @return void
     */
    public function saveAndSetEmails()
    {
        $this->saveAndSetEmail(
            'Amasty FAQ: You received new question',
            'amastyfaq_admin_email_template',
            ConfigProvider::ADMIN_NOTIFY_EMAIL_TEMPLATE,
            Area::AREA_ADMINHTML
        );
        $this->saveAndSetEmail(
            'Amasty FAQ: Your question was answered',
            'amastyfaq_user_email_template',
            ConfigProvider::USER_NOTIFY_EMAIL_TEMPLATE
        );
        $this->typeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }

    /**
     * @param string $code
     * @param string $originalCode
     * @param string $configPath
     * @param string $area
     */
    private function saveAndSetEmail($code, $originalCode, $configPath, $area = Area::AREA_FRONTEND)
    {
        try {
            /** @var \Magento\Email\Model\Template $mailTemplate */
            $mailTemplate = $this->emailTemplate->create();

            $mailTemplate->setDesignConfig(
                ['area' => $area, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID]
            )->loadDefault(
                $originalCode
            )->setTemplateCode(
                $code
            )->setOrigTemplateCode(
                $originalCode
            )->setId(
                null
            )->save();

            $this->configWriter->save(ConfigProvider::PATH_PREFIX . $configPath, $mailTemplate->getId());
        } catch (\Exception $e) {
            null;
        }
    }
}
