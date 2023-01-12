<?php

namespace Echidna\Yves\GoogleAnalytics\Model\DataLayer;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Generated\Shared\Transfer\GoogleAnalyticsCategoryProductTransfer;
use Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;

class CategoryVariableBuilder
{
    /**
     * @var \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface
     */
    protected $moneyPlugin;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables\CategoryVariableBuilderPluginInterface[]
     */
    protected $categoryVariableBuilderPlugins;

    /**
     * @param \Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface $moneyPlugin
     * @param array $categoryVariableBuilderPlugins
     */
    public function __construct(
        MoneyPluginInterface $moneyPlugin,
        array $categoryVariableBuilderPlugins = []
    ) {
        $this->moneyPlugin = $moneyPlugin;
        $this->categoryVariableBuilderPlugins = $categoryVariableBuilderPlugins;
    }

    /**
     * @param array $category
     * @param array $products
     *
     * @return array
     */
    public function getVariables(array $category, array $products): array
    {
        $categoryProducts = [];

        $GoogleAnalyticsCategoryTransfer = new GoogleAnalyticsCategoryTransfer();
        $GoogleAnalyticsCategoryTransfer->setIdCategory($category['id_category']);
        $GoogleAnalyticsCategoryTransfer->setName($category['name']);
        $GoogleAnalyticsCategoryTransfer->setSize(\count($products));

        foreach ($products as $product) {
            $GoogleAnalyticsCategoryProductTransfer = new GoogleAnalyticsCategoryProductTransfer();
            $GoogleAnalyticsCategoryProductTransfer->setIdProductAbstract($product['id_product_abstract']);
            $GoogleAnalyticsCategoryProductTransfer->setName($this->getProductName($product));
            $GoogleAnalyticsCategoryProductTransfer->setSku($product['abstract_sku']);
            $GoogleAnalyticsCategoryProductTransfer->setPrice($this->moneyPlugin->convertIntegerToDecimal($product['price']));

            $GoogleAnalyticsCategoryTransfer->addCategoryProducts($GoogleAnalyticsCategoryProductTransfer);

            $categoryProducts[] = $GoogleAnalyticsCategoryProductTransfer->toArray();
        }

        $variables = [
            GoogleAnalyticsConstants::CATEGORY_ID => $category['id_category'],
            GoogleAnalyticsConstants::CATEGORY_NAME => $category['name'],
            GoogleAnalyticsConstants::CATEGORY_SIZE => $GoogleAnalyticsCategoryTransfer->getCategoryProducts()->count(),
            GoogleAnalyticsConstants::CATEGORY_PRODUCTS => $categoryProducts,
            GoogleAnalyticsConstants::PRODUCTS => $GoogleAnalyticsCategoryTransfer->getProducts(),
        ];

        return $this->executePlugins($variables, $GoogleAnalyticsCategoryTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer|array $product
     *
     * @return string
     */
    protected function getProductName(array $product): string
    {
        if (!\array_key_exists('attributes', $product)) {
            return $product['abstract_name'];
        }

        if (isset($product['attributes'][GoogleAnalyticsConstants::NAME_UNTRANSLATED])) {
            return $product['attributes'][GoogleAnalyticsConstants::NAME_UNTRANSLATED];
        }

        return $product['abstract_name'];
    }

    /**
     * @param array $variables
     * @param \Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer
     *
     * @return array
     */
    protected function executePlugins(array $variables, GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer): array
    {
        foreach ($this->categoryVariableBuilderPlugins as $plugin) {
            $variables = array_merge($variables, $plugin->handle($GoogleAnalyticsCategoryTransfer));
        }

        return $variables;
    }
}
