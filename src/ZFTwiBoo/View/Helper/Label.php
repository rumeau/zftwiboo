<?php
namespace ZFTwiBoo\View\Helper;

use ZFTwiBoo\View\Helper\AbstractStyleHelper;
use Zend\View\Exception\BadMethodCallException;

class Label extends AbstractStyleHelper
{
    /**
     * @var string     Text to be displayed
     */
    public $text;
    
    /**
     * @var string     Style Type to render
     */
    public $type;
    
    /**
     * Retrive the object to call overloaded methods, or render the label
     * 
     * @param string     Text to be displayed as content
     * @param string     Style Type to render
     * @return \ZFTwiBoo\View\Helper\Label
     */
    public function __invoke($text = null, $type ='default')
    {
        $this->type = $type;
        $this->text = $text;
        return $this;
    }
    
    /**
     * Overload method access
     *
     * Allows the following method calls:
     * - default($text)
     * - success($text)
     * - warning($text)
     * - important($text)
     * - info($text)
     * - inverse($text)
     *
     * @param  string $method Method to call
     * @param  array  $args   Arguments of method
     * @return \ZFTwiBoo\View\Helper\Label
     * @throws \Zend\View\Exception\BadMethodCallException if too few arguments
     */
    public function __call($method, $args)
    {
        if (!in_array($method, $this->styleTypes)) {
            $method = 'default';
        }
        
        if (0 == count($args)) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" requires at least one argument, an text to display',
                $method
            ));
        }
        
        $text = $args[0];
        
        $escaper = $this->view->plugin('escapeHtml');
        $this->text = $escaper($text);
        $this->type = $method;
        return $this;
    }    
        
    /**
     * Renders de badge
     *
     * @return string
     */
    public function toString()
    {
        return sprintf($this->template, $this->type, $this->text);
    }
    
    /**
     * Magic overload: Proxy to {@link toString()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link HelperInterface::__toString()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
            return '';
        }
    }
}