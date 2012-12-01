<?php
namespace ZFTwiBoo\Form\View\Helper;

use Zend\Form\View\Helper\FormRow;
use Zend\Form\ElementInterface;

class FormControlGroup extends FormRow
{
    protected $inputErrorClass = 'error';
    
    /**
     * Utility form helper that renders a control group compatible with 
     * Twitter Bootstrap
     *
     * @param ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();
    
        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();
        $elementErrors   = $elementErrorsHelper->render($element);
    
        // Does this element have errors ?
        $controlGroupAttribs['class'] = 'control-group';
        if (!empty($elementErrors) && !empty($inputErrorClass)) {
            $controlGroupAttribs['class'] = $controlGroupAttribs['class'] . ' ' . $inputErrorClass;
        }
        
        // Add class for checkbox and radio elements
        $type = $element->getAttribute('type');
        if ($type == 'multi_checkbox' || $type == 'checkbox' || $type == 'radio') {
            $multiClass = $type == 'multi_checkbox' ? 'checkbox' : $type;
            $labelAttributes = $element->getLabelAttributes();
            if (isset($labelAttributes['class'])) {
                $labelAttributes['class'] .= ' ' . $multiClass;
            } else {
                $labelAttributes['class'] = $multiClass;
            }
            $element->setLabelAttributes($labelAttributes);
        }
        // Render the element html
        $elementString = $elementHelper->render($element);
        // Open the control group element
        $markup = '<div ' . $this->createAttributesString($controlGroupAttribs) . '>';
        
        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                        $label, $this->getTranslatorTextDomain()
                );
            }
    
            $label = $escapeHtmlHelper($label);
            $labelAttributes = $element->getLabelAttributes();
            // Set label class container
            $labelAttributes['class'] = 'control-label';
    
            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }
    
            $type = $element->getAttribute('type');
            if ($element->hasAttribute('id')) {
                $labelOpen = $labelHelper($element);
                $labelClose = '';
                $label = '';
            } else {
                $labelOpen  = $labelHelper->openTag($labelAttributes);
                $labelClose = $labelHelper->closeTag();
            }
            // Render label and element
            $markup .= $labelOpen . $label . $labelClose . '<div class="controls">' . $elementString . '</div>';
            // Render errors
            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            // Render the element without main label
            if ($this->renderErrors) {
                $markup .= '<div class="controls">' . $elementString . '</div>' . $elementErrors;
            } else {
                $markup .= '<div class="controls">' . $elementString . '</div>';
            }
        }
        // Open the control group element
        $markup .= '</div>';
        
        return $markup;
    }
}
