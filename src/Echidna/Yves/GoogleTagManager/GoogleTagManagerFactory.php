<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author      Jozsef Geng <gengjozsef86@gmail.com>
 */

namespace Echidna\Yves\GoogleTagManager;

use Echidna\Shared\GoogleTagManager\GoogleTagManagerConstants;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductImageStorageClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface;
use Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\CategoryVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\DefaultVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\NewsletterVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\OrderVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\ProductVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\QuoteVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\TransactionProductsVariableBuilder;
use Echidna\Yves\GoogleTagManager\Model\DataLayer\TransactionProductsVariableBuilderInterface;
use Echidna\Yves\GoogleTagManager\Model\EnhancedEcommerce\ProductArrayModel;
use Echidna\Yves\GoogleTagManager\Model\EnhancedEcommerce\ProductModelBuilderInterface;
use Echidna\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandler;
use Echidna\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandlerInterface;
use Echidna\Yves\GoogleTagManager\Twig\EnhancedEcommerceTwigExtension;
use Echidna\Yves\GoogleTagManager\Twig\GoogleTagManagerTwigExtension;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use Twig\Extension\ExtensionInterface;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig getConfig()
 */
class GoogleTagManagerFactory extends AbstractFactory
{
    /**
     * @return \Echidna\Yves\GoogleTagManager\Twig\GoogleTagManagerTwigExtension
     */
    public function createGoogleTagManagerTwigExtension(): GoogleTagManagerTwigExtension
    {
        return new GoogleTagManagerTwigExtension(
            $this->getContainerID(),
            $this->isEnabled(),
            $this->getVariableBuilders(),
            $this->getCartClient(),
            $this->getSessionClient()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\Business\Model\DataLayer\ProductVariableBuilder
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
     * @return \Echidna\Yves\GoogleTagManager\Business\Model\DataLayer\CategoryVariableBuilder
     */
    protected function createCategoryVariableBuilder(): CategoryVariableBuilder
    {
        return new CategoryVariableBuilder(
            $this->createMoneyPlugin(),
            $this->getCategoryVariableBuilderPlugins()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\Business\Model\DataLayer\DefaultVariableBuilder
     */
    protected function createDefaultVariableBuilder(): DefaultVariableBuilder
    {
        return new DefaultVariableBuilder(
            $this->getDefaultVariableBuilderPlugins(),
            $this->getConfig()->getInternalIps()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\Business\Model\DataLayer\OrderVariableBuilder
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
     * @return \Echidna\Yves\GoogleTagManager\Business\Model\DataLayer\QuoteVariableBuilder
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
     * @return \Echidna\Yves\GoogleTagManager\Model\DataLayer\TransactionProductsVariableBuilderInterface
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
     * @return \Echidna\Yves\GoogleTagManager\Model\DataLayer\NewsletterVariableBuilder
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
            GoogleTagManagerConstants::PAGE_TYPE_PRODUCT => $this->createProductVariableBuilder(),
            GoogleTagManagerConstants::PAGE_TYPE_CATEGORY => $this->createCategoryVariableBuilder(),
            GoogleTagManagerConstants::PAGE_TYPE_DEFAULT => $this->createDefaultVariableBuilder(),
            GoogleTagManagerConstants::PAGE_TYPE_ORDER => $this->createOrderVariableBuilder(),
            GoogleTagManagerConstants::PAGE_TYPE_QUOTE => $this->createQuoteVariableBuilder(),
            GoogleTagManagerConstants::PAGE_TYPE_NEWSLETTER_SUBSCRIBE => $this->getNewsletterVariableBuilder(),
        ];
    }

    /**
     * @return \Twig\Extension\ExtensionInterface
     */
    public function createEnhancedEcommerceTwigExtension(): ExtensionInterface
    {
        return new EnhancedEcommerceTwigExtension($this->getEnhancedEcommercePlugins());
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommercePageTypePluginInterface[]
     */
    public function getEnhancedEcommercePlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::ENHANCED_ECOMMERCE_PAGE_PLUGINS);
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig
     */
    public function getGoogleTagManagerConfig(): GoogleTagManagerConfig
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
     * @return \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface
     */
    public function getCartClient(): GoogleTagManagerToCartClientInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::CART_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface
     */
    public function createMoneyPlugin(): MoneyPluginInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::PLUGIN_MONEY);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface
     */
    protected function getSessionClient(): GoogleTagManagerToSessionClientInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::SESSION_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Echidna\Client\TaxProductConnector\TaxProductConnectorClient
     */
    public function getTaxProductConnectorClient()
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::TAX_PRODUCT_CONNECTOR_CLIENT);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\VariableBuilderPluginInterface[]
     */
    public function getProductVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::PRODUCT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\CategoryVariables\CategoryVariableBuilderPluginInterface[]
     */
    public function getCategoryVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::CATEGORY_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables\DefaultVariableBuilderPluginInterface[]
     */
    public function getDefaultVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::DEFAULT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface[]
     */
    public function getOrderVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::ORDER_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface[]
     */
    public function getQuoteVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::QUOTE_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return array
     */
    public function getTransactionProductVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::TRANSACTION_PRODUCT_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\NewsletterVariables\NewsletterVariablesPluginInterface[]
     */
    public function getNewsletterVariableBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::NEWSLETTER_VARIABLE_BUILDER_PLUGINS);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\ControllerEventHandler\ControllerEventHandlerInterface[]
     */
    public function getCartControllerEventHandler(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::CART_CONTROLLER_EVENT_HANDLER);
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\ControllerEventHandler\ControllerEventHandlerInterface[]
     */
    public function getNewsletterControllerEventHandler(): array
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::NEWSLETTER_SUBSCRIBE_EVENT_HANDLER);
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    public function getStore(): Store
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::STORE);
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientInterface
     */
    public function getProductStorageClient(): GoogleTagManagerToProductStorageClientInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::PRODUCT_STORAGE_CLIENT);
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandlerInterface
     */
    public function createEnhancedEcommerceSessionHandler(): EnhancedEcommerceSessionHandlerInterface
    {
        return new EnhancedEcommerceSessionHandler(
            $this->getSessionClient(),
            $this->getCartClient(),
            $this->getEnhancedEcommerceProductMapperPlugin()
        );
    }

    /**
     * @return \Echidna\Yves\GoogleTagManager\Model\EnhancedEcommerce\ProductModelBuilderInterface
     */
    public function createEnhancedEcommerceProductArrayBuilder(): ProductModelBuilderInterface
    {
        return new ProductArrayModel(
            $this->getCartClient(),
            $this->getProductStorageClient(),
            $this->getEnhancedEcommerceProductMapperPlugin(),
            $this->getConfig()
        );
    }

    /**
     * @throws
     *
     * @return \Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface
     */
    public function getEnhancedEcommerceProductMapperPlugin(): EnhancedEcommerceProductMapperInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::EEC_PRODUCT_MAPPER_PLUGIN);
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
     * @return \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductImageStorageClientInterface
     */
    public function getProductImageStorageClient(): GoogleTagManagerToProductImageStorageClientInterface
    {
        return $this->getProvidedDependency(GoogleTagManagerDependencyProvider::PRODUCT_IMAGE_STORAGE_CLIENT);
    }
}
