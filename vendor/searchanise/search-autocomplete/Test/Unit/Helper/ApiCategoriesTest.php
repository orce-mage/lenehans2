<?php

namespace Searchanise\SearchAutocomplete\Test\Unit\Helper;

class ApiCategoriesTest extends \PHPUnit\Framework\TestCase
{
    protected $objectManager;
    protected $apiCategories;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->apiCategories = $this->objectManager->getObject(\Searchanise\SearchAutocomplete\Helper\ApiCategories::class);
    }

    public function testGenerateCategoryFeed()
    {
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parent_category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parent_category->method('getId')->will($this->returnValue(1));
        $parent_category->method('getName')->will($this->returnValue('Parent category'));
        $parent_category->method('getUrl')->will($this->returnValue('http://127.0.0.1/parent.html'));
        $parent_category->method('getImageUrl')->will($this->returnValue('http://127.0.0.1/parent.jpg'));
        $parent_category->method('getIsActive')->will($this->returnValue(true));

        $category->method('getId')->will($this->returnValue(2));
        $category->method('getName')->will($this->returnValue('Test category'));
        $category->method('getUrl')->will($this->returnValue('http://127.0.0.1/category.html'));
        $category->method('getImageUrl')->will($this->returnValue('http://127.0.0.1/parent.jpg'));
        $category->method('getParentCategory')->will($this->returnValue($parent_category));
        $category->method('getIsActive')->will($this->returnValue(true));

        $item = $this->apiCategories->generateCategoryFeed($category);

        $this->assertEquals(
            $item,
            [
            'id' => 2,
            'parent_id' => 1,
            'title' => 'Test category',
            'link' => 'http://127.0.0.1/category.html',
            'image_link' => 'http://127.0.0.1/parent.jpg',
            'summary' => null,
            ]
        );
    }
}
