<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class ChangeQuantityProductControllerEventHandlerTest extends Unit
{
    /**
     * @var EchidnaEcommerceSessionHandlerInterface|MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var Request|MockObject
     */
    protected $requestMock;

    /**
     * @var ItemTransfer[]|MockObject[]
     */
    protected $itemTransferListMock;

    /**
     * @var ItemTransfer|MockObject
     */
    protected $itemTransferMock1;

    /**
     * @var QuoteTransfer|MockObject
     */
    protected $quoteTransferMock;

    /**
     * @var GoogleAnalyticsToCartClientInterface|MockObject
     */
    protected $cartClientMock;

    /**
     * @var ChangeQuantityProductControllerEventHandler
     */
    protected $eventHandler;

    /**
     * @var GoogleAnalyticsToProductStorageClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageClientMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->sessionHandlerMock = $this->getMockBuilder(EchidnaEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemTransferMock1 = $this->getMockBuilder(ItemTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemTransferMock2 = $this->getMockBuilder(ItemTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->quoteTransferMock = $this->getMockBuilder(QuoteTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartClientMock = $this->getMockBuilder(GoogleAnalyticsToCartClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHandler = new ChangeQuantityProductControllerEventHandler(
            $this->sessionHandlerMock,
            $this->cartClientMock
        );
    }

    /**
     * @return void
     */
    public function testGetMethodName(): void
    {
        $this->assertEquals(ChangeQuantityProductControllerEventHandler::METHOD_NAME, $this->eventHandler->getMethodName());
    }

    /**
     * @return void
     */
    public function testHandleSuccessIncreaseQuantity(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'SKU-111'],
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 99],
            ]));

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->sessionHandlerMock->expects($this->once())
            ->method('addProduct');

        $this->sessionHandlerMock->expects($this->never())
            ->method('removeProduct');

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->eventHandler, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->once())
            ->method('getIdProductAbstract')
            ->willReturn(666);

        $itemTransferMock->expects($this->once())
            ->method('getUnitPrice')
            ->willReturn(1234);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getQuantity')
            ->willReturn($this->itemTransferListMock[0]->getQuantity());

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleSuccessReduceQuantity(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'SKU-111'],
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 1],
            ]));

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->sessionHandlerMock->expects($this->never())
            ->method('addProduct');

        $this->sessionHandlerMock->expects($this->once())
            ->method('removeProduct');

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->eventHandler, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->once())
            ->method('getIdProductAbstract')
            ->willReturn(666);

        $itemTransferMock->expects($this->once())
            ->method('getUnitPrice')
            ->willReturn(1234);

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getQuantity')
            ->willReturn($this->itemTransferListMock[0]->getQuantity());

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureMissingSku(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 1],
            ]));

        $this->requestMock->expects($this->exactly(2))
            ->method('get');

        $this->cartClientMock->expects($this->never())
            ->willReturn($this->quoteTransferMock)
            ->method('getQuote');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureMissingQuantity(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'SKU-111'],
            ]));

        $this->cartClientMock->expects($this->never())
            ->willReturn($this->quoteTransferMock)
            ->method('getQuote');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureProductNotInQuote(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'SKU_NOT_IN_QUOTE'],
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 3],
            ]));

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->sessionHandlerMock->expects($this->never())
            ->method('addProduct');

        $this->sessionHandlerMock->expects($this->never())
            ->method('removeProduct');

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $result = $methodGetProductFromQuote->invokeArgs($this->eventHandler, ['SKU_NOT_IN_QUOTE']);
        $this->assertNull($result);

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected function getMethod(string $name)
    {
        $class = new ReflectionClass(ChangeQuantityProductControllerEventHandler::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
