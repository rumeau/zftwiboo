<?php
namespace ZFTwiBoo\View\Helper\Navigation;

use Zend\View\Helper\Navigation\PluginManager as ZendPluginManager;
use Zend\ServiceManager\ConfigInterface;

class PluginManager extends ZendPluginManager
{
    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached renderer and translator, if any, to the currently requested helper.
     *
     * @param  null|ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
    }
}
