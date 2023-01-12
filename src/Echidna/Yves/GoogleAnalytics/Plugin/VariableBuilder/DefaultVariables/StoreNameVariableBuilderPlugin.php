<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables;

use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class StoreNameVariableBuilderPlugin extends AbstractPlugin implements DefaultVariableBuilderPluginInterface
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
            'store' => $this->getFactory()->getStore()->getStoreName(),
        ];
    }
}
