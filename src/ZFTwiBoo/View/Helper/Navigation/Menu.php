<?php
namespace ZFTwiBoo\View\Helper\Navigation;

use RecursiveIteratorIterator;
use Zend\View\Helper\Navigation\Menu as ZendMenu;
use Zend\Navigation\Page\AbstractPage;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Navigation;

class Menu extends ZendMenu
{
    /**
     * CSS class to use for the ul element
     *
     * @var string
     */
    protected $ulClass = 'nav';
    
    /**
     * Render the menu as a dropdown menu
     * 
     * @var bool
     */
    protected $dropdown;
    
    /**
     * Keep functinoality of the URLs on dropdown menus
     * 
     * @var bool
     */
    protected $keepUrls;
    
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  AbstractContainer $container [optional] container to operate on
     * @return Menu      fluent interface, returns self
     */
    public function __invoke($container = null, $dropdown = true, $keepUrls = true)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }
        
        $this->setDropdown($dropdown);
        $this->setKeepUrls($keepUrls);

        return $this;
    }
    
    public function setDropdown($flag)
    {
        $this->dropdown = (bool) $flag;
        return $this;
    }
    
    public function setKeepUrls($flag)
    {
        $this->keepUrls = (bool) $flag;
        return $this;
    }
    
    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty
     *
     * Overrides {@link AbstractHelper::htmlify()}.
     *
     * @param  AbstractPage $page   page to generate HTML for
     * @param bool $escapeLabel     Whether or not to escape the label
     * @return string               HTML string for the given page
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true)
    {
        // get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();

        // translate label and title?
        if (null !== ($translator = $this->getTranslator())) {
            $textDomain = $this->getTranslatorTextDomain();
            if (is_string($label) && !empty($label)) {
                $label = $translator->translate($label, $textDomain);
            }
            if (is_string($title) && !empty($title)) {
                $title = $translator->translate($title, $textDomain);
            }
        }

        $hasVisible = $this->hasVisiblePages($page);
        
        // get attribs for element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $title,
            'class'  => $page->getClass()
        );

        // does page have a href?
        $href = $page->getHref();
        if ($href) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
            if ($this->keepUrls && $page->hasChildren() && $hasVisible) {
                $attribs['data-target'] = '#';
            }
        } else {
            if ($attribs['class'] == 'divider' || $attribs['class'] == 'divider-vertical') {
                return '';
                
            }
            $element = 'span';
        }
        
        // does de page have data attributes?
        $customProperties = $page->getCustomProperties();
        if (isset($customProperties['data']) && is_array($customProperties['data'])) {
            foreach ($customProperties['data'] as $key => $val) {
                $attribs['data-' . $key] = $val;
            }
        }
        
        $maxDepth = $this->getMaxDepth();
        if ($maxDepth === 0) {
            $noChild = true;
        } else {
            $noChild = false;
        }
        if ($this->dropdown && $page->hasChildren() && $hasVisible && !$noChild) {
            $attribs['class']       = @$attribs['class'] . ' dropdown-toggle';
            if ($this->keepUrls && $page->getParent() instanceof Navigation) {
                $attribs['data-toggle'] = 'dropdown';
            }
        }

        $html = '<' . $element . $this->htmlAttribs($attribs) . '>';
        if ($escapeLabel === true) {
            $escaper = $this->view->plugin('escapeHtml');
            $html .= $escaper($label);
        } else {
            $html .= $label;
        }
        if ($page->hasChildren() && $hasVisible && $page->getParent() instanceof Navigation && !$noChild) {
            $html .= ' <b class="caret"></b>';
        }
        $html .= '</' . $element . '>';

        return $html;
    }
    
    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  AbstractContainer         $container    container to render
     * @param  string                    $ulClass      CSS class for first UL
     * @param  string                    $indent       initial indentation
     * @param  int|null                  $minDepth     minimum depth
     * @param  int|null                  $maxDepth     maximum depth
     * @param  bool                      $onlyActive   render only active branch?
     * @param  bool                      $escapeLabels Whether or not to escape the labels
     * @return string
     */
    protected function renderNormalMenu(AbstractContainer $container,
                                   $ulClass,
                                   $indent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $escapeLabels
    ) {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
                            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
                continue;
            } elseif ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } elseif ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && $depth ==  0) {
                    $ulClass = ' class="' . $ulClass . '"';
                } else {
                    if ($depth > 0) {
                        $ulClass = $this->dropdown ? ' class="dropdown-menu"' : '';
                    } else {
                        $ulClass = '';
                    }
                }
                $html .= $myIndent . '<ul' . $ulClass . '>' . self::EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . self::EOL;
                    $html .= $ind . '</ul>' . self::EOL;
                }
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            }
            
            
            // render li tag and page
            $liClass = $isActive ? array('active') : array('');
            if ($this->dropdown) {
                if ($page->hasChildren() && $this->hasVisiblePages($page)) {
                    if ($depth >= 1) {
                        $liClass[] = 'dropdown-submenu';
                    } else {
                        $liClass[] = 'dropdown';
                    }
                } elseif ($page->hasChildren() && $depth >= 1 && $this->hasVisiblePages($page)) {
                    $liClass[] = 'dropdown-submenu';
                }
            }
            
            if ($page->getClass() == 'divider' || $page->getClass() == 'divider-vertical') {
                $liClass[] = $page->getClass();
            }

            $liClass = join(' ', $liClass);
            $html .= $myIndent . '    <li class="' . $liClass . '">' . self::EOL
                   . $myIndent . '        ' . $this->htmlify($page, $escapeLabels) . self::EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth+1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat('        ', $i-1);
                $html .= $myIndent . '    </li>' . self::EOL
                       . $myIndent . '</ul>' . self::EOL;
            }
            $html = rtrim($html, self::EOL);
        }

        return $html;
    }
    
    /**
     *
     * @param  AbstractPage $page   page to generate HTML for
     * @return bool
     */
    public function hasVisiblePages(AbstractPage $page)
    {
        $pages = $page->getPages();
        $hasVisible = false;
        foreach ($pages as $p) {
            if ($p->isVisible()) {
                $hasVisible = true;
                break;
            }
        }
    
        return $hasVisible;
    }
}