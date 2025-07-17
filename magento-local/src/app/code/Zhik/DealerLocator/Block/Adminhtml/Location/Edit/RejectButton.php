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
            $location = $this->locationRepository->getById((int)$locationId);
            
            // Only show reject button if location is pending
            if ($location->getStatus() !== LocationInterface::STATUS_PENDING) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        
        return [
            'label' => __('Reject'),
            'class' => 'reject action-secondary',
            'id' => 'reject-location-button',
            'data_attribute' => [
                'mage-init' => [
                    'Zhik_DealerLocator/js/location-reject-handler' => []
                ]
            ],
            'sort_order' => 20
        ];
    }
}