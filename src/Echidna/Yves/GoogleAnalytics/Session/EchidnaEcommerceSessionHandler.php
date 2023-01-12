<?php

namespace Echidna\Yves\GoogleAnalytics\Session;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer;

class EchidnaEcommerceSessionHandler implements EchidnaEcommerceSessionHandlerInterface
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface
     */
    protected $sessionClient;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface
     */
    protected $productMapper;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientBridge
     */
    protected $cartClient;

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface $sessionClient
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientBridge $cartClient
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface $productMapper
     */
    public function __construct(
        GoogleAnalyticsToSessionClientInterface $sessionClient,
        GoogleAnalyticsToCartClientInterface $cartClient,
        EchidnaEcommerceProductMapperInterface $productMapper
    ) {
        $this->sessionClient = $sessionClient;
        $this->productMapper = $productMapper;
        $this->cartClient = $cartClient;
    }

    /**
     * @param bool $removeFromSessionAfterOutput
     *
     * @return array
     */
    public function getChangeProductQuantityEventArray(bool $removeFromSessionAfterOutput = false): array
    {
        if (!\is_array($this->sessionClient->get(EchidnaEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY))) {
            return [];
        }

        $eventArray = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY);

        if ($removeFromSessionAfterOutput === true) {
            $this->sessionClient->remove(EchidnaEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY);
        }

        return $eventArray;
    }

    /**
     * @return \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer[]
     */
    public function getAddedProducts($removeFromSession = false): array
    {
        $addedProducts = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS);

        if (!\is_array($addedProducts)) {
            return [];
        }

        if ($removeFromSession === true) {
            $this->sessionClient->remove(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS);
        }

        return $addedProducts;
    }

    /**
     * @param bool $removeFromSession
     *
     * @return array
     */
    public function getRemovedProducts($removeFromSession = false): array
    {
        $removedProducts = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_REMOVED_PRODUCTS);

        if (!\is_array($removedProducts)) {
            return [];
        }

        if ($removeFromSession === true) {
            $this->sessionClient->remove(EchidnaEcommerceConstants::SESSION_REMOVED_PRODUCTS);
        }

        return $removedProducts;
    }

    /**
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer|null
     */
    public function getProduct(string $sku): ?EchidnaEcommerceProductDataTransfer
    {
        $addedProducts = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS);

        if (!\is_array($addedProducts)) {
            return null;
        }

        if (!\array_key_exists($sku, $addedProducts)) {
            return null;
        }

        return $addedProducts[$sku] = (new EchidnaEcommerceProductDataTransfer)->fromArray($addedProducts[$sku]);
    }

    /**
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function addProduct(EchidnaEcommerceProductDataTransfer $productDataTransfer): void
    {
        $addedProducts = $this->getAddedProducts();
        $existingProduct = $this->getProduct($productDataTransfer->getSku());

        if ($existingProduct !== null) {
            $quantity = $existingProduct->getQuantity() + $productDataTransfer->getQuantity();
            $existingProduct->setQuantity($quantity);

            $addedProducts[$productDataTransfer->getSku()] = $existingProduct->toArray();

            $this->sessionClient->set(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS, $addedProducts);

            return;
        }

        $addedProducts[$productDataTransfer->getSku()] = $productDataTransfer->toArray();

        $this->sessionClient->set(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS, $addedProducts);

        return;
    }

    /**
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function removeProduct(EchidnaEcommerceProductDataTransfer $productDataTransfer): void
    {
        $removedProduct[$productDataTransfer->getSku()] = $productDataTransfer->toArray();

        $this->sessionClient->set(EchidnaEcommerceConstants::SESSION_REMOVED_PRODUCTS, $removedProduct);
    }

    /**
     * @param string $sku
     * @param int $quanity
     *
     * @return void
     */
    public function changeProductQuantity(EchidnaEcommerceProductDataTransfer $ecommerceProductDataTransfer): void
    {
        $addedProducts = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_ADDED_PRODUCTS);

        if (!\is_array($addedProducts) || \count($addedProducts) === 0) {
            return;
        }

        if (!\array_key_exists($ecommerceProductDataTransfer->getSku(), $addedProducts)) {
            return;
        }

        $addedProducts[$ecommerceProductDataTransfer->getSku()]['quantity'] += $ecommerceProductDataTransfer->getQuantity();
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function setPurchase(array $params): void
    {
        $this->sessionClient->set(EchidnaEcommerceConstants::SESSION_PURCHASE, $params);
    }

    /**
     * @param bool $removeFromSessionAfterOutput
     *
     * @return array
     */
    public function getPurchase($removeFromSessionAfterOutput = false): array
    {
        $purchaseArray = $this->sessionClient->get(EchidnaEcommerceConstants::SESSION_PURCHASE);

        if (!\is_array($purchaseArray)) {
            return [];
        }

        if ($removeFromSessionAfterOutput === true) {
            $this->sessionClient->remove(EchidnaEcommerceConstants::SESSION_PURCHASE);
        }

        return $purchaseArray;
    }
}
