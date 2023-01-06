<?php

namespace Echidna\Yves\GoogleTagManager\Model\EnhancedEcommerce;

use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface;
use Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class ProductArrayModel implements ProductModelBuilderInterface
{
    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface
     */
    protected $productMapper;

    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientInterface
     */
    protected $storageClient;

    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface
     */
    protected $cartClient;

    /**
     * @var \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig
     */
    protected $config;

    /**
     * @param \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface $cartClient
     * @param \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientInterface $storageClient
     * @param \Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface $productMapper
     * @param \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig $config
     */
    public function __construct(
        GoogleTagManagerToCartClientInterface $cartClient,
        GoogleTagManagerToProductStorageClientInterface $storageClient,
        EnhancedEcommerceProductMapperInterface $productMapper,
        GoogleTagManagerConfig $config
    ) {
        $this->productMapper = $productMapper;
        $this->storageClient = $storageClient;
        $this->cartClient = $cartClient;
        $this->config = $config;
    }

    /**
     * @param array $productsArray
     *
     * @return array
     */
    public function handle(array $productsArray): array
    {
        $products = [];

        foreach ($productsArray as $product) {
            if (!isset($product[EnhancedEcommerceConstants::PRODUCT_FIELD_SKU])) {
                continue;
            }

            if (!isset($product[EnhancedEcommerceConstants::PRODUCT_FIELD_QUANTITY])) {
                continue;
            }

            $itemTransfer = $this->getItemTransferFromQuote($product[EnhancedEcommerceConstants::PRODUCT_FIELD_SKU]);

            if ($itemTransfer === null) {
                continue;
            }

            if (!isset($product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID])) {
                $product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID] = $itemTransfer->getIdProductAbstract();
            }

            if (!isset($product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRICE])) {
                $product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRICE] = $itemTransfer->getUnitPrice();
            }

            $productDataAbstract = $this->storageClient
                ->findProductAbstractStorageData(
                    $product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID],
                    $this->config->getEnhancedEcommerceLocale()
                );

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($product[EnhancedEcommerceConstants::PRODUCT_FIELD_PRICE]);
            $productViewTransfer->setQuantity($product[EnhancedEcommerceConstants::PRODUCT_FIELD_QUANTITY]);

            $products[] = $this->productMapper->map($productViewTransfer)->toArray();
        }

        return $products;
    }

    /**
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|null
     */
    protected function getItemTransferFromQuote(string $sku): ?ItemTransfer
    {
        $quoteTransfer = $this->cartClient
            ->getQuote();

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getSku() === $sku) {
                return $itemTransfer;
            }
        }

        return null;
    }
}
