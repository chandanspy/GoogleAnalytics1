<?php


namespace Echidna\Yves\GoogleAnalytics\Model\DataLayer;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;

class NewsletterVariableBuilder
{
    /**
     * @var array
     */
    protected $newsletterVariableBuilderPlugins;

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\NewsletterVariables\NewsletterVariablesPluginInterface[] $defaultVariableBuilderPlugins
     */
    public function __construct(array $newsletterVariableBuilderPlugins)
    {
        $this->newsletterVariableBuilderPlugins = $newsletterVariableBuilderPlugins;
    }

    /**
     * @param string $page
     *
     * @return array
     */
    public function getVariables(string $page): array
    {
        $variables = [
            'pageType' => GoogleAnalyticsConstants::PAGE_TYPE_NEWSLETTER_SUBSCRIBE,
        ];

        return $this->executePlugins($variables);
    }

    /**
     * @param array $variables
     * @param \Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer
     *
     * @return array
     */
    protected function executePlugins(array $variables): array
    {
        foreach ($this->newsletterVariableBuilderPlugins as $plugin) {
            $variables = \array_merge($variables, $plugin->handle($variables));
        }

        return $variables;
    }
}
