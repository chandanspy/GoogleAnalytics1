<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig;
use Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductArrayModel;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use ReflectionClass;

class ProductArrayModelTest extends Unit
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageClientMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMapperMock;

    /**
     * @var \Generated\Shared\Transfer\ItemTransfer[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected $itemTransferListMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductArrayModel
     */
    protected $modelMapper;

    /**
     * @var \Generated\Shared\Transfer\QuoteTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteTransferMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @return void
     */
    protected function _before()
    {
        parent::_before();

        $this->configMock = $this->getMockBuilder(GoogleAnalyticsConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->method('getEchidnaEcommerceLocale')->willReturn('en_US');

        $this->cartClientMock = $this->getMockBuilder(GoogleAnalyticsToCartClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storageClientMock = $this->getMockBuilder(GoogleAnalyticsToProductStorageClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMapperMock = $this->getMockBuilder(EchidnaEcommerceProductMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemTransferMock1 = $this->getMockBuilder(ItemTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteTransferMock = $this->getMockBuilder(QuoteTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemTransferMock2 = $itemTransferMock1;

        $itemTransferMock1
            ->method('getSku')
            ->willReturn('SKU-111');

        $itemTransferMock1
            ->method('getQuantity')
            ->willReturn(3);

        $itemTransferMock2
            ->method('getSku')
            ->willReturn('SKU-222');

        $itemTransferMock2
            ->method('getQuantity')
            ->willReturn(3);

        $this->itemTransferListMock = [
            $itemTransferMock1,
            $itemTransferMock2,
        ];

        $this->quoteTransferMock
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->cartClientMock
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->modelMapper = new ProductArrayModel(
            $this->cartClientMock,
            $this->storageClientMock,
            $this->productMapperMock,
            $this->configMock
        );
    }

    /**
     * @return void
     */
    public function testHandleSuccessWithCompleteProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        $productViewTransferMappingArray = include codecept_data_dir('ProductViewTransferMappingArray.php');

        $methodGetProductFromQuote = $this->getMethod('getItemTransferFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->modelMapper, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $this->storageClientMock->expects($this->once())
            ->method('findProductAbstractStorageData')
            ->with($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID], 'en_US')
            ->willReturn($productViewTransferMappingArray);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getSku');

        $itemTransferMock->expects($this->never())
            ->method('getIdProductAbstract');

        $itemTransferMock->expects($this->never())
            ->method('getUnitPrice');

        $this->productMapperMock->expects($this->once())
            ->method('map');

        $result = $this->modelMapper->handle($productsArray);

        $this->assertNotCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleSuccessMissingAbstractIdInProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        $productViewTransferMappingArray = include codecept_data_dir('ProductViewTransferMappingArray.php');
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID]);

        $methodGetProductFromQuote = $this->getMethod('getItemTransferFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->modelMapper, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getSku');

        $itemTransferMock->expects($this->once())
            ->method('getIdProductAbstract')
            ->willReturn(666);

        $itemTransferMock->expects($this->never())
            ->method('getUnitPrice');

        $this->storageClientMock->expects($this->once())
            ->method('findProductAbstractStorageData')
            ->with(666, 'en_US')
            ->willReturn($productViewTransferMappingArray);

        $this->productMapperMock->expects($this->once())
            ->method('map');

        $result = $this->modelMapper->handle($productsArray);

        $this->assertNotCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleSuccessMissingPriceInProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        $productViewTransferMappingArray = include codecept_data_dir('ProductViewTransferMappingArray.php');
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE]);

        $methodGetProductFromQuote = $this->getMethod('getItemTransferFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->modelMapper, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getSku');

        $itemTransferMock->expects($this->never())
            ->method('getIdProductAbstract');

        $itemTransferMock->expects($this->once())
            ->method('getUnitPrice')
            ->willReturn(1111);

        $this->storageClientMock->expects($this->once())
            ->method('findProductAbstractStorageData')
            ->with(666, 'en_US')
            ->willReturn($productViewTransferMappingArray);

        $this->productMapperMock->expects($this->once())
            ->method('map');

        $result = $this->modelMapper->handle($productsArray);

        $this->assertNotCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleSuccessMissingProductIdAndPriceInProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        $productViewTransferMappingArray = include codecept_data_dir('ProductViewTransferMappingArray.php');
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID]);
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE]);

        $methodGetProductFromQuote = $this->getMethod('getItemTransferFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->modelMapper, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getSku');

        $itemTransferMock->expects($this->once())
            ->method('getIdProductAbstract')
            ->willReturn(666);

        $itemTransferMock->expects($this->once())
            ->method('getUnitPrice')
            ->willReturn(1111);

        $this->storageClientMock->expects($this->once())
            ->method('findProductAbstractStorageData')
            ->with(666, 'en_US')
            ->willReturn($productViewTransferMappingArray);

        $this->productMapperMock->expects($this->once())
            ->method('map');

        $result = $this->modelMapper->handle($productsArray);

        $this->assertNotCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleFailureMissingSkuInProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_SKU]);

        $result = $this->modelMapper->handle($productsArray);

        $this->assertCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleFailureMissingQuantityInProductArray(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        unset($productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY]);

        $result = $this->modelMapper->handle($productsArray);

        $this->assertCount(0, $result);
    }

    /**
     * @return void
     */
    public function testHandleFailureProductNotInQuote(): void
    {
        $productsArray = include codecept_data_dir('ProductsArray.php');
        $productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_SKU] = 'SKU_NOT_IN_QUOTE';

        $methodGetProductFromQuote = $this->getMethod('getItemTransferFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs(
            $this->modelMapper,
            [$productsArray[0][EchidnaEcommerceConstants::PRODUCT_FIELD_SKU]]
        );
        $this->assertNull($itemTransferMock);

        $result = $this->modelMapper->handle($productsArray);

        $this->assertCount(0, $result);
    }

    /**
     * @param string $name
     *
     * @throws
     *
     * @return \ReflectionMethod
     */
    protected function getMethod(string $name)
    {
        $class = new ReflectionClass(ProductArrayModel::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
