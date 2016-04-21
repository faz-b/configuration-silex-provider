# Simple configuration for Silex App

_Rapid routing, view and global parameters definition through a human readable structured config file._

Once you choose your preferred parsable data format, register the **ConfigurationServiceProvider**.

    # src/app.php

    # $configuration = json_decode(file_get_contents(__DIR__ . '/Site/Resource/config/config.json'), true);
    $configuration = Yaml::parse(file_get_contents(__DIR__ . '/Site/Resources/config/config.yml'));
    $app->register(new ConfigurationServiceProvider(), array('site.config' => $configuration));

## The ```settings``` key

Allows to define your routes and view variables. The first key, under the ```settings``` key is your route name. All routes defined here are registered with the ```Silex\ControllerCollection```.

    # src/Site/Resources/config/config.yml

    settings:
        homepage:
            route:
                controller:     default.controller:indexAction
                pattern:        /
                # other Silex route parameters...
            view:
                title:          homepage.title
                metas:
                    keywords:  "keyword, keyword, keyword"
        contact:
            route:
                pattern:        /contact
                method:         GET|POST
            view:
                template:       page/contact.html
                title:          contact.title


When no controller is given (eg: contact route), a default closure will be generated, returning the given template.

The configuration can be accessed in your twig template through the following syntax :

    <h1>{{ app.config.get('homepage.view.title')|trans }}</h1>

As a convenience, you can use the ```current``` shortcut to access the settings for the current matched route

    <h1>{{ app.config.current('view.title')|trans }}</h1>

    {# equivalent #}

    <h1>{{ app.config.settings('homepage.view.title')|trans }}</h1>

The same applies inside a controller

    public function indexAction(Request $request)
    {
        $title = $this->container['config']->current('view.title', 'Default title');

        return $this->container['twig']->render('@site/Default/index.html');
    }

## The ```global``` key

It is just a convention for settings your others application parameters.

    # src/Site/Resources/config/config.yml
    global:
        email:                  contact@localhost
        domain:                 localhost
        brand:                  brandname
        analytics_id:           UA-xxxxx
        facebook_url:           https://www.facebook.com/xxx
        linkedin_url:           https://www.linkedin.com/pub/xxx
        view:
            title:              " - default title suffix"
            metas:
                description:    "Silex app"

In a twig template:

    # index.html

    <title>{% block title %}{{ app.config.current('view.title') }}{% endblock %}{{ app.config.global('view.title')}}</title>

    ...

    <a href="mailto:{{ app.config.global('email') }}">email me</a>


*the __settings__ and __global__ keys are reserved*


## A ```navigation``` section example

    settings:
    ...

    navigation:
        homepage:
            label:              homepage.label
            childs:
                contact:
                    label:      contact.label


In a twig template:

    {{ app.config.navigation('current.label') }}

### Typical usecase using the bootstrap framework

    {% import "@site/macro/navigation.html" as macros %}

    <div id="navigation" class="navbar navbar-default navbar-fixed-top navbar-inverse shadow-bottom" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a href="{{ path('homepage') }}" class="navbar-brand" title="{{ app.config.global('brand')|trans }}">
            <span class="icon-logo"></span>
            <span class="suffix">{{ app.config.global('brand')|trans }}</span>
          </a>
        </div>
        <div id="scrollspy" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            {{ macros.menu(app.config.navigation('homepage.childs')) }}
          </ul>
          {% if app.locales %}
          <ul class="nav navbar-nav navbar-right">
            {% for locale in app.locales %}
            <li{% if app.request.get('_locale') == locale %} class="active"{% endif %}>
              <a hreflang="{{locale}}" href="{{path(app.request.get('_route'), {_locale: locale})}}">{{locale|upper}}</a>
            </li>
            {% endfor %}
          </ul>
          {% endif %}

        </div>
      </div>
    </div>


## Full config.yml example

    global:
        email:                  contact@localhost
        domain:                 localhost
        brand:                  brand.name
        analytics_id:           UA-xxxxx
        facebook_url:           https://www.facebook.com/xxx
        linkedin_url:           https://www.linkedin.com/pub/xxx
        view:
            title:              view.title
            suffix:             view.title.suffix
            metas:
                description:    view.metas.description
    settings:
        homepage:
            route:
                controller: default.controller:indexAction
                pattern:    /
            view:
                title:      home.title
                metas:
                    keywords:    ~
                    description: ~
        contact:
            route:
                controller: default.controller:contactAction
                pattern:    /contact
            view:
                title:      contact.title
        test:
            route:
                pattern:    /test
                i18n:       false
            view:
                template:   '@site/Default/test.html'

    navigation:
        homepage:
            label:          home.label
            childs:
                contact:
                    label:  contact.label
                test:
                    label:  test.label

---

F/\Z-B 2014
