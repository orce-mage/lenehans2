<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\Config\Attribute\Backend\UseConfig;

use Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\Lifetime;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @see Lifetime
 */
class LifetimeTest extends \PHPUnit\Framework\TestCase
{
    const ATTR_NAME = 'test_attr';

    /**
     * @var Lifetime
     */
    private $emailTemplate;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attribute;

    protected function setUp(): void
    {
        $this->emailTemplate = $this->createPartialMock(Lifetime::class, []);
        $this->attribute = $this->createPartialMock(AbstractAttribute::class, []);
        $this->attribute->setName(self::ATTR_NAME);
        $this->emailTemplate->setAttribute($this->attribute);
    }

    /**
     * @covers \Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\Lifetime::beforeSave
     *
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($attr, $useConfig, $expected)
    {
        $object = new DataObject();
        $object->setData(
            [
                self::ATTR_NAME => $attr,
                'use_config_' . self::ATTR_NAME => $useConfig
            ]
        );
        $this->emailTemplate->beforeSave($object);

        $this->assertEquals($expected, $object->getData(self::ATTR_NAME));
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            [//first assertion - without use config field
             'test',
             '',
             'test'
            ],
            [//second assertion - with use config field
             'test',
             '1',
             Attributes::ATTRIBUTE_CONFIG_VALUE
            ]
        ];
    }
}
