<?php
namespace ZFTwiBoo\View\Helper;

use Zend\View\Helper\AbstractHelper;

abstract class AbstractStyleHelper extends AbstractHelper
{
    /**
     * @var string   HTML template
     */
    public $template = "<span class=\"label label-%s\">%s</span>";
    
    /**
     * Default style types available, should be overrided
     * for advanced componenents
     * 
     * @var array
     */
    public $styleTypes = array(
        'default',
        'success',
        'warning',
        'important',
        'info',
        'inverse'
    );
}