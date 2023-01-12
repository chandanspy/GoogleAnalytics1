<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author Chandan Kumar <ranjanpratik@yahoo.in>
 */
namespace Echidna\Yves\GoogleAnalytics;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Spryker\Yves\Kernel\AbstractBundleConfig;

class GoogleAnalyticsConfig extends AbstractBundleConfig
{
    /**
     * @return string
     */
    public function getContainerID(): string
    {
        return $this->get(GoogleAnalyticsConstants::CONTAINER_ID);
    }

    /**
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->get(GoogleAnalyticsConstants::ENABLED);
    }

    /**
     * @return string
     */
    public function getSpecialPriceAttribute(): string
    {
        return $this->get(GoogleAnalyticsConstants::ATTRIBUTE_SPECIAL_PRICE);
    }

    /**
     * @return string
     */
    public function getSpecialPriceFromAttribute(): string
    {
        return $this->get(GoogleAnalyticsConstants::ATTRIBUTE_SPECIAL_PRICE_FROM);
    }

    /**
     * @return string
     */
    public function getSpecialPriceToAttribute(): string
    {
        return $this->get(GoogleAnalyticsConstants::ATTRIBUTE_SPECIAL_PRICE_TO);
    }

    /**
     * @return array
     */
    public function getListenToControllersEchidnaEcommerce(): array
    {
        return $this->get(GoogleAnalyticsConstants::EEC_LISTEN_TO_CONTROLLERS, []);
    }

    /**
     * @return array
     */
    public function getListenToControllersGoogleAnalytics(): array
    {
        return $this->get(GoogleAnalyticsConstants::GTM_LISTEN_TO_CONTROLLERS, []);
    }

    /**
     * @return string
     */
    public function getEchidnaEcommerceLocale(): string
    {
        return $this->get(EchidnaEcommerceConstants::EEC_LOCALE, 'en_US');
    }

    /**
     * @return array
     */
    public function getPaymentMethodMapping(): array
    {
        return $this->get(EchidnaEcommerceConstants::PAYMENT_METHODS, [
            EchidnaEcommerceConstants::PAYMENT_METHOD_PREPAYMENT_SELECTION => EchidnaEcommerceConstants::PAYMENT_METHOD_PREPAYMENT_NAME,
            EchidnaEcommerceConstants::PAYMENT_METHOD_PAYPAL_SELECTION => EchidnaEcommerceConstants::PAYMENT_METHOD_PAYPAL_NAME,
            EchidnaEcommerceConstants::PAYMENT_METHOD_CREDITCARD_SELECTION => EchidnaEcommerceConstants::PAYMENT_METHOD_CREDITCARD_NAME,
        ]);
    }

    /**
     * @return array
     */
    public function getInternalIps(): array
    {
        return $this->get(GoogleAnalyticsConstants::INTERNAL_IPS, []);
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->get(GoogleAnalyticsConstants::GTM_PROTOCOL, 'http');
    }
}
