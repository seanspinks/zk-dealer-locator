<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Custom renderer for API key field with validation button
 */
class ApiKey extends Field
{
    /**
     * Render element HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // Add autocomplete="off" and other attributes to prevent autofill
        $element->setData('autocomplete', 'off');
        $element->setData('data-password-autocomplete', 'off');
        $element->addClass('no-autofill');
        
        // Get the standard input field HTML
        $html = parent::_getElementHtml($element);
        
        // Add validation button and help text
        $html .= $this->getValidationHtml($element);
        
        return $html;
    }
    
    /**
     * Get validation button and help HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function getValidationHtml(AbstractElement $element): string
    {
        $fieldId = $element->getHtmlId();
        
        $html = '<div class="api-key-validator" style="margin-top: 10px;">';
        $html .= '<button type="button" ';
        $html .= 'id="validate-api-key-' . $this->escapeHtmlAttr($fieldId) . '" ';
        $html .= 'class="action-default" ';
        $html .= 'data-input-id="' . $this->escapeHtmlAttr($fieldId) . '" ';
        $html .= 'data-message-id="api-key-message-' . $this->escapeHtmlAttr($fieldId) . '">';
        $html .= '<span>' . $this->escapeHtml(__('Validate API Key')) . '</span>';
        $html .= '</button>';
        
        $html .= '<div id="api-key-message-' . $this->escapeHtmlAttr($fieldId) . '" ';
        $html .= 'class="message" ';
        $html .= 'style="display:none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>';
        $html .= '</div>';
        
        // Add help text
        $html .= '<div class="api-key-help" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 4px;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600;">';
        $html .= $this->escapeHtml(__('Required Google APIs:'));
        $html .= '</h4>';
        $html .= '<ul style="margin: 0; padding-left: 20px;">';
        $html .= '<li>' . $this->escapeHtml(__('Maps JavaScript API')) . '</li>';
        $html .= '<li>' . $this->escapeHtml(__('Places API')) . '</li>';
        $html .= '<li>' . $this->escapeHtml(__('Geocoding API')) . '</li>';
        $html .= '</ul>';
        $html .= '</div>';
        
        // Add JavaScript
        $html .= $this->getJavaScriptHtml($fieldId);
        
        // Add styles
        $html .= $this->getStylesHtml();
        
        return $html;
    }
    
    /**
     * Get JavaScript for validation
     *
     * @param string $fieldId
     * @return string
     */
    protected function getJavaScriptHtml(string $fieldId): string
    {
        return <<<SCRIPT
<script type="text/javascript">
require(['jquery', 'Zhik_DealerLocator/js/config/api-key-validator'], function($, validator) {
    'use strict';
    
    $(document).ready(function() {
        var \$input = $('#{$this->escapeJs($fieldId)}');
        var \$button = $('#validate-api-key-{$this->escapeJs($fieldId)}');
        
        // Prevent autofill
        \$input.attr('autocomplete', 'off');
        \$input.attr('data-form-type', 'other');
        \$input.on('focus', function() {
            $(this).attr('autocomplete', 'off');
        });
        
        // Validate button click
        \$button.on('click', function() {
            validator.validate(null, this);
        });
        
        // Validate on enter key
        \$input.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                \$button.trigger('click');
            }
        });
    });
});
</script>
SCRIPT;
    }
    
    /**
     * Get styles HTML
     *
     * @return string
     */
    protected function getStylesHtml(): string
    {
        return <<<STYLES
<style>
.message-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.message-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}
</style>
STYLES;
    }
}