<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author Chandan Kumar <ranjanpratik@yahoo.in>
 */
namespace Echidna\Yves\GoogleAnalytics\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class GoogleAnalyticsTwigServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $GoogleAnalyticsTwigExtension = $this
            ->getFactory()
            ->createGoogleAnalyticsTwigExtension();

        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (Twig_Environment $twig) use ($GoogleAnalyticsTwigExtension, $app) {
                    $twig->addExtension($GoogleAnalyticsTwigExtension);

                    return $twig;
                }
            )
        );
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
    }
}
