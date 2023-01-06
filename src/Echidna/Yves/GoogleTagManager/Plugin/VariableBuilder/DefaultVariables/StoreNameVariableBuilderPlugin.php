<?php


namespace Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables;

use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
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
