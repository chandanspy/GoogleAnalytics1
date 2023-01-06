# Google Analytics Manager integration for Spryker

Google Analytics Manager integration for Spryker


## Installation

```
composer require echidna/google-analytics
```

## 1. Add the Container ID in the configuration file 

```
$config[GoogleTagManagerConstants::CONTAINER_ID] = 'GTM-XXXX'; 
```

## 2. Enable the Module in the configuration file 
```
$config[GoogleTagManagerConstants::ENABLED] = true;
```

## 3. Include the namespace as a core namespace in the configuration file 
```
$config[KernelConstants::CORE_NAMESPACES] = [
    [...]
    'Echidna'
];
```

## 4. Add twig service provider to YvesBootstrap.php in registerServiceProviders()

```
$this->application->register(new GoogleTagManagerTwigServiceProvider());
```

## 5. Add the Twig Extension in the neccessary Twig Templates

```
  Application/layout/layout.twig 
  between <head></head> tags
  
  {% block googletagmanager_data_layer %} {{ dataLayer('other', {}) }}{% endblock %} 
  {{ googleTagManager('@GoogleTagManager/partials/tag.twig') }}
  
  after <body> tag
  {{ googleTagManager('@GoogleTagManager/partials/tag-noscript.twig') }}
```

```
  Catalog/catalog/index.twig 
  {% block googletagmanager_data_layer %}
      {% set params = { 'category' : category, 'products' : products} %}
      {{ dataLayer('category', params) }}
  {% endblock %}
```

```
  Product/product/detail.twig 
  {% block googletagmanager_data_layer %}
      {% set params = { 'product' : product} %}
      {{ dataLayer('product', params) }}
  {% endblock %}
```

```
  Cart/cart/index.twig 
  {% block googletagmanager_data_layer %}
      {{ dataLayer('cart', {}) }}
  {% endblock %}
```

```
  Checkout/checkout/partial/success.twig 
  {% block googletagmanager_data_layer %}
      {% set params = { 'order' : orderTransfer} %}
      {{ dataLayer('order', params) }}
  {% endblock %}
```

