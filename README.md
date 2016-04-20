# Simple configuration for Silex App

This approach assume that you will use **Twig** as the template engine, and that you will use the __ServicerouteServiceProvider__ to build your routes. It is designed for simple use case, where writing and maintaining routes, views code can be cumbersome. Do not use at home.

## Example of a configuration using a .json file

    {
        "global": {
            "title": "Acme Site - "
        },
        "settings": {
            "homepage": {
                "route": {
                    "controller" : "page.route:indexAction",
                    "pattern": "/",
                    "method" : "GET"
                },
                "view": {
                    "title": "Homepage",
                    "metas": {
                        "keywords": "keyword, keyword, keyword",
                        "description": "some seo description..."
                    }
                }
            },
            "contact":
    ...
    }

## Or with a yaml file :

    global:
        title: 'Acme Site - '

    settings:
        homepage:
            route:
                controller: page.controller:indexAction
                pattern: "/"
            view:
                title:      "Homepage"
                metas:
                    keywords: "keyword, keyword, keyword"
        contact:
            route:
                pattern:    "/contact"
            view:
                template:   "page/contact.html"
                title:      "Contact"


This configuration allows to setup a few common and redundant things that happens in a silex workflow app. It gives you a central point to configure the different layers of your application (route, view, global).

Moreover, it gives you a simple way to access config var within a twig template using the ```get``` method as it extends the _ParameterBag_ class. As an extra cherry, it uses the dot notation to access sub tree array, which can be more convenient than the square bracket notation.


    <h1>{{ app.config.get('current.view.title') }}</h1>


The **current** keyword is replaced by the current matched route name internally, each time it is met during the parsing process.

The default main key is **settings** if none provided.

## Conventions

### Root entry keys and tree access

* global
* settings

You can create any other root key you may want. The _settings_ keyword is a reserved key. _global_ is given as an extra entry to store any other settings you want.

You can also use shortcuts methods like

    {{ app.config.current('view.title') }}

Which will default to settings.current.view.title

Or use any root key as a calling method

    {{ app.config.global('title') }}


#### The "global" key and its _sub-sections_

Defines any global variable at will

eg:

* global

    * title


```<title>{% block title %}{{ app.config.global('title') }}{% endblock %}</title>```

#### The "settings" key and its _sub-sections_

The _route_ section allows you to map route definition to a specific controller, or having it be automatically generated.

* route_name

    * route:
        * controller:        (xxx.controller:indexAction)
        * pattern:       (pattern to match)
        * method:        (http methods)

    * view:
        * template:      (path to twig template, overrided by route.controller key if defined)
        * title:
        * metas:
            * keywords:
            * description:


For simple static page, a common use case would be to define the view.template value,
which will automatically generate a simple closure route that will return the result of the twig render call.


#### The "Navigation" key exemple and its _sub-sections_

* navigation
    * route_name
        * label
        * href (if none defined the route_name is used using the {{ path() }} helper)
        * childs

eg:

    {{ app.config.navigation('current.label') }}

You can freely structure as you mind.

## Internals

This is your responsability to pass a configuration tree array to the _SiteConfigurationServiceProvider_.

    $configuration = json_decode(file_get_contents(__DIR__ . '/Site/Resource/config/config.json'), true);
    $app->register(new SiteConfigurationServiceProvider(), array('site.config' => $configuration));

Using plain php object with the _ServicerouteServiceProvider_

    class Pageroute
    {
        protected $container;

        public function __construct(Application $container)
        {
            $this->container = $container;
        }

        /**
         * @return Response
         */
        public function indexAction()
        {
            return $this->container['twig']->render('@site/page/index.html');
        }
    }


## TODO

* Finish the tree variables definition
* Enforce config file definition by using sf2 treebuilder
* Tests

---

inspired by the silex-skeleton repository by Fabien Potencier

