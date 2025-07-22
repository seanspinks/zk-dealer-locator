<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Customer\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\Source\Status;

/**
 * Customer account locations block
 */
class Locations extends Template
{
    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Status
     */
    protected $statusSource;

    /**
     * @var array|null
     */
    protected $locations = null;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param Session $customerSession
     * @param Status $statusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        Session $customerSession,
        Status $statusSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->locationRepository = $locationRepository;
        $this->customerSession = $customerSession;
        $this->statusSource = $statusSource;
    }

    /**
     * Get customer locations
     *
     * @return array
     */
    public function getLocations()
    {
        if ($this->locations === null) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $this->locations = $this->locationRepository->getByCustomerId((int)$customerId);
            } else {
                $this->locations = [];
            }
        }
        return $this->locations;
    }

    /**
     * Get add new location URL
     *
     * @return string
     */
    public function getAddUrl()
    {
        return $this->getUrl('dealerlocator/customer_location/add');
    }

    /**
     * Get edit location URL
     *
     * @param int $locationId
     * @return string
     */
    public function getEditUrl($locationId)
    {
        return $this->getUrl('dealerlocator/customer_location/edit', ['id' => $locationId]);
    }

    /**
     * Get delete location URL
     *
     * @param int $locationId
     * @return string
     */
    public function getDeleteUrl($locationId)
    {
        return $this->getUrl('dealerlocator/customer_location/delete', ['id' => $locationId]);
    }

    /**
     * Get location status label
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $statuses = $this->statusSource->toArray();
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Get status CSS class
     *
     * @param string $status
     * @return string
     */
    public function getStatusClass($status)
    {
        switch ($status) {
            case 'approved':
                return 'status-approved';
            case 'rejected':
                return 'status-rejected';
            case 'pending_deletion':
                return 'status-pending-deletion';
            case 'pending':
            default:
                return 'status-pending';
        }
    }

    /**
     * Check if location can be edited
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return bool
     */
    public function canEdit($location)
    {
        return true; // All locations can be edited, versioning is handled in repository
    }

    /**
     * Check if location can be deleted
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return bool
     */
    public function canDelete($location)
    {
        // Don't allow deletion if already pending deletion
        if ($location->getStatus() === 'pending_deletion') {
            return false;
        }
        // Allow customers to delete their own locations
        return true;
    }

    /**
     * Get locations grouped by status
     *
     * @return array
     */
    public function getLocationsByStatus()
    {
        $grouped = [
            'all' => [],
            'approved' => [],
            'pending' => [],
            'rejected' => [],
            'pending_deletion' => []
        ];

        foreach ($this->getLocations() as $location) {
            $grouped['all'][] = $location;
            $status = $location->getStatus();
            if (isset($grouped[$status])) {
                $grouped[$status][] = $location;
            }
        }

        return $grouped;
    }

    /**
     * Get delete post JSON
     *
     * @param int $locationId
     * @return string
     */
    public function getDeletePostJson($locationId)
    {
        $url = $this->getDeleteUrl($locationId);
        return json_encode([
            'action' => $url,
            'data' => []
        ]);
    }
}