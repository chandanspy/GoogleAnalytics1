<?php

namespace Echidna\Yves\GoogleTagManager\Plugin\Mapper;

use Echidna\Yves\GoogleTagManager\Dependency\EnhancedEcommerceProductMapperInterface;
use Generated\Shared\Transfer\EnhancedEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 */
class EnhancedEcommerceProductMapperPlugin extends AbstractPlugin implements EnhancedEcommerceProductMapperInterface
{
    /**
     * @var array|\Echidna\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\ProductFieldMapperPluginInterface[]
     */
    protected $productFieldMapperPlugin;

    /**
     * @var \Generated\Shared\Transfer\EnhancedEcommerceProductTransfer
     */
    protected $enhancedEcommerceProductTransfer;

    /**
     * @param \Echidna\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\ProductFieldMapperPluginInterface[] $fieldProductMapPlugins
     */
    public function __construct(array $fieldProductMapPlugins)
    {
        $this->enhancedEcommerceProductTransfer = new EnhancedEcommerceProductTransfer();
        $this->productFieldMapperPlugin = $fieldProductMapPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param array $params
     *
     * @return \Generated\Shared\Transfer\EnhancedEcommerceProductTransfer
     */
    public function map(ProductViewTransfer $productViewTransfer, array $params = []): EnhancedEcommerceProductTransfer
    {
        $this->executePlugins($productViewTransfer, $params);

        return $this->enhancedEcommerceProductTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return void
     */
    protected function executePlugins(ProductViewTransfer $productViewTransfer, array $params): void
    {
        foreach ($this->productFieldMapperPlugin as $plugin) {
            $plugin->map($productViewTransfer, $this->enhancedEcommerceProductTransfer, $params);
        }
    }
}
