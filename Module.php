<?php
namespace ZFTwiBoo;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
        $configuration = $this->getConfig();
        if (!isset($configuration['zftwiboo'])) {
            return array();
        }
        
        $array = array(
            'invokables' => array(
                'zftwiboolabel' => 'ZFTwiBoo\View\Helper\Label',
                'zftwiboobadge' => 'ZFTwiBoo\View\Helper\Badge',
            ),
        );
        if ($configuration['zftwiboo']['use_main_namespace']) {
            $array['invokables']['label'] = 'ZFTwiBoo\View\Helper\Label';
            $array['invokables']['badge'] = 'ZFTwiBoo\View\Helper\Badge';
        }
        
        $navigationHelpers = array(
            'invokables' => array(
                'zftwiboomenu'   => 'ZFTwiBoo\View\Helper\Navigation\Menu',
                'zftwiboonavbar' => 'ZFTwiBoo\View\Helper\Navigation\Navbar',
            ),
        );
        if ($configuration['zftwiboo']['override_zf_helpers']) {
            $navigationHelpers['invokables']['menu']   = 'ZFTwiBoo\View\Helper\Navigation\Menu';
            $navigationHelpers['invokables']['navbar'] = 'ZFTwiBoo\View\Helper\Navigation\Navbar';
        }
        $navigationHelpers = new \Zend\ServiceManager\Config($navigationHelpers);
        
        $array['factories']['navigation'] = function ($pm) use ($navigationHelpers) {
            $helper = new \Zend\View\Helper\Navigation;
            $pluginManager = new \Zend\View\Helper\Navigation\PluginManager($navigationHelpers);
            $pluginManager->setServiceLocator($pm->getServiceLocator());
            $pluginManager->setRenderer($pm->getRenderer());
            $helper->setPluginManager($pluginManager);
            return $helper;
        };
        
        return $array;
    }
}
