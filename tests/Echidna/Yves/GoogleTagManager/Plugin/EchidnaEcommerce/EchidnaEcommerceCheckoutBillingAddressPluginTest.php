<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Codeception\Test\Unit;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToProductStorageClientInterface;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig;
use Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class EchidnaEcommerceCheckoutBillingAddressPluginTest extends Unit
{
    /**
     * @var \Twig_Environment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $twigEnvironmentMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommerceCheckoutBillingAddressPlugin
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
     * @var \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Generated\Shared\Transfer\ItemTransfer[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected $itemTransferListMock;

    /**
     * @var \Generated\Shared\Transfer\QuoteTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteTransferMock;

    /**
     * @var \Generated\Shared\Transfer\ItemTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemTransferMock;

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartClientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storageClientMock;

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

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEnvironmentMock = $this->getMockBuilder(Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemTransferMock = $this->getMockBuilder(ItemTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteTransferMock = $this->getMockBuilder(QuoteTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartClientMock = $this->getMockBuilder(GoogleAnalyticsToCartClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storageClientMock = $this->getMockBuilder(GoogleAnalyticsToProductStorageClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEnvironmentMock
            ->method('render')
            ->willReturn('');

        $this->plugin = new EchidnaEcommerceCheckoutBillingAddressPlugin();
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
    public function testhandleSucces(): void
    {
        $productAbstractDataArray = include codecept_data_dir('ProductAbstractDataArray.php');

        $this->itemTransferMock->method('getIdProductAbstract')->willReturn(53);
        $this->itemTransferMock->method('getUnitPrice')->willReturn(3999);
        $this->itemTransferMock->method('getQuantity')->willReturn(1);

        $this->itemTransferListMock = [$this->itemTransferMock];

        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($this->itemTransferListMock);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getCartClient')
            ->willReturn($this->cartClientMock);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getProductStorageClient')
            ->willReturn($this->storageClientMock);

        $this->storageClientMock->expects($this->atLeastOnce())
            ->method('findProductAbstractStorageData')
            ->with(53, 'en_US')
            ->willReturn($productAbstractDataArray);

        $methodGetProductFromQuote = $this->getMethod('renderCartViewProducts');
        $products = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertNotCount(0, $products);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);
    }

    /**
     * @return void
     */
    public function testhandleFailure(): void
    {
        $this->cartClientMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteTransferMock);

        $this->quoteTransferMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([]);

        $this->factoryMock->expects($this->atLeastOnce())
            ->method('getCartClient')
            ->willReturn($this->cartClientMock);

        $this->factoryMock->expects($this->never())
            ->method('getProductStorageClient');

        $methodGetProductFromQuote = $this->getMethod('renderCartViewProducts');
        $products = $methodGetProductFromQuote->invokeArgs($this->plugin, []);
        $this->assertCount(0, $products);

        $this->plugin->handle($this->twigEnvironmentMock, $this->requestMock, []);
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
        $class = new ReflectionClass(EchidnaEcommerceCheckoutBillingAddressPlugin::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
