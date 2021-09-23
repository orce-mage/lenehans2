<?php

namespace Searchanise\SearchAutocomplete\Test\Unit\Helper;

class ApiPagesTest extends \PHPUnit\Framework\TestCase
{
    const MIN_ID = '1';
    const MAX_ID = '9';

    protected $objectManager;
    protected $apiPages;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->apiPages = $this->objectManager->getObject(
            \Searchanise\SearchAutocomplete\Helper\ApiPages::class,
            [
            '_pageHelper' => $this->getPageHelper(),
            '_cmsResourceModelPageCollectionFactory' => $this->getCmsResourceModelPageCollectionFactory(),
            ]
        );
    }

    private function captureArg(&$arg)
    {
        return $this->callback(
            function ($argToMock) use (&$arg) {
                    $arg = $argToMock;
                    return true;
            }
        );
    }

    private function getCmsResourceModelPageCollectionFactory()
    {
        $collection = $this->getMockBuilder('Magento\Cms\Model\ResourceModel\Page\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'setPageSize', 'setOrder', 'toArray'])
            ->getMock();

        $collection->method('setPageSize')->with(1)->willReturnSelf();
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('load')->willReturnSelf();

        $collection->method('toArray')->will(
            $this->returnCallback(
                function ($object) {
                    static $sort = false;
                    $sort = !$sort;

                    return [[
                    'page_id' => $sort ? self::MIN_ID : self::MAX_ID
                    ]];
                }
            )
        );

        //$this->returnCallback($callback)

        for ($i = self::MIN_ID; $i <= self::MAX_ID; $i++) {
            $collection->addItem($this->getPage($i));
        }

        $collectionFactory = $this->getMockBuilder('Magento\Cms\Model\ResourceModel\Page\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);

        return $collectionFactory;
    }

    private function getPageHelper()
    {
        $pageHelper = $this->getMockBuilder(\Magento\Cms\Helper\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageHelper->method('getPageUrl')->will($this->returnValue('http://127.0.0.1/page.html'));

        return $pageHelper;
    }

    private function getPage($pageId = self::MIN_ID)
    {
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page->method('getId')->will($this->returnValue($pageId));
        $page->method('getTitle')->will($this->returnValue('Page title'));
        $page->method('getContent')->will($this->returnValue('Hello world'));

        return $page;
    }

    public function testGeneratePageFeed()
    {
        $page = $this->getPage(1);
        $item = $this->apiPages->generatePageFeed($page);

        $this->assertEquals(
            $item,
            [
            'id' => 1,
            'title' => 'Page title',
            'link' => 'http://127.0.0.1/page.html',
            'summary' => 'Hello world',
            ]
        );
    }

    public function testGetPages()
    {
        $pageIds = [];
        $pages = $this->apiPages->getPages([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        if (!empty($pages)) {
            foreach ($pages as $page) {
                $pageIds[] = $page->getId();
            }
        }

        $this->assertEquals($pageIds, [1, 2, 3, 4, 5, 6, 7, 8, 9]);
    }

    public function testMinMaxPageId()
    {
        $range = $this->apiPages->getMinMaxPageId();
        $this->assertEquals($range, [self::MIN_ID, self::MAX_ID]);
    }
}
