<?php


namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Symfony\Component\HttpFoundation\Request;

class ChangeQuantityProductControllerEventHandler implements ControllerEventHandlerInterface
{
    public const METHOD_NAME = 'changeAction';

    /**
     * @var EchidnaEcommerceSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @var GoogleAnalyticsToCartClientInterface
     */
    protected $cartClient;

    /**
     * @param EchidnaEcommerceSessionHandlerInterface $sessionHandler
     * @param GoogleAnalyticsToCartClientInterface $cartClient
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
        $sku = $request->get(EchidnaEcommerceConstants::PRODUCT_FIELD_SKU);
        $quantity = $request->get(EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY);

        if (!$sku || !$quantity) {
            return;
        }

        $itemTransfer = $this->getProductFromQuote($sku);

        if ($itemTransfer === null) {
            return;
        }

        if ((int)$quantity === $itemTransfer->getQuantity()) {
            return;
        }

        $EchidnaEcommerceProductData = new EchidnaEcommerceProductDataTransfer();
        $EchidnaEcommerceProductData->setProductAbstractId($itemTransfer->getIdProductAbstract());
        $EchidnaEcommerceProductData->setSku($sku);
        $EchidnaEcommerceProductData->setPrice($itemTransfer->getUnitPrice());

        if ($quantity > $itemTransfer->getQuantity()) {
            $EchidnaEcommerceProductData->setQuantity($quantity - $itemTransfer->getQuantity());

            $this->sessionHandler->addProduct($EchidnaEcommerceProductData);

            return;
        }

        if ($quantity < $itemTransfer->getQuantity()) {
            $EchidnaEcommerceProductData->setQuantity($itemTransfer->getQuantity() - $quantity);

            $this->sessionHandler->removeProduct($EchidnaEcommerceProductData);

            return;
        }
    }

    /**
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|null
     */
    protected function getProductFromQuote(string $sku): ?ItemTransfer
    {
        $quoteTransfer = $this->cartClient->getQuote();

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getSku() === $sku) {
                return $itemTransfer;
            }
        }

        return null;
    }
}
