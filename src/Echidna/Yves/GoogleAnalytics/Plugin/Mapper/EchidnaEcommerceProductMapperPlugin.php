<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\Mapper;

use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\ProductFieldMapperPluginInterface;
use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class EchidnaEcommerceProductMapperPlugin extends AbstractPlugin implements EchidnaEcommerceProductMapperInterface
{
    /**
     * @var array|ProductFieldMapperPluginInterface[]
     */
    protected $productFieldMapperPlugin;

    /**
     * @var EchidnaEcommerceProductTransfer
     */
    protected $EchidnaEcommerceProductTransfer;

    /**
     * @param ProductFieldMapperPluginInterface[] $fieldProductMapPlugins
     */
    public function __construct(array $fieldProductMapPlugins)
    {
        $this->EchidnaEcommerceProductTransfer = new EchidnaEcommerceProductTransfer();
        $this->productFieldMapperPlugin = $fieldProductMapPlugins;
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     * @param array $params
     *
     * @return EchidnaEcommerceProductTransfer
     */
    public function map(ProductViewTransfer $productViewTransfer, array $params = []): EchidnaEcommerceProductTransfer
    {
        $this->executePlugins($productViewTransfer, $params);

        return $this->EchidnaEcommerceProductTransfer;
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     * @param array $params
     * @return void
     */
    protected function executePlugins(ProductViewTransfer $productViewTransfer, array $params): void
    {
        foreach ($this->productFieldMapperPlugin as $plugin) {
            $plugin->map($productViewTransfer, $this->EchidnaEcommerceProductTransfer, $params);
        }
    }
}
