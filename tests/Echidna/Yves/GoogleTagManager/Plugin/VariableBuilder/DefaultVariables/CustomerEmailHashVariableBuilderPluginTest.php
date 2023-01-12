<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class CustomerEmailHashVariableBuilderPluginTest extends Unit
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factoryMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\CurrencyVariableBuilderPlugin
     */
    protected $plugin;

    /**
     * @var \Generated\Shared\Transfer\QuoteTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteTransferMock;

    /**
     * @var \Generated\Shared\Transfer\AddressesTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $billingAddressTransfer;

    /**
     * @return void
     */
    protected function _before()
    {
        $this->factoryMock = $this->createMock(GoogleAnalyticsFactory::class);
        $this->cartClientMock = $this->createMock(GoogleAnalyticsToCartClientInterface::class);
        $this->quoteTransferMock = $this->createMock(QuoteTransfer::class);
        $this->billingAddressTransfer = $this->createMock(AddressTransfer::class);

        $this->plugin = new CustomerEmailHashVariableBuilderPlugin();
        $this->plugin->setFactory($this->factoryMock);
    }

    /**
     * @return void
     */
    public function testHandleSuccess(): void
    {
        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getCartClient')
            ->willReturn($this->cartClientMock);

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressTransfer);

        $this->billingAddressTransfer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn('john.doe@mailinator.com');

        $array = $this->plugin->handle([]);

        $this->assertArrayHasKey('externalIdHash', $array);
    }

    /**
     * @return void
     */
    public function testHandleSuccessFailureNoBillingAddressInQuote(): void
    {
        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getCartClient')
            ->willReturn($this->cartClientMock);

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn(null);

        $array = $this->plugin->handle([]);

        $this->assertArrayNotHasKey('externalIdHash', $array);
        $this->assertCount(0, $array);
    }

    /**
     * @return void
     */
    public function testHandleSuccessFailureNoEmailAddress(): void
    {
        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getCartClient')
            ->willReturn($this->cartClientMock);

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressTransfer);

        $this->billingAddressTransfer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn(null);

        $array = $this->plugin->handle([]);

        $this->assertArrayNotHasKey('externalIdHash', $array);
        $this->assertCount(0, $array);
    }
}
