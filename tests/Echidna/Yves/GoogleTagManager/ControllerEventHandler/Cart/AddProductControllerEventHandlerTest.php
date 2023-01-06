<?php

namespace Echidna\Yves\GoogleTagManager\ControllerEventHandler\Cart;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Echidna\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class AddProductControllerEventHandlerTest extends Unit
{
    /**
     * @var EnhancedEcommerceSessionHandlerInterface|MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var Request|MockObject
     */
    protected $requestMock;

    /**
     * @var EnhancedEcommerceProductDataTransfer|MockObject
     */
    protected $enhancedEcommerceProductDataTransferMock;

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

        $this->sessionHandlerMock = $this->getMockBuilder(EnhancedEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->enhancedEcommerceProductDataTransferMock = $this->getMockBuilder(EnhancedEcommerceProductDataTransfer::class)
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
                [EnhancedEcommerceConstants::PRODUCT_FIELD_SKU, null, 'TEST_SKU'],
                [EnhancedEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, 11],
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
                [EnhancedEcommerceConstants::PRODUCT_FIELD_SKU, null, 'TEST_SKU'],
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
                [EnhancedEcommerceConstants::PRODUCT_FIELD_QUANTITY, null, '11'],
            ]));

        $this->sessionHandlerMock->expects($this->never())
            ->method('addProduct');

        $this->eventHandler->handle($this->requestMock, 'xx_XX');
    }
}
