<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author      Jozsef Geng <jozsefgeng@86gmail.com>
 */

namespace Echidna\Yves\GoogleAnalytics;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart\AddProductControllerEventHandler;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart\ChangeQuantityProductControllerEventHandler;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Cart\RemoveProductControllerEventHandler;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Checkout\PlaceOrderControllerEventHandler;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Newsletter\NewsletterConfirmationEventHandler;
use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Newsletter\NewsletterSubscribeEventHandler;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientBridge;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductImageStorageClientBridge;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientBridge;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientBridge;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCartPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCheckoutBillingAddressPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCheckoutPaymentPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCheckoutSummaryPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommercePageTypePluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceProductDetailPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommercePurchasePlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\BrandProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\CouponProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\Dimension10ProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\IdProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\NameProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\PriceProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\ProductFieldMapperPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\QuantityProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\VariantProductFieldMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapperPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables\CategoryVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables\ProductSkuCategoryVariableBuilderPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\CurrencyVariableBuilderPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\CustomerEmailHashVariableBuilderPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\DefaultVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\InternalVariableBuilderPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables\StoreNameVariableBuilderPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\NewsletterVariables\CustomerEmailHashNewsletterVariablesPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\NewsletterVariables\NewsletterVariablesPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\OrderVariables\OrderDiscountPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\ProductVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\ProductVariables\SalePricePlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\BrandPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\EanPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\ImageUrlPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\NamePlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\QuantityPlugin;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\TransactionProductVariableBuilderPluginInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\TransactionProductVariables\UrlPlugin;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandler;
use Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandler;
use Spryker\Shared\Kernel\Store;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use Spryker\Yves\Money\Plugin\MoneyPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()
 */
class GoogleAnalyticsDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CART_CLIENT = 'CART_CLIENT';
    public const PRODUCT_CLIENT = 'PRODUCT_CLIENT';
    public const PRODUCT_STORAGE_CLIENT = 'PRODUCT_STORAGE_CLIENT';
    public const TAX_PRODUCT_CONNECTOR_CLIENT = 'TAX_PRODUCT_CONNECTOR_CLIENT';
    public const PLUGIN_MONEY = 'PLUGIN_MONEY';
    public const SESSION_CLIENT = 'SESSION_CLIENT';
    public const PRODUCT_VARIABLE_BUILDER_PLUGINS = 'PRODUCT_VARIABLE_BUILDER_PLUGINS';
    public const DEFAULT_VARIABLE_BUILDER_PLUGINS = 'DEFAULT_VARIABLE_BUILDER_PLUGINS';
    public const CATEGORY_VARIABLE_BUILDER_PLUGINS = 'CATEGORY_VARIABLE_BUILDER_PLUGINS';
    public const ORDER_VARIABLE_BUILDER_PLUGINS = 'ORDER_VARIABLE_BUILDER_PLUGINS';
    public const QUOTE_VARIABLE_BUILDER_PLUGINS = 'QUOTE_VARIABLE_BUILDER_PLUGINS';
    public const TRANSACTION_PRODUCT_VARIABLE_BUILDER_PLUGINS = 'TRANSACTION_PRODUCT_VARIABLE_BUILDER_PLUGINS';
    public const NEWSLETTER_VARIABLE_BUILDER_PLUGINS = 'NEWSLETTER_VARIABLE_BUILDER_PLUGINS';
    public const CART_CONTROLLER_EVENT_HANDLER = 'CART_CONTROLLER_EVENT_HANDLER';
    public const ECHIDNA_ECOMMERCE_PAGE_PLUGINS = 'ECHIDNA_ECOMMERCE_PAGE_PLUGINS';
    public const STORE = 'STORE';
    public const PRODUCT_FIELD_MAPPER_PLUGINS = 'PRODUCT_FIELD_MAPPER_PLUGINS';
    public const EEC_PRODUCT_MAPPER_PLUGIN = 'EEC_PRODUCT_MAPPER_PLUGIN';
    public const NEWSLETTER_SUBSCRIBE_EVENT_HANDLER = 'NEWSLETTER_SUBSCRIBE_EVENT_HANDLER';
    public const GTM_SESSION_HANDLER = 'GTM_SESSION_HANDLER';
    public const EEC_SESSION_HANDLER = 'EEC_SESSION_HANDLER';
    public const PRODUCT_IMAGE_STORAGE_CLIENT = 'PRODUCT_IMAGE_STORAGE_CLIENT';

    /**
     * @param Container $container
     *
     * @return Container
     */
    public function provideDependencies(Container $container)
    {
        $this->provideCartClient($container);
        $this->provideProductClient($container);
        $this->provideTaxProductConnectorClient($container);
        $this->provideMoneyPlugin($container);
        $this->provideSessionClient($container);
        $this->addProductImageStorageClient($container);
        $this->addProductVariableBuilderPlugins($container);
        $this->addCategoryVariableBuilderPlugins($container);
        $this->addDefaultVariableBuilderPlugins($container);
        $this->addOrderVariableBuilderPlugins($container);
        $this->addQuoteVariableBuilderPlugins($container);
        $this->addTransactionProductVariableBuilderPlugins($container);
        $this->addEchidnaEcommercePlugins($container);
        $this->addProductStorageClient($container);
        $this->addStore($container);
        $this->addProductFieldMapperPlugins($container);

        $this->addGoogleAnalyticsSessionHandler($container);
        $this->addEchidnaEcommerceSessionHandler($container);
        $this->addNewsletterVariableBuilderPlugins($container);
        $this->addNewsletterControllerEventHandler($container);
        $this->addEchidnaEcommerceProductMapperPlugin($container);
        $this->addCartControllerEventHandler($container);

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container $container
     */
    protected function provideCartClient(Container $container): Container
    {
        $container[static::CART_CLIENT] = function (Container $container) {
            return new GoogleAnalyticsToCartClientBridge($container->getLocator()->cart()->client());
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container $container
     */
    protected function provideProductClient(Container $container)
    {
        $container[static::PRODUCT_CLIENT] = function (Container $container) {
            return $container->getLocator()->product()->client();
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container $container
     */
    protected function provideTaxProductConnectorClient(Container $container)
    {
        $container[static::TAX_PRODUCT_CONNECTOR_CLIENT] = function (Container $container) {
            return $container->getLocator()->taxProductConnector()->client();
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function provideMoneyPlugin(Container $container): Container
    {
        $container[static::PLUGIN_MONEY] = function () {
            return new MoneyPlugin();
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function provideSessionClient(Container $container): Container
    {
        $container[static::SESSION_CLIENT] = function (Container $container) {
            return new GoogleAnalyticsToSessionClientBridge($container->getLocator()->session()->client());
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addProductVariableBuilderPlugins(Container $container): Container
    {
        $container[static::PRODUCT_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getProductVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return ProductVariableBuilderPluginInterface[]
     */
    protected function getProductVariableBuilderPlugins(Container $container): array
    {
        return [
            new SalePricePlugin(new MoneyPlugin(), $this->getConfig()),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addCategoryVariableBuilderPlugins(Container $container): Container
    {
        $container[static::CATEGORY_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getCategoryVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return CategoryVariableBuilderPluginInterface[]
     */
    protected function getCategoryVariableBuilderPlugins(Container $container): array
    {
        return [
            new ProductSkuCategoryVariableBuilderPlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addNewsletterVariableBuilderPlugins(Container $container): Container
    {
        $container[static::NEWSLETTER_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getNewsletterVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return NewsletterVariablesPluginInterface[]
     */
    protected function getNewsletterVariableBuilderPlugins(Container $container): array
    {
        return [
            new CustomerEmailHashNewsletterVariablesPlugin($container[static::GTM_SESSION_HANDLER]),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addDefaultVariableBuilderPlugins(Container $container): Container
    {
        $container[static::DEFAULT_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getDefaultVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return DefaultVariableBuilderPluginInterface[]
     */
    protected function getDefaultVariableBuilderPlugins(Container $container): array
    {
        return [
            new CustomerEmailHashVariableBuilderPlugin(),
            new StoreNameVariableBuilderPlugin(),
            new CurrencyVariableBuilderPlugin(),
            new InternalVariableBuilderPlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addOrderVariableBuilderPlugins(Container $container): Container
    {
        $container[static::ORDER_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getOrderVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return OrderVariableBuilderPluginInterface[]
     */
    protected function getOrderVariableBuilderPlugins(Container $container): array
    {
        return [
            new OrderDiscountPlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addQuoteVariableBuilderPlugins(Container $container): Container
    {
        $container[static::QUOTE_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getQuoteVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return QuoteVariableBuilderPluginInterface[]
     */
    protected function getQuoteVariableBuilderPlugins(Container $container): array
    {
        return [];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addTransactionProductVariableBuilderPlugins(Container $container): Container
    {
        $container[static::TRANSACTION_PRODUCT_VARIABLE_BUILDER_PLUGINS] = function () {
            return $this->getTransactionProductVariableBuilderPlugins();
        };

        return $container;
    }

    /**
     * @return TransactionProductVariableBuilderPluginInterface[]
     */
    public function getTransactionProductVariableBuilderPlugins(): array
    {
        return [
            new NamePlugin(),
            new EanPlugin(),
            new UrlPlugin($this->getConfig()),
            new BrandPlugin(),
            new ImageUrlPlugin(),
            new QuantityPlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container $container
     */
    protected function addEchidnaEcommercePlugins(Container $container): Container
    {
        $container[static::ECHIDNA_ECOMMERCE_PAGE_PLUGINS] = function () {
            return $this->getEchidnaEcommercePlugins();
        };

        return $container;
    }

    /**
     * @return EchidnaEcommercePageTypePluginInterface[]
     */
    protected function getEchidnaEcommercePlugins(): array
    {
        return [
            EchidnaEcommerceConstants::PAGE_TYPE_CART => new EchidnaEcommerceCartPlugin(),
            EchidnaEcommerceConstants::PAGE_TYPE_PRODUCT_DETAIL => new EchidnaEcommerceProductDetailPlugin(),
            EchidnaEcommerceConstants::PAGE_TYPE_CHECKOUT_BILLING_ADDRESS => new EchidnaEcommerceCheckoutBillingAddressPlugin(),
            EchidnaEcommerceConstants::PAGE_TYPE_CHECKOUT_PAYMENT => new EchidnaEcommerceCheckoutPaymentPlugin(),
            EchidnaEcommerceConstants::PAGE_TYPE_CHECKOUT_SUMMARY => new EchidnaEcommerceCheckoutSummaryPlugin(),
            EchidnaEcommerceConstants::PAGE_TYPE_PURCHASE => new EchidnaEcommercePurchasePlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addProductStorageClient(Container $container): Container
    {
        $container[static::PRODUCT_STORAGE_CLIENT] = function (Container $container) {
            return new GoogleAnalyticsToProductStorageClientBridge(
                $container->getLocator()->productStorage()->client()
            );
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addStore(Container $container): Container
    {
        $container[static::STORE] = function (Container $container) {
            return $this->getStore();
        };

        return $container;
    }

    /**
     * @return Store
     */
    protected function getStore(): Store
    {
        return Store::getInstance();
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addProductFieldMapperPlugins(Container $container): Container
    {
        $container[static::PRODUCT_FIELD_MAPPER_PLUGINS] = function (Container $container) {
            return $this->getProductFieldMapperPlugins();
        };

        return $container;
    }

    /**
     * @return ProductFieldMapperPluginInterface[]
     */
    protected function getProductFieldMapperPlugins(): array
    {
        return [
            new IdProductFieldMapperPlugin(),
            new NameProductFieldMapperPlugin(),
            new VariantProductFieldMapperPlugin(),
            new BrandProductFieldMapperPlugin(),
            new Dimension10ProductFieldMapperPlugin(),
            new QuantityProductFieldMapperPlugin(),
            new PriceProductFieldMapperPlugin(),
            new CouponProductFieldMapperPlugin(),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addNewsletterControllerEventHandler(Container $container): Container
    {
        $container[static::NEWSLETTER_SUBSCRIBE_EVENT_HANDLER] = function (Container $container) {
            return $this->getNewsletterControllerEventHandler($container);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return array
     */
    protected function getNewsletterControllerEventHandler(Container $container): array
    {
        return [
            new NewsletterSubscribeEventHandler($container[static::GTM_SESSION_HANDLER]),
            new NewsletterConfirmationEventHandler($container[static::GTM_SESSION_HANDLER]),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addGoogleAnalyticsSessionHandler(Container $container): Container
    {
        $container[static::GTM_SESSION_HANDLER] = function (Container $container) {
            return new GoogleAnalyticsSessionHandler($container[static::SESSION_CLIENT]);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addEchidnaEcommerceSessionHandler(Container $container): Container
    {
        $container[static::EEC_SESSION_HANDLER] = function (Container $container) {
            return new EchidnaEcommerceSessionHandler(
                $container[static::SESSION_CLIENT],
                $container[static::CART_CLIENT],
                $container[static::EEC_PRODUCT_MAPPER_PLUGIN]
            );
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addEchidnaEcommerceProductMapperPlugin(Container $container): Container
    {
        $container[static::EEC_PRODUCT_MAPPER_PLUGIN] = function (Container $container) {
            return new EchidnaEcommerceProductMapperPlugin($container[static::PRODUCT_FIELD_MAPPER_PLUGINS]);
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addCartControllerEventHandler(Container $container): Container
    {
        $container[static::CART_CONTROLLER_EVENT_HANDLER] = function (Container $container) {
            return $this->getCartControllerEventHandler($container);
        };

        return $container;
    }

    /**
     * @return \Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface[]
     */
    protected function getCartControllerEventHandler(Container $container): array
    {
        return [
            new AddProductControllerEventHandler($container[static::EEC_SESSION_HANDLER], $container[static::CART_CLIENT]),
            new ChangeQuantityProductControllerEventHandler($container[static::EEC_SESSION_HANDLER], $container[static::CART_CLIENT]),
            new RemoveProductControllerEventHandler($container[static::EEC_SESSION_HANDLER], $container[static::CART_CLIENT]),
            new PlaceOrderControllerEventHandler($container[static::EEC_SESSION_HANDLER], $container[static::CART_CLIENT]),
        ];
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addProductImageStorageClient(Container $container): Container
    {
        $container[static::PRODUCT_IMAGE_STORAGE_CLIENT] = function (Container $container) {
            return new GoogleAnalyticsToProductImageStorageClientBridge($container->getLocator()->productImageStorage()->client());
        };

        return $container;
    }
}
