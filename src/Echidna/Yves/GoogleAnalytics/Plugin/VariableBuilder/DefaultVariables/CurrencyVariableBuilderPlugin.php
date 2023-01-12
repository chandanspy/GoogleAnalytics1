<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables;

use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class CurrencyVariableBuilderPlugin extends AbstractPlugin implements DefaultVariableBuilderPluginInterface
{
    /**
     * @param array $variables
     * @param array $params
     *
     * @return array
     */
    public function handle(array $variables, array $params = []): array
    {
        return [
            'currency' => $this->getFactory()->getStore()->getCurrencyIsoCode(),
        ];
    }
}
