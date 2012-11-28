<?php
namespace ZFTwiBoo\View\Helper\Navigation;

use ZFTwiBoo\Navigation\ConstructRouterNavigation;
use Zend\Navigation\Navigation;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\Navigation\AbstractContainer;
use Zend\View\Helper\Navigation\AbstractHelper;

class Navbar extends AbstractHelper
{
    /**
     * @var Navigation|string|array  Navigation Container, array of pages or an html string
     */
    protected $containerRight;
    
    /**
     * @var array|string    An array of options or a brand name
     */
    protected $brand;
    
    /**
     * @var bool     Set responsive mode for the navbar
     */
    protected $responsive = true;
    
    /**
     * @var string|int     Navbar position or class
     */
    protected $position = 'navbar-fixed-top';
    
    /**
     * Available positions
     */
    const NAVBAR_POSITION_FIXED_TOP    = 1;
    const NAVBAR_POSITION_FIXED_BOTTOM = 2;
    const NAVBAR_POSITION_STATIC_TOP   = 3;
    
    /**
     * @var array     Custom options
     */
    public $options = array();
    
    /**
     * 
     * @param Navigation|array
     * @param Navigation|array|string
     * @param array|string         $brand
     * @param array                $options
     * @return Navbar
     */
    public function __invoke($containerLeft = null, $containerRight = null, $brand = array(), $options = array())
    {
        if ($containerLeft !== null) {
            $this->setContainerLeft($containerLeft);
        }
        if ($containerRight !== null) {
            $this->setContainerRight($containerRight);
        }
        
        $this->setBrand($brand);
        $this->setOptions($options);
        
        return $this;
    }
    
    /**
     * Set the navigation container for the left block of the navbar
     * 
     * @param Navigation|array     $container
     * @return Navbar
     */
    public function setContainerLeft($container)
    {
        // If is an array of pages, create the container
        if (is_array($container)) {
            $container = new Navigation($container);
        } elseif ($container == false) {
            $this->container = false;
            return $this;
        }
        $this->setContainer($container);
        return $this;
    }
    
    /**
     * Set the Content for the right block of the navbar
     * 
     * @param Navigation|array|string        $container
     * @return Navbar
     */
    public function setContainerRight($container)
    {
        // If is an array of pages, create the container
        if (is_array($container)) {
            $pages = new ConstructRouterNavigation($container, $this->getServiceLocator()->getServiceLocator());
            $container = new Navigation($pages->pages);
        }
        $this->containerRight = $container;
        return $this;
    }
    
    /**
     * Set the brand element of the navbar
     * 
     * @param array    $brand
     * @return Navbar
     */
    public function setBrand($brand = array())
    {
        if (!is_array($brand)) {
            $brand = array($brand);
        }
        
        if (count($brand) == 1) {
            // Set only the title of the brand
            $title = array_shift($brand);
            $this->brand = sprintf("<a class=\"brand\" href=\"#\" title=\"%s\">%s</a>", $title, $title);
        } elseif (count($brand) == 2) {
            // Build the brand element with options provided
            $this->brand = $this->buildBrand($brand);
        } else {
            // If no brand is provided omit this element
            $this->brand = '';
        }
        return $this;
    }
    
    /**
     * Set Navbar options
     * 
     * @param array     $options
     * @return Navbar
     */
    public function setOptions($options = array())
    {
        if (isset($options['responsive'])) {
            $this->setResponsive((bool) $options['responsive']);
            unset($options['responsive']);
        }
        if (isset($options['position'])) {
            $this->setPosition($options['position']);
            unset($options['position']);
        }
        $this->options = $options;
        
        return $this;
    }
    
    /**
     * Set responsive mode for the navbar
     * 
     * @param bool        $flag
     * @return Navbar
     */
    public function setResponsive($flag)
    {
        $this->responsive = $flag;
        return $this;
    }
    
    /**
     * Set the position of the navbar
     * 
     * @param int|string         $position
     * @return Navbar
     */
    public function setPosition($position)
    {
        if ($position === self::NAVBAR_POSITION_FIXED_TOP) {
            $this->position = 'navbar-fixed-top';
        } elseif ($position === self::NAVBAR_POSITION_FIXED_BOTTOM) {
            $this->position = 'navbar-fixed-bottom';
        } elseif ($position === self::NAVBAR_POSITION_STATIC_TOP) {
            $this->position = 'navbar-static-top';
        } else {
            // If position is not one of the standard positions
            // set position as a custom class name
            $this->position = $position;
        }
        return $this;
    }
    
    /**
     * Builds the brand element based on options
     * 
     * @param array         $brand
     * @return string
     */
    protected function buildBrand($brand = array())
    {
        $attribs = array(
            'class' => 'brand',
        );
        
        $escapeLabel = true;
        if (isset($brand['escapeLabel'])) {
            // escape label option
            $escapeLabel = (bool) $brand['escapeLabel'];
            unset($brand['escapeLabel']);
        }
        
        // Build html attributes
        foreach ($brand as $key => $val) {
            if (strtolower($key) == 'title') {
                // Title attribute
                $title = $val;
                $attribs['title'] = $val;
            } elseif (strtolower($key) == 'href') {
                // href attribute
                $attribs['href'] = $val;
            } else {
                if (strtolower($key) == 'class') {
                    // Merge class attribute with custom ones
                    if ($val == 'brand') {
                        continue;
                    } else {
                        $attribs['class'] = $attribs['class'] . ' ' . $val;
                    }
                } else {
                    // Add custom attributes
                    $attribs[$key] = $val;
                }
            }
        }
        
        // Build html
        $html = '<a' . $this->htmlAttribs($attribs) . '>';
        if ($escapeLabel === true) {
            $escaper = $this->view->plugin('escapeHtml');
            $html .= $escaper($title);
        } else {
            $html .= $title;
        }
        $html .= '</a>';
        
        return $html;
    }
    
    /**
     * Renders the Navbar
     * 
     * @return string
     */
    public function render($container = null)
    {
        $html = '';
        
        $class = 'navbar ' . $this->position;
        if (isset($this->options['theme'])) {
            $class .= ' navbar-' . $this->options['theme'];
        }
        // Add custom class to navbar
        if (!isset($this->options['attributes'])) {
            $this->options['attributes'] = array(
                'class' => $class
            );
        } else {
            if (isset($this->options['attributes']['class'])) {
                $class .= ' ' . $this->options['attributes']['class'];
                $this->options['attributes']['class'] = $class; 
            }
        }
        $attributes = $this->htmlAttribs($this->options['attributes']);
        // open navbar
        $html .= "<div" . $attributes . ">\n    <div class=\"navbar-inner\">\n";
        if ($this->responsive) {
            // open .container
            $html .= "        <div class=\"container\">\n";
            // add collapser button
            $html .= "            <a class=\"btn btn-navbar\" data-toggle=\"collapse\" data-target=\".nav-collapse\">\n"
                   . "                <span class=\"icon-bar\"></span>\n"
                   . "                <span class=\"icon-bar\"></span>\n"
                   . "                <span class=\"icon-bar\"></span>\n"
                   . "            </a>\n";
        }
        
        // add brand
        $html .= "            " . $this->brand . "\n";
        
        if ($this->responsive) {
            $html .= "            <div class=\"nav-collapse collapse\">\n";
        }
        
        $escapeLabels = true;
        if (isset($this->options['escapeLabels'])) {
            $escapeLabels = (bool) $this->options['escapeLabels'];
        }
        if ($this->container === false) {
            $html .= "";
        } elseif ($this->container !== null) {
            $html .= "            " . $this->view->plugin('navigation')->menu()->escapeLabels($escapeLabels) . "\n";
        }
        
        if ($this->responsive) {
            // close collapse
            $html .= "            </div>\n";
        }
        
        if ($this->containerRight !== null) {
            $html .= "            <div class=\"pull-right\">\n";
            if ($this->containerRight instanceof Navigation) {
                $html .= "                " . $this->view->plugin('navigation')->setContainer()->menu($this->containerRight)->escapeLabels($escapeLabels) . "\n";
            } else {
                $html .= "                " . $this->containerRight;
            }
            $html .= "            </div>\n";
        }
        
        if ($this->responsive) {
            // close .container
            $html .= "        </div>\n";
        }
        // close navbar
        $html .= "    </div>\n</div>";
        return $html;
    }
    
    /**
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link \Zend\View\Helper\AbstractHtmlElement::htmlAttribs()}.
     *
     * @param  array $attribs  an array where each key-value pair is converted
     *                         to an attribute name and value
     * @return string          an attribute string
     */
    protected function htmlAttribs($attribs)
    {
        // filter out null values and empty string values
        foreach ($attribs as $key => $value) {
            if ($value === null || (is_string($value) && !strlen($value))) {
                unset($attribs[$key]);
            }
        }

        return parent::htmlAttribs($attribs);
    }
    
    /**
     * Magic overload: Proxy to {@link render()}.
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
            return $this->render();
        } catch (\Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
            return '';
        }
    }
}