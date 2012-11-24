<?php
namespace ZFTwiBoo\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Router\RouteStackInterface as Router;
use Zend\Mvc\Router\RouteMatch;

class ConstructRouterNavigation
{
    /**
     * @var array
     */
    public $pages;
    
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    public function __construct($config, ServiceLocatorInterface $serviceLocator)
    {
        $pages = $this->getPages($config, $serviceLocator);
        return $this;
    }
    
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     * @throws \Zend\Navigation\Exception\InvalidArgumentException
    */
    public function getPages($config, ServiceLocatorInterface $serviceLocator)
    {
        if (null === $this->pages) {
            $application = $serviceLocator->get('Application');
            $routeMatch  = $application->getMvcEvent()->getRouteMatch();
            $router      = $application->getMvcEvent()->getRouter();
            
            $this->pages = $this->injectComponents($config, $routeMatch, $router);
        }
        return $this->pages;
    }
    
    /**
     * @param array $pages
     * @param RouteMatch $routeMatch
     * @param Router $router
     * @return mixed
     */
    protected function injectComponents(array $pages, RouteMatch $routeMatch = null, Router $router = null)
    {
        foreach ($pages as &$page) {
            $hasMvc = isset($page['action']) || isset($page['controller']) || isset($page['route']);
            if ($hasMvc) {
                if (!isset($page['routeMatch']) && $routeMatch) {
                    $page['routeMatch'] = $routeMatch;
                }
                if (!isset($page['router'])) {
                    $page['router'] = $router;
                }
            }
    
            if (isset($page['pages'])) {
                $page['pages'] = $this->injectComponents($page['pages'], $routeMatch, $router);
            }
        }
        return $pages;
    }
}