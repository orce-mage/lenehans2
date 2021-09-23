<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Test\Unit\Model\Import\Validator;

use Amasty\Storelocator\Model\Import\Validator\Country;
use Amasty\Storelocator\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CountryTest
 *
 * @see Country
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CountryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const SOME_COUNTRY = [
        'country' => [
            'label' => 'someCountry',
            'value' => 'test'
        ]
    ];
    const SOME_COUNTRY_TWO = [
        'country' => [
            'label' => 'someCountryTwo',
            'value' => 'testTwo'
        ]
    ];
    const SOME_COUNTRY_THREE = [
        'country' => [
            'label' => 'someCountryThree',
            'value' => 'testThree'
        ]
    ];
    const SOME_COUNTRY_FOUR = [
        'country' => [
            'label' => 'someCountryFour',
            'value' => 'testFour'
        ]
    ];

    /**
     * @var \Magento\Directory\Model\Config\Source\Country|MockObject
     */
    private $configCountry;

    /**
     * @var Country|MockObject
     */
    private $model;

    public function setUp(): void
    {
        $this->configCountry = $this->getMockBuilder(\Magento\Directory\Model\Config\Source\Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();
        $this->configCountry->expects($this->any())->method('toOptionArray')
            ->willReturn(self::SOME_COUNTRY);

        $this->model = $this->getObjectManager()->getObject(
            Country::class,
            [
                'configCountry' => $this->configCountry
            ]
        );
    }

    /**
     * @covers Country::isValid
     *
     * @dataProvider isValidDataProvider
     *
     * @param bool $expectedResult
     * @param array $country
     *
     * @throws \ReflectionException
     */
    public function testIsValid($expectedResult, $country)
    {
        $result = $this->model->isValid($country);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers Country::getCountryByName
     *
     * @dataProvider getCountryByNameDataProvider
     *
     * @param string|array $country
     * @param string $expectedResult
     *
     * @throws \ReflectionException
     */
    public function testGetCountryByName($country, $expectedResult = '')
    {
        $result = $this->model->getCountryByName($country);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for getCountryByName test
     * @return array
     */
    public function getCountryByNameDataProvider()
    {
        return [
            ['someCountry', 'test'],
            ['country']
        ];
    }

    /**
     * Data provider for isValid test
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [true, self::SOME_COUNTRY],
            [false, self::SOME_COUNTRY_TWO],
            [false, self::SOME_COUNTRY_THREE],
            [false, self::SOME_COUNTRY_FOUR]
        ];
    }
}
