<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory;
use Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductModelBuilderInterface;
use Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class EchidnaEcommerceCartPluginTest extends Unit
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factoryMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCartPlugin
     */
    protected $plugin;

    /**
     * @var \Twig_Environment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $twigEnvironmentMock;

    /**
     * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Session\EchidnaEcommerceSessionHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHandlerMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce\ProductModelBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productArrayBuilderMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productStorageClientMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @return void
     */
    protected function _before()
    {
        parent::_before();

        $this->factoryMock = $this->getMockBuilder(GoogleAnalyticsFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(GoogleAnalyticsConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->method('getEchidnaEcommerceLocale')->willReturn('en_US');

        $this->twigEnvironmentMock = $this->getMockBuilder(Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionHandlerMock = $this->getMockBuilder(EchidnaEcommerceSessionHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productArrayBuilderMock = $this->getMockBuilder(ProductModelBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productStorageClientMock = $this->getMockBuilder(GoogleAnalyticsToProductStorageClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new EchidnaEcommerceCartPlugin();
        $this->plugin->setFactory($this->factoryMock);
        $this->plugin->setConfig($this->configMock);
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
    public function testGetAddedProductsEventSuccess(): void
    {
        $addedProductsArray = include codecept_data_dir('AddedProductsArray.php');
        $addedProductsCompleteArray = include codecept_data_dir('AddedProductsCompleteArray.php');

        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->sessionHandlerMock->expects($this->atLeastOnce())
            ->method('getAddedProducts')
            ->willReturn($addedProductsArray);

        $this->productArrayBuilderMock->expects($this->atLeastOnce())
            ->method('handle')
            ->willReturn($addedProductsCompleteArray);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('createEchidnaEcommerceSessionHandler')
            ->willReturn($this->sessionHandlerMock);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('createEchidnaEcommerceProductArrayBuilder')
            ->willReturn($this->productArrayBuilderMock);

        $this->productArrayBuilderMock->expects($this->atLeastOnce())
            ->method('handle')
            ->with($addedProductsArray)
            ->willReturn($addedProductsCompleteArray);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);

        $methodGetProductFromQuote = $this->getMethod('getAddedProductsEvent');
        $result = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertNotCount(0, $result['ecommerce']['add']['products']);
    }

    /**
     * @return void
     */
    public function testGetAddedProductsEventFailureNoProductInSession(): void
    {
        $addedProductsArray = [];

        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->sessionHandlerMock->expects($this->atLeastOnce())
            ->method('getAddedProducts')
            ->willReturn($addedProductsArray);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('createEchidnaEcommerceSessionHandler')
            ->willReturn($this->sessionHandlerMock);

        $this->productArrayBuilderMock->expects($this->never())
            ->method('handle')
            ->with($addedProductsArray);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);

        $methodGetProductFromQuote = $this->getMethod('getAddedProductsEvent');
        $result = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertCount(0, $result);
    }

    /**
     * @return void
     */
    public function testGetRemovedProductsEventSuccess(): void
    {
        $removedProductsCompleteArray = include codecept_data_dir('RemovedProductsArray.php');
        $productAbstractDataArray = include codecept_data_dir('ProductAbstractDataArray.php');

        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getProductStorageClient')
            ->willReturn($this->productStorageClientMock);

        $this->sessionHandlerMock->expects($this->atLeastOnce())
            ->method('getRemovedProducts')
            ->willReturn($removedProductsCompleteArray);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('createEchidnaEcommerceSessionHandler')
            ->willReturn($this->sessionHandlerMock);

        $this->productStorageClientMock->expects($this->atLeastOnce())
            ->method('findProductAbstractStorageData')
            ->with($productAbstractDataArray['id_product_abstract'], 'en_US')
            ->willReturn($productAbstractDataArray);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);

        $methodGetProductFromQuote = $this->getMethod('getRemovedProducts');
        $products = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertNotCount(0, $products);
    }

    /**
     * @return void
     */
    public function testGetRemovedProductsEventFailureNoProductInSession(): void
    {
        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->factoryMock->expects($this->never())
            ->method('getProductStorageClient')
            ->willReturn($this->productStorageClientMock);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);

        $methodGetProductFromQuote = $this->getMethod('getRemovedProducts');
        $products = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertCount(0, $products);
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
        $class = new ReflectionClass(EchidnaEcommerceCartPlugin::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
