<?php

namespace Echidna\Yves\GoogleTagManager\Session;

use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface;
use Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer;

class EnhancedEcommerceSessionHandler implements EnhancedEcommerceSessionHandlerInterface
{
    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface
     */
    protected $sessionClient;

    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface
     */
    protected $productMapper;

    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientBridge
     */
    protected $cartClient;

    /**
     * @param \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface $sessionClient
     * @param \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientBridge $cartClient
     * @param \Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface $productMapper
     */
    public function __construct(
        GoogleTagManagerToSessionClientInterface $sessionClient,
        GoogleTagManagerToCartClientInterface $cartClient,
        EnhancedEcommerceProductMapperInterface $productMapper
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
        if (!\is_array($this->sessionClient->get(EnhancedEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY))) {
            return [];
        }

        $eventArray = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY);

        if ($removeFromSessionAfterOutput === true) {
            $this->sessionClient->remove(EnhancedEcommerceConstants::SESSION_REMOVED_CHANGED_QUANTITY);
        }

        return $eventArray;
    }

    /**
     * @return \Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer[]
     */
    public function getAddedProducts($removeFromSession = false): array
    {
        $addedProducts = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS);

        if (!\is_array($addedProducts)) {
            return [];
        }

        if ($removeFromSession === true) {
            $this->sessionClient->remove(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS);
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
        $removedProducts = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_REMOVED_PRODUCTS);

        if (!\is_array($removedProducts)) {
            return [];
        }

        if ($removeFromSession === true) {
            $this->sessionClient->remove(EnhancedEcommerceConstants::SESSION_REMOVED_PRODUCTS);
        }

        return $removedProducts;
    }

    /**
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer|null
     */
    public function getProduct(string $sku): ?EnhancedEcommerceProductDataTransfer
    {
        $addedProducts = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS);

        if (!\is_array($addedProducts)) {
            return null;
        }

        if (!\array_key_exists($sku, $addedProducts)) {
            return null;
        }

        return $addedProducts[$sku] = (new EnhancedEcommerceProductDataTransfer)->fromArray($addedProducts[$sku]);
    }

    /**
     * @param \Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function addProduct(EnhancedEcommerceProductDataTransfer $productDataTransfer): void
    {
        $addedProducts = $this->getAddedProducts();
        $existingProduct = $this->getProduct($productDataTransfer->getSku());

        if ($existingProduct !== null) {
            $quantity = $existingProduct->getQuantity() + $productDataTransfer->getQuantity();
            $existingProduct->setQuantity($quantity);

            $addedProducts[$productDataTransfer->getSku()] = $existingProduct->toArray();

            $this->sessionClient->set(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS, $addedProducts);

            return;
        }

        $addedProducts[$productDataTransfer->getSku()] = $productDataTransfer->toArray();

        $this->sessionClient->set(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS, $addedProducts);

        return;
    }

    /**
     * @param \Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function removeProduct(EnhancedEcommerceProductDataTransfer $productDataTransfer): void
    {
        $removedProduct[$productDataTransfer->getSku()] = $productDataTransfer->toArray();

        $this->sessionClient->set(EnhancedEcommerceConstants::SESSION_REMOVED_PRODUCTS, $removedProduct);
    }

    /**
     * @param string $sku
     * @param int $quanity
     *
     * @return void
     */
    public function changeProductQuantity(EnhancedEcommerceProductDataTransfer $ecommerceProductDataTransfer): void
    {
        $addedProducts = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_ADDED_PRODUCTS);

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
        $this->sessionClient->set(EnhancedEcommerceConstants::SESSION_PURCHASE, $params);
    }

    /**
     * @param bool $removeFromSessionAfterOutput
     *
     * @return array
     */
    public function getPurchase($removeFromSessionAfterOutput = false): array
    {
        $purchaseArray = $this->sessionClient->get(EnhancedEcommerceConstants::SESSION_PURCHASE);

        if (!\is_array($purchaseArray)) {
            return [];
        }

        if ($removeFromSessionAfterOutput === true) {
            $this->sessionClient->remove(EnhancedEcommerceConstants::SESSION_PURCHASE);
        }

        return $purchaseArray;
    }
}
