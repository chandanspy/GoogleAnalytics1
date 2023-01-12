<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class RemoveProductControllerEventHandlerTest extends Unit
{
    /**
     * @var EchidnaEcommerceSessionHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var ItemTransfer[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected $itemTransferListMock;

    /**
     * @var ItemTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemTransferMock1;

    /**
     * @var QuoteTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteTransferMock;

    /**
     * @var GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    /**
     * @var RemoveProductControllerEventHandler
     */
    protected $eventHandler;

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

        $this->eventHandler = new RemoveProductControllerEventHandler(
            $this->sessionHandlerMock,
            $this->cartClientMock
        );
    }

    /**
     * @return void
     */
    public function testHandleSuccess(): void
    {
        $this->requestMock->expects($this->once())
            ->method('get')
            ->willReturn('SKU-111');

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $itemTransferMock = $methodGetProductFromQuote->invokeArgs($this->eventHandler, ['SKU-111']);
        $this->assertEquals($this->itemTransferListMock[0], $itemTransferMock);

        $itemTransferMock->expects($this->once())
            ->method('getIdProductAbstract');

        $itemTransferMock->expects($this->once())
            ->method('getUnitPrice');

        $itemTransferMock->expects($this->atLeastOnce())
            ->method('getQuantity')
            ->willReturn($this->itemTransferListMock[0]->getQuantity());

        $this->sessionHandlerMock->expects($this->once())
            ->method('removeProduct');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureSkuMissing(): void
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, ''],
            ]));

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->sessionHandlerMock->expects($this->never())
            ->method('removeProduct');

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $result = $methodGetProductFromQuote->invokeArgs($this->eventHandler, [
            $this->requestMock->get(EchidnaEcommerceConstants::PRODUCT_FIELD_SKU),
        ]);
        $this->assertNull($result);

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureProductNotInQuote()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'SKU_NOT_IN_QUOTE'],
            ]));

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->sessionHandlerMock->expects($this->never())
            ->method('removeProduct');

        $methodGetProductFromQuote = $this->getMethod('getProductFromQuote');
        $result = $methodGetProductFromQuote->invokeArgs($this->eventHandler, [
            $this->requestMock->get(EchidnaEcommerceConstants::PRODUCT_FIELD_SKU),
        ]);
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
        $class = new ReflectionClass(RemoveProductControllerEventHandler::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
