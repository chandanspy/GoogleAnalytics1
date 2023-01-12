<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class EchidnaEcommerceCheckoutPaymentPlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
{
    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleAnalytics/partials/echidna-ecommerce-default.twig';
    }

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
        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_CHECKOUT)
            ->setEventLabel(EchidnaEcommerceConstants::CHECKOUT_STEP_PAYMENT)
            ->setEcommerce([
                    EchidnaEcommerceConstants::EVENT_CHECKOUT => [
                        'actionField' => [
                            'step' => EchidnaEcommerceConstants::CHECKOUT_STEP_PAYMENT,
                        ],
                    ],
                ]);

        return $twig->render($this->getTemplate(), [
            'data' => [
                $this->stripEmptyArrayIndex($EchidnaEcommerceTransfer),
            ],
        ]);
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
