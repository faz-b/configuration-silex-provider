<?php

namespace Fazb\Silex\Configuration;

use Fazb\Silex\Configuration\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
* Configuration class
*/
class Configuration extends ParameterBag implements EventSubscriberInterface
{
    protected $protected_namespaces = array(
        'global',
        'settings'
    );

    protected $namespaces = array();

    protected $default_namespace = 'settings';

    protected $current_name = 'current';

    /**
     * Current route name given by listening to the onKernelRequest event
     *
     * @var null
     */
    protected $current = null;

    public function __construct($parameters = array())
    {
        $this->namespaces = array_unique(array_merge(array_keys($parameters), $this->protected_namespaces));

        parent::__construct($parameters);
    }

    public function get($path, $default = null, $deep = true)
    {
        $path = $this->convert($path);

        return parent::get($path, $default, $deep);
    }

    public function find($namespace, $path, $default = null)
    {
        return $this->get(sprintf('%s.%s', $namespace, $path), $default);
    }

    /**
     * Parse path and convert to the square bracket notation used by the ParameterBag class
     *
     * @param  string $path the tree path
     * @return string       the converted path
     */
    protected function convert($path)
    {
        // replace "current" key by the current route name matched
        if (false !== strpos($path, $this->current_name)) {
            $path = preg_replace('#' . $this->current_name . '#', $this->current, $path);
        }

        if ($parts = explode('.', $path)) {
            $key   = array_shift($parts);
            if (!in_array($key, $this->namespaces)) {
                array_unshift($parts, $key);
                $key = $this->default_namespace;
            }

            $parts = array_map(function ($part) {
                return sprintf('[%s]', $part);
            }, $parts);

            $path = join('', $parts);
            $path = $key . $path;
        }

        return $path;
    }

    public function __call($method, $arguments)
    {
        if ($method != $this->current_name && !in_array($method, $this->namespaces)) {
            throw new \LogicException(sprintf('Namespace "%s" does not exist.', $method));
        }

        return call_user_func_array(array($this, 'find'), array_merge(array($method), $arguments));
    }

    public function getCurrentRouteName()
    {
        return $this->current;
    }

    protected function setPageId(Request $request)
    {
        $page_id = $this->get('current.page_id', $this->current);
        $request->attributes->set('page_id', $page_id);
    }

    /**
     * Runs before filters.
     *
     * @param GetResponseEvent $event The event to handle
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->current = null;

        $request = $event->getRequest();

        $this->current = $request->attributes->get('_route');

        $this->setPageId($request);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1))
        );
    }
}
