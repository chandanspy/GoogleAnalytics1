<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author Chandan Kumar <ranjanpratik@yahoo.in>
 */

namespace Echidna\Yves\GoogleAnalytics;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductImageStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\CategoryVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\DefaultVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\NewsletterVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\OrderVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\ProductVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\QuoteVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilder;
use Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface;
use Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductArrayModel;
use Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductModelBuilderInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandler;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Twig\EchidnaEcommerceTwigExtension;
use Echidna\Yves\GoogleAnalytics\Twig\GoogleAnalyticsTwigExtension;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use Twig\Extension\ExtensionInterface;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()
 */
class GoogleAnalyticsFactory extends AbstractFactory
{
    /**
     * @return \Echidna\Yves\GoogleAnalytics\Twig\GoogleAnalyticsTwigExtension
     */
    public function createGoogleAnalyticsTwigExtension(): GoogleAnalyticsTwigExtension
    {
        return new GoogleAnalyticsTwigExtension(
            $this->getContainerID(),
            $this->isEnabled(),
            $this->getVariableBuilders(),
            $this->getCartClient(),
            $this->getSessionClient()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Business\Model\DataLayer\ProductVariableBuilder
     */
    protected function createProductVariableBuilder(): ProductVariableBuilder
    {
        return new ProductVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getTaxProductConnectorClient(),
            $this->getProductVariableBuilderPlugins()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Business\Model\DataLayer\CategoryVariableBuilder
     */
    protected function createCategoryVariableBuilder(): CategoryVariableBuilder
    {
        return new CategoryVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getCategoryVariableBuilderPlugins()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Business\Model\DataLayer\DefaultVariableBuilder
     */
    protected function createDefaultVariableBuilder(): DefaultVariableBuilder
    {
        return new DefaultVariableBuilder(
            $this->getDefaultVariableBuilderPlugins(),
            $this->getConfig()->getInternalIps()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Business\Model\DataLayer\OrderVariableBuilder
     */
    protected function createOrderVariableBuilder(): OrderVariableBuilder
    {
        return new OrderVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getCartClient(),
            $this->getProductStorageClient(),
            $this->getStore(),
            $this->getOrderVariableBuilderPlugins(),
            $this->createTransactionProductsVariableBuilder()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Business\Model\DataLayer\QuoteVariableBuilder
     */
    protected function createQuoteVariableBuilder(): QuoteVariableBuilder
    {
        return new QuoteVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getQuoteVariableBuilderPlugins(),
            $this->createTransactionProductsVariableBuilder()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Model\DataLayer\TransactionProductsVariableBuilderInterface
     */
    protected function createTransactionProductsVariableBuilder(): TransactionProductsVariableBuilderInterface
    {
        return new TransactionProductsVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getProductStorageClient(),
            $this->getProductImageStorageClient(),
            $this->getTransactionProductVariableBuilderPlugins(),
            $this->getStore()->getCurrentLocale()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Model\DataLayer\NewsletterVariableBuilder
     */
    protected function getNewsletterVariableBuilder(): NewsletterVariableBuilder
    {
        return new NewsletterVariableBuilder($this->getNewsletterVariableBuilderPlugins());
    }

    /**
     * @return array
     */
    public function getVariableBuilders(): array
    {
        return [
            GoogleAnalyticsConstants::PAGE_TYPE_PRODUCT => $this->createProductVariableBuilder(),
            GoogleAnalyticsConstants::PAGE_TYPE_CATEGORY => $this->createCategoryVariableBuilder(),
            GoogleAnalyticsConstants::PAGE_TYPE_DEFAULT => $this->createDefaultVariableBuilder(),
            GoogleAnalyticsConstants::PAGE_TYPE_ORDER => $this->createOrderVariableBuilder(),
            GoogleAnalyticsConstants::PAGE_TYPE_QUOTE => $this->createQuoteVariableBuilder(),
            GoogleAnalyticsConstants::PAGE_TYPE_NEWSLETTER_SUBSCRIBE => $this->getNewsletterVariableBuilder(),
        ];
    }

    /**
     * @return \Twig\Extension\ExtensionInterface
     */
    public function createEchidnaEcommerceTwigExtension(): ExtensionInterface
    {
        return new EchidnaEcommerceTwigExtension($this->getEchidnaEcommercePlugins());
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommercePageTypePluginInterface[]
     */
    public function getEchidnaEcommercePlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::ECHIDNA_ECOMMERCE_PAGE_PLUGINS);
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig
     */
    public function getGoogleAnalyticsConfig(): GoogleAnalyticsConfig
    {
        return $this->getConfig();
    }

    /**
     * @return string
     */
    protected function getContainerID(): string
    {
        return $this->getConfig()->getContainerID();
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return $this->getConfig()->isEnabled();
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface
     */
    public function getCartClient(): GoogleAnalyticsToCartClientInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::CART_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface
     */
    public function createMoneyPlugin(): MoneyPluginInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::PLUGIN_MONEY);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface
     */
    protected function getSessionClient(): GoogleAnalyticsToSessionClientInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::SESSION_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Echidna\Client\TaxProductConnector\TaxProductConnectorClient
     */
    public function getTaxProductConnectorClient()
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::TAX_PRODUCT_CONNECTOR_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\VariableBuilderPluginInterface[]
     */
    public function getProductVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::PRODUCT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables\CategoryVariableBuilderPluginInterface[]
     */
    public function getCategoryVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::CATEGORY_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\DefaultVariableBuilderPluginInterface[]
     */
    public function getDefaultVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::DEFAULT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface[]
     */
    public function getOrderVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::ORDER_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface[]
     */
    public function getQuoteVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::QUOTE_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return array
     */
    public function getTransactionProductVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::TRANSACTION_PRODUCT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\NewsletterVariables\NewsletterVariablesPluginInterface[]
     */
    public function getNewsletterVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::NEWSLETTER_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface[]
     */
    public function getCartControllerEventHandler(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::CART_CONTROLLER_EVENT_HANDLER);
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface[]
     */
    public function getNewsletterControllerEventHandler(): array
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::NEWSLETTER_SUBSCRIBE_EVENT_HANDLER);
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    public function getStore(): Store
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::STORE);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface
     */
    public function getProductStorageClient(): GoogleAnalyticsToProductStorageClientInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::PRODUCT_STORAGE_CLIENT);
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface
     */
    public function createEchidnaEcommerceSessionHandler(): EchidnaEcommerceSessionHandlerInterface
    {
        return new EchidnaEcommerceSessionHandler(
            $this->getSessionClient(),
            $this->getCartClient(),
            $this->getEchidnaEcommerceProductMapperPlugin()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductModelBuilderInterface
     */
    public function createEchidnaEcommerceProductArrayBuilder(): ProductModelBuilderInterface
    {
        return new ProductArrayModel(
            $this->getCartClient(),
            $this->getProductStorageClient(),
            $this->getEchidnaEcommerceProductMapperPlugin(),
            $this->getConfig()
        );
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface
     */
    public function getEchidnaEcommerceProductMapperPlugin(): EchidnaEcommerceProductMapperInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::EEC_PRODUCT_MAPPER_PLUGIN);
    }

    /**
     * @return array
     */
    public function getPaymentMethodMappingConfig(): array
    {
        return $this->getConfig()->getPaymentMethodMapping();
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductImageStorageClientInterface
     */
    public function getProductImageStorageClient(): GoogleAnalyticsToProductImageStorageClientInterface
    {
        return $this->getProvidedDependency(GoogleAnalyticsDependencyProvider::PRODUCT_IMAGE_STORAGE_CLIENT);
    }
}
