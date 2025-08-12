<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Custom field renderer for Google Maps style JSON
 */
class MapStyle extends Field
{
    /**
     * Get element HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setRows(10);
        $element->setCols(70);
        $element->setClass('monospace-font');
        $element->setStyle('font-family: monospace; font-size: 12px;');
        
        $html = parent::_getElementHtml($element);
        
        // Add help text
        $html .= $this->getHelpHtml();
        
        return $html;
    }
    
    /**
     * Get help HTML
     *
     * @return string
     */
    protected function getHelpHtml(): string
    {
        $html = '<div style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 4px;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600;">';
        $html .= $this->escapeHtml(__('Map Style Format'));
        $html .= '</h4>';
        $html .= '<p style="margin: 0 0 10px 0; color: #666;">';
        $html .= $this->escapeHtml(__('Enter a valid JSON array for Google Maps styling. You can generate custom styles at:'));
        $html .= '</p>';
        $html .= '<ul style="margin: 0; padding-left: 20px;">';
        $html .= '<li><a href="https://mapstyle.withgoogle.com/" target="_blank">Google Maps Styling Wizard</a></li>';
        $html .= '<li><a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a></li>';
        $html .= '</ul>';
        $html .= '<p style="margin: 10px 0 0 0; color: #666; font-size: 12px;">';
        $html .= $this->escapeHtml(__('Leave empty to use default Google Maps styling.'));
        $html .= '</p>';
        $html .= '</div>';
        
        return $html;
    }
}