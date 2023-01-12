<?php

namespace Echidna\Yves\GoogleAnalytics\Model\DataLayer;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;

class QuoteVariableBuilder
{
    /**
     * @var \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface
     */
    protected $moneyPlugin;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface[]
     */
    protected $quoteVariableBuilderPlugins;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\TransactionProductVariableBuilderPluginInterface[]
     */
    protected $transactionProductPlugins;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface
     */
    protected $transactionProductsVariableBuilder;

    /**
     * @param \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface $moneyPlugin
     * @param \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface[] $quoteVariableBuilderPlugins
     * @param \Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface $transactionProductsVariableBuilder
     */
    public function __construct(
        MoneyPluginInterface $moneyPlugin,
        array $quoteVariableBuilderPlugins,
        TransactionProductsVariableBuilderInterface $transactionProductsVariableBuilder
    ) {
        $this->moneyPlugin = $moneyPlugin;
        $this->quoteVariableBuilderPlugins = $quoteVariableBuilderPlugins;
        $this->transactionProductsVariableBuilder = $transactionProductsVariableBuilder;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $sessionId
     *
     * @return array
     */
    public function getVariables(QuoteTransfer $quoteTransfer, string $sessionId): array
    {
        $variables = [
            GoogleAnalyticsConstants::TRANSACTION_ENTITY => GoogleAnalyticsConstants::TRANSACTION_ENTITY_QUOTE,
            GoogleAnalyticsConstants::TRANSACTION_ID => $sessionId,
            GoogleAnalyticsConstants::TRANSACTION_AFFILIATION => $quoteTransfer->getStore()->getName(),
            GoogleAnalyticsConstants::TRANSACTION_TOTAL => $this->moneyPlugin->convertIntegerToDecimal(
                $quoteTransfer->getTotals()->getGrandTotal()
            ),
            GoogleAnalyticsConstants::TRANSACTION_WITHOUT_SHIPPING_AMOUNT => $this->moneyPlugin->convertIntegerToDecimal(
                $this->getTotalWithoutShippingAmount($quoteTransfer)
            ),
            GoogleAnalyticsConstants::TRANSACTION_TAX => $this->moneyPlugin->convertIntegerToDecimal(
                $quoteTransfer->getTotals()->getTaxTotal()->getAmount()
            ),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCTS => $this->transactionProductsVariableBuilder->getProductsFromQuote($quoteTransfer),
            GoogleAnalyticsConstants::TRANSACTION_PRODUCTS_SKUS => $this->getTransactionProductsSkus($quoteTransfer),
            GoogleAnalyticsConstants::CUSTOMER_EMAIL => $this->getCustomerEmail($quoteTransfer->getBillingAddress()),
        ];

        return $this->executePlugins($quoteTransfer, $variables);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param array $variables
     *
     * @return array
     */
    protected function executePlugins(QuoteTransfer $quoteTransfer, array $variables): array
    {
        foreach ($this->quoteVariableBuilderPlugins as $plugin) {
            $variables = array_merge($variables, $plugin->handle($quoteTransfer, $variables));
        }

        return $variables;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array
     */
    protected function getTransactionProductsSkus(QuoteTransfer $quoteTransfer): array
    {
        $collection = [];

        foreach ($quoteTransfer->getItems() as $item) {
            $collection[] = $item->getSku();
        }

        return $collection;
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
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return int
     */
    protected function getTotalWithoutShippingAmount(QuoteTransfer $quoteTransfer): int
    {
        if ($quoteTransfer->getShipment() === null) {
            return 0;
        }

        if ($quoteTransfer->getTotals() === null) {
            return 0;
        }

        if ($quoteTransfer->getShipment()->getMethod() === null) {
            return 0;
        }

        return $quoteTransfer->getTotals()->getGrandTotal() - $quoteTransfer->getShipment()->getMethod()->getStoreCurrencyPrice();
    }
}
