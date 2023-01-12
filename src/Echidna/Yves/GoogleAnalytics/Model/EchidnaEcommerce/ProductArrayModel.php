<?php

namespace Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class ProductArrayModel implements ProductModelBuilderInterface
{
    /**
     * @var EchidnaEcommerceProductMapperInterface
     */
    protected $productMapper;

    /**
     * @var GoogleAnalyticsToProductStorageClientInterface
     */
    protected $storageClient;

    /**
     * @var GoogleAnalyticsToCartClientInterface
     */
    protected $cartClient;

    /**
     * @var GoogleAnalyticsConfig
     */
    protected $config;

    /**
     * @param GoogleAnalyticsToCartClientInterface $cartClient
     * @param GoogleAnalyticsToProductStorageClientInterface $storageClient
     * @param EchidnaEcommerceProductMapperInterface $productMapper
     * @param GoogleAnalyticsConfig $config
     */
    public function __construct(
        GoogleAnalyticsToCartClientInterface $cartClient,
        GoogleAnalyticsToProductStorageClientInterface $storageClient,
        EchidnaEcommerceProductMapperInterface $productMapper,
        GoogleAnalyticsConfig $config
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
            if (!isset($product[EchidnaEcommerceConstants::PRODUCT_FIELD_SKU])) {
                continue;
            }

            if (!isset($product[EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY])) {
                continue;
            }

            $itemTransfer = $this->getItemTransferFromQuote($product[EchidnaEcommerceConstants::PRODUCT_FIELD_SKU]);

            if ($itemTransfer === null) {
                continue;
            }

            if (!isset($product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID])) {
                $product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID] = $itemTransfer->getIdProductAbstract();
            }

            if (!isset($product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE])) {
                $product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE] = $itemTransfer->getUnitPrice();
            }

            $productDataAbstract = $this->storageClient
                ->findProductAbstractStorageData(
                    $product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID],
                    $this->config->getEchidnaEcommerceLocale()
                );

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($product[EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE]);
            $productViewTransfer->setQuantity($product[EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY]);

            $products[] = $this->productMapper->map($productViewTransfer)->toArray();
        }

        return $products;
    }

    /**
     * @param string $sku
     *
     * @return ItemTransfer|null
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
