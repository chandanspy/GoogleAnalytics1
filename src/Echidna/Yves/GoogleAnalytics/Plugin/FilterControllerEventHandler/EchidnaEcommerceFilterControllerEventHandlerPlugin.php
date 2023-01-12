<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\FilterControllerEventHandler;

use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Yves\ShopApplicationExtension\Dependency\Plugin\FilterControllerEventHandlerPluginInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()()
 */
class EchidnaEcommerceFilterControllerEventHandlerPlugin extends AbstractPlugin implements FilterControllerEventHandlerPluginInterface
{
    protected function checkForValidController(string $className): bool
    {
        foreach ($this->getConfig()->getListenToControllersEchidnaEcommerce() as $controller) {
            if (\strpos($className, $controller) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     *
     * @return void
     */
    public function handle(FilterControllerEvent $event): void
    {
        if (!\is_array($event->getController())) {
            return;
        }

        if ($this->checkForValidController(\get_class($event->getController()[0])) === false) {
            return;
        }

        $cartControllerEventHandler = $this->getFactory()
            ->getCartControllerEventHandler();

        foreach ($cartControllerEventHandler as $controllerEventHandler) {
            if ($controllerEventHandler->getMethodName() === $event->getController()[1]) {
                $controllerEventHandler->handle($event->getRequest(), $this->getConfig()->getEchidnaEcommerceLocale());
            }
        }
    }
}
