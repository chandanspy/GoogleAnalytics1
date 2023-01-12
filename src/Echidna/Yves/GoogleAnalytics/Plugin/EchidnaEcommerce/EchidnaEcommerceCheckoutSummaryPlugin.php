<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class EchidnaEcommerceCheckoutSummaryPlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
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
        return '@GoogleAnalytics/partials/echidna-ecommerce-default.twig';
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\EchidnaEcommerceTransfer
     */
    protected function getCheckoutPaymentEvent(): EchidnaEcommerceTransfer
    {
        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_CHECKOUT_OPTION)
            ->setEventLabel(EchidnaEcommerceConstants::CHECKOUT_STEP_PAYMENT)
            ->setEcCheckoutOption([
                    EchidnaEcommerceConstants::EVENT_CHECKOUT_OPTION => [
                        'actionField' => [
                            'step' => EchidnaEcommerceConstants::CHECKOUT_STEP_PAYMENT,
                            'option' => $this->getPaymentMethod(),
                        ],
                    ],
                ]);

        return $EchidnaEcommerceTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\EchidnaEcommerceTransfer
     */
    protected function getSummaryEvent(): EchidnaEcommerceTransfer
    {
        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_CHECKOUT)
            ->setEventLabel(EchidnaEcommerceConstants::CHECKOUT_STEP_SUMMARY)
            ->setEcommerce([
                    EchidnaEcommerceConstants::EVENT_CHECKOUT => [
                        'actionField' => [
                            'step' => EchidnaEcommerceConstants::CHECKOUT_STEP_SUMMARY,
                        ],
                    ],
                ]);

        return $EchidnaEcommerceTransfer;
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
     * @param \Generated\Shared\Transfer\EchidnaEcommerceTransfer $transfer
     *
     * @return array
     */
    protected function stripEmptyArrayIndex(EchidnaEcommerceTransfer $transfer): array
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
