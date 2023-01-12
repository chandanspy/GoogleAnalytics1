<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer;
use Symfony\Component\HttpFoundation\Request;

class AddProductControllerEventHandler implements ControllerEventHandlerInterface
{
    public const METHOD_NAME = 'addAction';

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface $sessionHandler
     */
    public function __construct(EchidnaEcommerceSessionHandlerInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
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
        $sku = $request->get(EchidnaEcommerceConstants::PRODUCT_FIELD_SKU);
        $quantity = $request->get(EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY);

        if (!$sku) {
            return;
        }

        if (!$quantity) {
            $quantity = 1;
        }

        $EchidnaEcommerceProductData = new EchidnaEcommerceProductDataTransfer();
        $EchidnaEcommerceProductData->setSku($sku);
        $EchidnaEcommerceProductData->setQuantity($quantity);

        $this->sessionHandler->addProduct($EchidnaEcommerceProductData);
    }
}
