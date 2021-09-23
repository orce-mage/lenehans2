<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Model\Image\ResourceModel\Image;
use Amasty\GiftCard\Model\Image\ResourceModel\ImageBakingInfo;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateDataTo250
{
    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $imageTable = $setup->getTable(Image::TABLE_NAME);
        $imageBackingInfo = $setup->getTable(ImageBakingInfo::TABLE_NAME);

        $selectUpdate = $connection->select()
            ->from(
                $imageTable,
                [
                    new \Zend_Db_Expr('NULL as info_id'),
                    'image_id' => 'image_id',
                    new \Zend_Db_Expr('1 as is_enabled'),
                    new \Zend_Db_Expr('\'code\' as name'),
                    'pos_x' => 'code_pos_x',
                    'pos_y' => 'code_pos_y',
                    'text_color' => 'code_text_color'
                ]
            );
        $connection->query($connection->insertFromSelect($selectUpdate, $imageBackingInfo));

        $connection->dropColumn($imageTable, 'code_pos_x');
        $connection->dropColumn($imageTable, 'code_pos_y');
        $connection->dropColumn($imageTable, 'code_text_color');

        $setup->endSetup();
    }
}
