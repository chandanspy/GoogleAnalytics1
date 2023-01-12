<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Checkout;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;

class PlaceOrderControllerEventHandlerTest extends Unit
{
    /**
     * @var EchidnaEcommerceSessionHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    protected $eventHandler;

    /**
     * @return void
     */
    protected function _before()
    {
        parent::_before();

        $this->sessionHandlerMock = $this->getMockBuilder(EchidnaEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartClientMock = $this->getMockBuilder(GoogleAnalyticsToCartClientInterface::class)
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
