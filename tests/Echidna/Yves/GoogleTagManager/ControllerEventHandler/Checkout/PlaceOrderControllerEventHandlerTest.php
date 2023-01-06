<?php

namespace Echidna\Yves\GoogleTagManager\ControllerEventHandler\Checkout;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface;
use Echidna\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandlerInterface;

class PlaceOrderControllerEventHandlerTest extends Unit
{
    /**
     * @var EnhancedEcommerceSessionHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var GoogleTagManagerToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    protected $eventHandler;

    /**
     * @return void
     */
    protected function _before()
    {
        parent::_before();

        $this->sessionHandlerMock = $this->getMockBuilder(EnhancedEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartClientMock = $this->getMockBuilder(GoogleTagManagerToCartClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHandler = new PlaceOrderControllerEventHandler($this->sessionHandlerMock, $this->cartClientMock);
    }

    /**
     * @return void
     */
    public function testGetMethodName(): void
    {
        $this->assertEquals(PlaceOrderControllerEventHandler::METHOD_NAME, $this->eventHandler->getMethodName());
    }

    /**
     * @return void
     */
    public function handleSuccess(): void
    {
        $this->sessionHandlerMock->expects($this->atLeastOnce())
            ->method('getPurchase')
            ->willReturn([]);

        $this->sessionHandlerMock->expects($this->atLeastOnce())
            ->method('setPurchase')
            ->with(['shipment' => 0]);
    }
}
