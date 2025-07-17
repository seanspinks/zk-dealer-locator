<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\Location\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Reject button
 */
class RejectButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $locationId = $this->getLocationId();
        
        if (!$locationId) {
            return [];
        }
        
        try {
            $location = $this->locationRepository->getById($locationId);
            
            // Only show reject button if location is pending
            if ($location->getStatus() !== LocationInterface::STATUS_PENDING) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        
        return [
            'label' => __('Reject'),
            'class' => 'reject',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'dealerlocator_location_form.dealerlocator_location_form.modal_reject',
                                'actionName' => 'openModal'
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 20
        ];
    }
}