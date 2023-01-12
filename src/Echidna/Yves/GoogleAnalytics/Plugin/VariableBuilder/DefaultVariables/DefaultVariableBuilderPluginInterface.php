<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\DefaultVariables;

interface DefaultVariableBuilderPluginInterface
{
    /**
     * @param array $variables
     * @param array $params
     *
     * @return array
     */
    public function handle(array $variables, array $params = []): array;
}
