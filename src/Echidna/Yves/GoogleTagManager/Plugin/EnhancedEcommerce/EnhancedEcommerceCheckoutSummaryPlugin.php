<?php

namespace Echidna\Yves\GoogleTagManager\Plugin\EnhancedEcommerce;

use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Generated\Shared\Transfer\EnhancedEcommerceTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 */
class EnhancedEcommerceCheckoutSummaryPlugin extends AbstractPlugin implements EnhancedEcommercePageTypePluginInterface
{
    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array|null $params
     *
     * @throws
     *
     * @return string
     */
    public function handle(Twig_Environment $twig, Request $request, ?array $params = []): string
    {
        return $twig->render($this->getTemplate(), [
            'data' => [
                $this->stripEmptyArrayIndex($this->getSummaryEvent()),
                $this->stripEmptyArrayIndex($this->getCheckoutPaymentEvent()),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleTagManager/partials/enhanced-ecommerce-default.twig';
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\EnhancedEcommerceTransfer
     */
    protected function getCheckoutPaymentEvent(): EnhancedEcommerceTransfer
    {
        $enhancedEcommerceTransfer = (new EnhancedEcommerceTransfer())
            ->setEvent(EnhancedEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EnhancedEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EnhancedEcommerceConstants::EVENT_CHECKOUT_OPTION)
            ->setEventLabel(EnhancedEcommerceConstants::CHECKOUT_STEP_PAYMENT)
            ->setEcCheckoutOption([
                    EnhancedEcommerceConstants::EVENT_CHECKOUT_OPTION => [
                        'actionField' => [
                            'step' => EnhancedEcommerceConstants::CHECKOUT_STEP_PAYMENT,
                            'option' => $this->getPaymentMethod(),
                        ],
                    ],
                ]);

        return $enhancedEcommerceTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\EnhancedEcommerceTransfer
     */
    protected function getSummaryEvent(): EnhancedEcommerceTransfer
    {
        $enhancedEcommerceTransfer = (new EnhancedEcommerceTransfer())
            ->setEvent(EnhancedEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EnhancedEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EnhancedEcommerceConstants::EVENT_CHECKOUT)
            ->setEventLabel(EnhancedEcommerceConstants::CHECKOUT_STEP_SUMMARY)
            ->setEcommerce([
                    EnhancedEcommerceConstants::EVENT_CHECKOUT => [
                        'actionField' => [
                            'step' => EnhancedEcommerceConstants::CHECKOUT_STEP_SUMMARY,
                        ],
                    ],
                ]);

        return $enhancedEcommerceTransfer;
    }

    /**
     * @return string
     */
    protected function getPaymentMethod(): string
    {
        $quoteTransfer = $this->getFactory()
            ->getCartClient()
            ->getQuote();

        if (!$quoteTransfer->getPayment() instanceof PaymentTransfer) {
            return '';
        }

        $paymentMethodMapping = $this->getFactory()->getPaymentMethodMappingConfig();

        if (!isset($paymentMethodMapping[$quoteTransfer->getPayment()->getPaymentSelection()])) {
            return '';
        }

        return $paymentMethodMapping[$quoteTransfer->getPayment()->getPaymentSelection()];
    }

    /**
     * @param \Generated\Shared\Transfer\EnhancedEcommerceTransfer $transfer
     *
     * @return array
     */
    protected function stripEmptyArrayIndex(EnhancedEcommerceTransfer $transfer): array
    {
        $result = [];

        foreach ($transfer->toArray() as $key => $value) {
            if (!$value) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
