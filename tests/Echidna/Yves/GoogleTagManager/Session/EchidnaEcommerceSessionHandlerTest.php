<?php

namespace Echidna\Yves\GoogleAnalytics\Session;

use Codeception\Test\Unit;
use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;

class EchidnaEcommerceSessionHandlerTest extends Unit
{
    /**
     * @return void
     */
    public function testGetChangeProductQuantityEventArraySuccessWithoutRemovingDataFromSession(): void
    {
        $sessionClientMock = $this->getSessionClientMock();
        $cartClientMock = $this->getCartClientMock();
        $productMapperMock = $this->getProductMapperMock();

        $sessionClientMock->method('get')
            ->willReturn([
                [EchidnaEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY, null, []],
            ]);

        $EchidnaEcommerceSessionHandler = new EchidnaEcommerceSessionHandler(
            $sessionClientMock,
            $cartClientMock,
            $productMapperMock
        );

        $sessionClientMock->expects($this->exactly(2))->method('get');
        $sessionClientMock->expects($this->never())->method('remove');

        $EchidnaEcommerceSessionHandler->getChangeProductQuantityEventArray();
    }

    /**
     * @return void
     */
    public function testGetChangeProductQuantityEventArraySuccessRemovingDataFromSession(): void
    {
        $sessionClientMock = $this->getSessionClientMock();
        $cartClientMock = $this->getCartClientMock();
        $productMapperMock = $this->getProductMapperMock();

        $sessionClientMock->method('get')
            ->willReturn([
                [EchidnaEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY, null, []],
            ]);

        $EchidnaEcommerceSessionHandler = new EchidnaEcommerceSessionHandler(
            $sessionClientMock,
            $cartClientMock,
            $productMapperMock
        );

        $sessionClientMock->expects($this->exactly(2))->method('get');
        $sessionClientMock->expects($this->once())->method('remove');

        $EchidnaEcommerceSessionHandler->getChangeProductQuantityEventArray(true);
    }

    /**
     * @return void
     */
    public function testGetChangeProductQuantityEventArrayFailureNoDataInSession(): void
    {
        $sessionClientMock = $this->getSessionClientMock();
        $cartClientMock = $this->getCartClientMock();
        $productMapperMock = $this->getProductMapperMock();

        $EchidnaEcommerceSessionHandler = new EchidnaEcommerceSessionHandler(
            $sessionClientMock,
            $cartClientMock,
            $productMapperMock
        );

        $sessionClientMock->expects($this->once())->method('get');

        $EchidnaEcommerceSessionHandler->getChangeProductQuantityEventArray();
    }

    /**
     * @return void
     */
    public function testGetAddedProductsSuccess(): void
    {
        $sessionClientMock = $this->getSessionClientMock();
        $cartClientMock = $this->getCartClientMock();
        $productMapperMock = $this->getProductMapperMock();

        $EchidnaEcommerceSessionHandler = new EchidnaEcommerceSessionHandler(
            $sessionClientMock,
            $cartClientMock,
            $productMapperMock
        );

        $sessionClientMock->expects($this->once())->method('get');

        $EchidnaEcommerceSessionHandler->getAddedProducts();
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductMapperMock()
    {
        $productMapperMock = $this->createMock(EchidnaEcommerceProductMapperInterface::class);

        $productMapperMock->method('map');

        return $productMapperMock;
    }

    /**
     * @param QuoteTransfer|null $quoteTransferMock
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCartClientMock()
    {
        $cartClientMock = $this->createMock(GoogleAnalyticsToCartClientInterface::class);

        return $cartClientMock;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteTransferMock(?ItemTransfer $itemTransfer)
    {
        $quoteTransferMock = $this->createMock(QuoteTransfer::class);

        if ($itemTransfer !== null) {
            $quoteTransferMock->method('getItems')
                ->willReturn([$itemTransfer]);

            return $quoteTransferMock;
        }

        $quoteTransferMock->method('getItems')
            ->willReturn([]);

        return $quoteTransferMock;
    }

    /**
     * @return \Generated\Shared\Transfer\ItemTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getItemTransferMock()
    {
        $itemTransferMock = $this->getMockBuilder(ItemTransfer::class)
            ->setMethods(['getIdProductAbstract', 'getUnitPrice', 'getSku'])
            ->getMock();

        $itemTransferMock->method('getSku')->willReturn('TEST_SKU');
        $itemTransferMock->method('getIdProductAbstract')->willReturn(666);
        $itemTransferMock->method('getUnitPrice')->willReturn(1111);

        return $itemTransferMock;
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSessionClientMock()
    {
        $sessionClientMock = $this->getMockBuilder(GoogleAnalyticsToSessionClientInterface::class)
            ->setMethods(['getId', 'get', 'set', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();

        return $sessionClientMock;
    }
}
