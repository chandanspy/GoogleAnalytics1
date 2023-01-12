<?php

namespace Echidna\Yves\GoogleAnalytics\Dependency\Client;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QuoteTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\Cart\CartClientInterface;

class GoogleAnalyticsToCartClientBridgeTest extends Unit
{
    /**
     * @var GoogleAnalyticsToCartClientBridge
     */
    protected $bridge;

    /**
     * @var CartClientInterface|MockObject
     */
    protected $cartClientMock;

    /**
     * @var QuoteTransfer|MockObject
     */
    protected $quoteTransferMock;

    /**
     * @return void
     */
    protected function _before()
    {
        $this->cartClientMock = $this->getMockBuilder(CartClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteTransferMock = $this->getMockBuilder(QuoteTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bridge = new GoogleAnalyticsToCartClientBridge($this->cartClientMock);
    }

    /**
     * @return void
     */
    public function testGetQuoteSuccess(): void
    {
        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->bridge->getQuote();
    }
}
