<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class CustomerEmailHashVariableBuilderPlugin extends AbstractPlugin implements DefaultVariableBuilderPluginInterface
{
    /**
     * @param array $variables
     * @param array $params
     *
     * @return array
     */
    public function handle(array $variables, array $params = []): array
    {
        $quoteTransfer = $this->getFactory()
            ->getCartClient()
            ->getQuote();

        if (!$quoteTransfer instanceof QuoteTransfer) {
            return [];
        }

        if (!$quoteTransfer->getBillingAddress() instanceof AddressTransfer) {
            return [];
        }

        if (!$quoteTransfer->getBillingAddress()->getEmail()) {
            return [];
        }

        return [
            'externalIdHash' => \sha1($quoteTransfer->getBillingAddress()->getEmail()),
        ];
    }
}
