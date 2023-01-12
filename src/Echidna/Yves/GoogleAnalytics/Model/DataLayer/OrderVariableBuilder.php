<?php

namespace Echidna\Yves\GoogleAnalytics\Model\DataLayer;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;
use Spryker\Shared\Shipment\ShipmentConstants;

class OrderVariableBuilder
{
    /**
     * @var \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface
     */
    protected $moneyPlugin;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface[]
     */
    protected $orderVariableBuilderPlugins;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface
     */
    protected $cartClient;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface
     */
    protected $storageClient;

    /**
     * @var \Spryker\Shared\Kernel\Store
     */
    protected $store;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface
     */
    protected $transactionProductsVariableBuilder;

    /**
     * @param \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface $moneyPlugin
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface $cartClient
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface $storageClient
     * @param \Spryker\Shared\Kernel\Store $store
     * @param \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface[] $orderVariableBuilderPlugins
     * @param \Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface $transactionProductsVariableBuilder
     */
    public function __construct(
        MoneyPluginInterface $moneyPlugin,
        GoogleAnalyticsToCartClientInterface $cartClient,
        GoogleAnalyticsToProductStorageClientInterface $storageClient,
        Store $store,
        array $orderVariableBuilderPlugins,
        TransactionProductsVariableBuilderInterface $transactionProductsVariableBuilder
    ) {
        $this->moneyPlugin = $moneyPlugin;
        $this->orderVariableBuilderPlugins = $orderVariableBuilderPlugins;
        $this->cartClient = $cartClient;
        $this->storageClient = $storageClient;
        $this->store = $store;
        $this->transactionProductsVariableBuilder = $transactionProductsVariableBuilder;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    public function getVariables(OrderTransfer $orderTransfer): array
    {
        $variables = [
            GoogleAnalyticsConstants::TRANSACTION_ENTITY => strtoupper(GoogleAnalyticsConstants::PAGE_TYPE_ORDER),
            GoogleAnalyticsConstants::TRANSACTION_ID => $orderTransfer->getOrderReference(),
            GoogleAnalyticsConstants::TRANSACTION_DATE => $orderTransfer->getCreatedAt(),
            GoogleAnalyticsConstants::TRANSACTION_AFFILIATION => $orderTransfer->getStore(),
            GoogleAnalyticsConstants::TRANSACTION_TOTAL => $this->moneyPlugin->convertIntegerToDecimal(
                $orderTransfer->getTotals()->getGrandTotal()
            ),
            GoogleAnalyticsConstants::TRANSACTION_WITHOUT_SHIPPING_AMOUNT => $this->moneyPlugin->convertIntegerToDecimal(
                $this->getTransactionTotalWithoutShippingAmount($orderTransfer)
            ),
            GoogleAnalyticsConstants::TRANSACTION_SUBTOTAL => $this->moneyPlugin->convertIntegerToDecimal(
                $orderTransfer->getTotals()->getSubtotal()
            ),
            GoogleAnalyticsConstants::TRANSACTION_TAX => $this->moneyPlugin->convertIntegerToDecimal(
                $orderTransfer->getTotals()->getTaxTotal()->getAmount()
            ),
            GoogleAnalyticsConstants::TRANSACTION_SHIPPING => implode('-', $this->getShipmentMethods($orderTransfer)),
            GoogleAnalyticsConstants::TRANSACTION_PAYMENT => implode('-', $this->getPaymentMethods($orderTransfer)),
            GoogleAnalyticsConstants::TRANSACTION_CURRENCY => $orderTransfer->getCurrencyIsoCode(),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCTS => $this->transactionProductsVariableBuilder->getProductsFromOrder($orderTransfer),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCTS_SKUS => $this->getTransactionProductsSkus($orderTransfer),
            GoogleAnalyticsConstants::CUSTOMER_EMAIL => $this->getCustomerEmail($orderTransfer->getBillingAddress()),
        ];

        return $this->executePlugins($orderTransfer, $variables);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param array $variables
     *
     * @return array
     */
    protected function executePlugins(OrderTransfer $orderTransfer, array $variables): array
    {
        foreach ($this->orderVariableBuilderPlugins as $plugin) {
            $variables = \array_merge($variables, $plugin->handle($orderTransfer, $variables));
        }

        return $variables;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getTransactionProducts(OrderTransfer $orderTransfer): array
    {
        /** @var \Generated\Shared\Transfer\ItemTransfer[] $collection */
        $collection = [];
        $returnCollection = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (\array_key_exists($itemTransfer->getSku(), $collection)) {
                $quantity = $collection[$itemTransfer->getSku()]->getQuantity() + 1;
                $collection[$itemTransfer->getSku()]->setQuantity($quantity);

                continue;
            }

            $collection[$itemTransfer->getSku()] = $itemTransfer;
        }

        foreach ($collection as $itemTransfer) {
            $returnCollection[] = $this->getProductForTransaction($itemTransfer);
        }

        return $returnCollection;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getTransactionProductsSkus(OrderTransfer $orderTransfer): array
    {
        $collection = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (!\in_array($itemTransfer->getSku(), $collection)) {
                $collection[] = $itemTransfer->getSku();
            }
        }

        return $collection;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $product
     *
     * @return array
     */
    protected function getProductForTransaction(ItemTransfer $product): array
    {
        return [
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_ID => $product->getIdProductAbstract(),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_SKU => $product->getSku(),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_NAME => $this->getProductName($this->getProductViewTransfer($product)),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_PRICE => $this->moneyPlugin->convertIntegerToDecimal($product->getUnitPrice()),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_PRICE_EXCLUDING_TAX => $this->getPriceExcludingTax($product),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_TAX => $this->moneyPlugin->convertIntegerToDecimal($product->getUnitTaxAmount()),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_TAX_RATE => $product->getTaxRate(),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCT_QUANTITY => $product->getQuantity(),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer|null
     */
    protected function getProductViewTransfer(ItemTransfer $itemTransfer): ?ProductViewTransfer
    {
        $productDataAbstract = $this->storageClient
            ->findProductAbstractStorageData($itemTransfer->getIdProductAbstract(), $this->store->getCurrentLocale());

        if ($productDataAbstract === null) {
            return null;
        }

        $productViewTransfer = $this->storageClient
            ->mapProductStorageData($productDataAbstract, $this->store->getCurrentLocale(), []);

        return $productViewTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $product
     *
     * @return string
     */
    protected function getProductName(ProductViewTransfer $product): string
    {
        if (!\array_key_exists(GoogleAnalyticsConstants::NAME_UNTRANSLATED, $product->getAttributes())) {
            return $product->getName();
        }

        if (!$product->getAttributes()[GoogleAnalyticsConstants::NAME_UNTRANSLATED]) {
            return $product->getName();
        }

        return $product->getAttributes()[GoogleAnalyticsConstants::NAME_UNTRANSLATED];
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $product
     *
     * @return float|null
     */
    protected function getPriceExcludingTax(ItemTransfer $product): ?float
    {
        if ($product->getUnitNetPrice()) {
            return $this->moneyPlugin->convertIntegerToDecimal($product->getUnitNetPrice());
        }

        return $this->moneyPlugin->convertIntegerToDecimal($product->getUnitPrice() - $product->getUnitTaxAmount());
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getShipmentMethods(OrderTransfer $orderTransfer): array
    {
        $shipmentMethods = [];

        foreach ($orderTransfer->getShipmentMethods() as $shipment) {
            $shipmentMethods[] = $shipment->getName();
        }

        return $shipmentMethods;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getPaymentMethods(OrderTransfer $orderTransfer): array
    {
        $paymentMethods = [];

        foreach ($orderTransfer->getPayments() as $payment) {
            $paymentMethods[] = $payment->getPaymentMethod();
        }

        return $paymentMethods;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getExpenses(OrderTransfer $orderTransfer): array
    {
        $expenses = [];

        foreach ($orderTransfer->getExpenses() as $expense) {
            $expenses[$expense->getType()] = (!\array_key_exists($expense->getType(), $expenses)) ? $expense->getUnitPrice() : $expenses[$expense->getType()] + $expense->getUnitPrice();
        }

        return $expenses;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer|null $addressTransfer
     *
     * @return string
     */
    protected function getCustomerEmail(?AddressTransfer $addressTransfer): string
    {
        if ($addressTransfer === null) {
            return '';
        }

        if (!$addressTransfer->getEmail()) {
            return '';
        }

        return $addressTransfer->getEmail();
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return int|null
     */
    protected function getTransactionTotalWithoutShippingAmount(OrderTransfer $orderTransfer): ?int
    {
        $expenses = $this->getExpenses($orderTransfer);

        if (\array_key_exists(ShipmentConstants::SHIPMENT_EXPENSE_TYPE, $expenses)) {
            return $orderTransfer->getTotals()->getGrandTotal() - $expenses[ShipmentConstants::SHIPMENT_EXPENSE_TYPE];
        }

        return $orderTransfer->getTotals()->getGrandTotal();
    }
}
