<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class AddProductControllerEventHandlerTest extends Unit
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
     * @var EchidnaEcommerceProductDataTransfer|MockObject
     */
    protected $EchidnaEcommerceProductDataTransferMock;

    /**
     * @var AddProductControllerEventHandler
     */
    protected $eventHandler;

    /**
     * @return void
     */
    public function testGetMethodName(): void
    {
        $this->assertEquals(AddProductControllerEventHandler::METHOD_NAME, $this->eventHandler->getMethodName());
    }

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->requestMock = $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionHandlerMock = $this->getMockBuilder(EchidnaEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->EchidnaEcommerceProductDataTransferMock = $this->getMockBuilder(EchidnaEcommerceProductDataTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHandler = new AddProductControllerEventHandler($this->sessionHandlerMock);
    }

    /**
     * @return void
     */
    public function testHandleSuccess(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'TEST_SKU'],
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 11],
            ]));

        $this->sessionHandlerMock->expects($this->once())
            ->method('addProduct');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleSuccessWithoutQuantity(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_SKU, null, 'TEST_SKU'],
            ]));

        $this->sessionHandlerMock->expects($this->once())
            ->method('addProduct');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }

    /**
     * @return void
     */
    public function testHandleFailureWithoutSKU(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, '11'],
            ]));

        $this->sessionHandlerMock->expects($this->never())
            ->method('addProduct');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }
}
