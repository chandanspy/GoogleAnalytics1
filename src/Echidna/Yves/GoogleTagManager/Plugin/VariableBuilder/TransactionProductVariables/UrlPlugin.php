<?php


namespace Echidna\Yves\GoogleTagManager\Plugin\VariableBuilder\TransactionProductVariables;

use Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig;
use Generated\Shared\Transfer\ItemTransfer;

class UrlPlugin implements TransactionProductVariableBuilderPluginInterface
{
    public const FIELD_NAME = 'url';

    /**
     * @var \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig
     */
    protected $config;

    /**
     * UrlPlugin constructor.
     *
     * @param \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig $config
     */
    public function __construct(GoogleTagManagerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return array
     */
    public function handle(ItemTransfer $itemTransfer, array $params = []): array
    {
        if (!isset($params['locale'])) {
            return [];
        }

        $locale = $params['locale'];

        if ($this->getUrlKey($itemTransfer, $locale) === null) {
            return [];
        }

        return [static::FIELD_NAME => \sprintf('%s/%s/%s', $this->getHost(), $this->getUrlLanguageKey($locale), $this->getUrlKey($itemTransfer, $locale))];
    }

    /**
     * @return string
     */
    protected function getHost(): string
    {
        $hostName = $_SERVER['HTTP_HOST'];

        return $this->config->getProtocol() . '://' . $hostName;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    protected function getUrlLanguageKey(string $locale): string
    {
        $locale = \explode('_', $locale);

        return $locale[0];
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $product
     * @param string $locale
     *
     * @return string|null
     */
    protected function getUrlKey(ItemTransfer $product, string $locale): ?string
    {
        if (!isset($product->getAbstractAttributes()[$locale]['url_key'])) {
            return null;
        }

        return $product->getAbstractAttributes()[$locale]['url_key'];
    }
}
