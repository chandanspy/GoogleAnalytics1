<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Checkout;

use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class PlaceOrderControllerEventHandler implements ControllerEventHandlerInterface
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface
     */
    protected $cartClient;

    public const METHOD_NAME = 'placeOrderAction';

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface $sessionHandler
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface $cartClient
     */
    public function __construct(
        EchidnaEcommerceSessionHandlerInterface $sessionHandler,
        GoogleAnalyticsToCartClientInterface $cartClient
    ) {
        $this->sessionHandler = $sessionHandler;
        $this->cartClient = $cartClient;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return static::METHOD_NAME;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $locale
     *
     * @return void
     */
    public function handle(Request $request, ?string $locale): void
    {
        $purchase = $this->sessionHandler->getPurchase();

        $this->sessionHandler->setPurchase(\array_merge(
            $purchase,
            ['shipment' => $this->getShippingCost()]
        ));
    }

    /**
     * @return int
     */
    protected function getShippingCost(): int
    {
        $quoteTransfer = $this->cartClient->getQuote();

        if ($quoteTransfer->getShipment() === null) {
            return 0;
        }

        if (!$quoteTransfer->getShipment()->getMethod() === null) {
            return 0;
        }

        if ($quoteTransfer->getShipment()->getMethod()->getStoreCurrencyPrice() === null) {
            return 0;
        }

        return $quoteTransfer->getShipment()->getMethod()->getStoreCurrencyPrice();
    }
}
