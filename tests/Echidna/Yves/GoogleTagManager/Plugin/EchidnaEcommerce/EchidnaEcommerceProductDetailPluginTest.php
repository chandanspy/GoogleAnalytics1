<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class EchidnaEcommerceProductDetailPluginTest extends Unit
{
    /**
     * @var \Twig_Environment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $twigEnvironmentMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceProductDetailPlugin
     */
    protected $plugin;

    /**
     * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factoryMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\EchidnaEcommerceProductMapperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMapperPluginMock;

    /**
     * @return void
     */
    protected function _before()
    {
        parent::_before();

        $this->productMapperPluginMock = $this->getMockBuilder(EchidnaEcommerceProductMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock = $this->getMockBuilder(GoogleAnalyticsFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEnvironmentMock = $this->getMockBuilder(Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->plugin = new EchidnaEcommerceProductDetailPlugin();
        $this->plugin->setFactory($this->factoryMock);
    }

    /**
     * @return void
     */
    public function testGetTemplate(): void
    {
        $this->assertEquals('@GoogleAnalytics/partials/echidna-ecommerce-default.twig', $this->plugin->getTemplate());
    }

    /**
     * @return void
     */
    public function testhandleSucces(): void
    {
        $productViewTransfer = include codecept_data_dir('ProductViewTransfer.php');
        $EchidnaEcommerceProductTransfer = include codecept_data_dir('EchidnaEcommerceProductTransfer.php');

        $this->productMapperPluginMock->expects($this->atLeastOnce())
            ->method('map')
            ->with($productViewTransfer)
            ->willReturn($EchidnaEcommerceProductTransfer);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getEchidnaEcommerceProductMapperPlugin')
            ->willReturn($this->productMapperPluginMock);

        $methodGetProductFromQuote = $this->getMethod('renderProductDetail');
        $result = $methodGetProductFromQuote->invokeArgs($this->plugin, [[$EchidnaEcommerceProductTransfer->toArray()]]);
        $this->assertNotCount(0, $result['ecommerce']['detail']['products']);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, ['product' => $productViewTransfer]);
    }

    /**
     * @param string $name
     *
     * @throws
     *
     * @return \ReflectionMethod
     */
    protected function getMethod(string $name)
    {
        $class = new ReflectionClass(EchidnaEcommerceProductDetailPlugin::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
