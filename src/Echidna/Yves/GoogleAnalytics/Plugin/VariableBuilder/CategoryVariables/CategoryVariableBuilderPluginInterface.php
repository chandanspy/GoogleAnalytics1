<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables;

use Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer;

interface CategoryVariableBuilderPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer
     *
     * @return array
     */
    public function handle(GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer): array;
}
