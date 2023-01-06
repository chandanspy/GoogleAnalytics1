<?php

namespace Echidna\Yves\GoogleTagManager\Dependency\Client;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QuoteTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\Cart\CartClientInterface;

class GoogleTagManagerToCartClientBridgeTest extends Unit
{
    /**
     * @var GoogleTagManagerToCartClientBridge
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

        $this->bridge = new GoogleTagManagerToCartClientBridge($this->cartClientMock);
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
