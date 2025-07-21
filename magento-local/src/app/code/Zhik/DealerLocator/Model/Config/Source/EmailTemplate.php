<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Config\Source;

use Magento\Config\Model\Config\Source\Email\Template as EmailTemplateSource;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Registry;

/**
 * Email template source model for dealer locator
 */
class EmailTemplate extends EmailTemplateSource
{
    /**
     * @var array
     */
    private $templates = [
        'dealerlocator_email_admin_new_submission' => 'Dealer Location - New Submission Notification',
        'dealerlocator_email_customer_submission_confirmation' => 'Dealer Location - Submission Confirmation',
        'dealerlocator_email_customer_location_approved' => 'Dealer Location - Location Approved',
        'dealerlocator_email_customer_location_rejected' => 'Dealer Location - Location Rejected'
    ];

    /**
     * @param Registry $coreRegistry
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($coreRegistry, $templatesFactory, $emailConfig, $data);
    }

    /**
     * Generate options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        
        // Add default option
        $options[] = [
            'value' => '',
            'label' => __('-- Please Select --')
        ];
        
        // Add module specific templates
        foreach ($this->templates as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => __($label)
            ];
        }
        
        // Add custom templates from database
        $collection = $this->_templatesFactory->create();
        $collection->addFieldToFilter('orig_template_code', ['in' => array_keys($this->templates)]);
        
        foreach ($collection as $template) {
            $options[] = [
                'value' => $template->getId(),
                'label' => $this->_getTemplateLabelFromCollection($template)
            ];
        }
        
        // Add all other system templates
        $collection = $this->_templatesFactory->create();
        $collection->load();
        
        foreach ($collection as $template) {
            // Skip if already added
            if (in_array($template->getOrigTemplateCode(), array_keys($this->templates))) {
                continue;
            }
            
            $options[] = [
                'value' => $template->getId(),
                'label' => $this->_getTemplateLabelFromCollection($template)
            ];
        }
        
        return $options;
    }
    
    /**
     * Get template label from collection item
     *
     * @param \Magento\Email\Model\Template $template
     * @return string
     */
    private function _getTemplateLabelFromCollection($template)
    {
        return $template->getTemplateCode() . 
               ($template->getOrigTemplateCode() ? ' (' . $template->getOrigTemplateCode() . ')' : '');
    }
}