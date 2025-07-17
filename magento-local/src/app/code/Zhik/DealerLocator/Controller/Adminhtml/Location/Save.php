<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Save location controller
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_save';

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var LocationInterfaceFactory
     */
    private $locationFactory;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param LocationInterfaceFactory $locationFactory
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        LocationInterfaceFactory $locationFactory,
        Session $authSession
    ) {
        parent::__construct($context);
        $this->locationRepository = $locationRepository;
        $this->locationFactory = $locationFactory;
        $this->authSession = $authSession;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $data = $this->getRequest()->getPostValue();
        
        // Debug logging
        $logger = $this->_objectManager->get(\Psr\Log\LoggerInterface::class);
        $logger->info('DealerLocator Save: Request method: ' . $this->getRequest()->getMethod());
        $logger->info('DealerLocator Save: Post data: ' . json_encode($data));
        $logger->info('DealerLocator Save: Form key valid: ' . ($this->_formKeyValidator->validate($this->getRequest()) ? 'YES' : 'NO'));
        
        if (!$data) {
            $logger->info('DealerLocator Save: No data received, redirecting to index');
            return $resultRedirect->setPath('*/*/');
        }
        
        // Validate form key
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $logger->error('DealerLocator Save: Invalid form key!');
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $locationId = !empty($data['location_id']) ? (int)$data['location_id'] : null;
            
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->info('DealerLocator Save: Location ID: ' . var_export($locationId, true));
            
            if ($locationId) {
                $location = $this->locationRepository->getById($locationId);
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->info('DealerLocator Save: Loaded existing location');
            } else {
                $location = $this->locationFactory->create();
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->info('DealerLocator Save: Created new location');
            }

            // Set location data
            $location->setName($data['name']);
            $location->setAddress($data['address']);
            $location->setCity($data['city']);
            $location->setState($data['state'] ?? '');
            $location->setPostalCode($data['postal_code']);
            $location->setCountry($data['country']);
            $location->setPhone($data['phone']);
            $location->setEmail($data['email']);
            $location->setWebsite($data['website'] ?? '');
            $location->setHours($data['hours'] ?? '');
            $location->setDescription($data['description'] ?? '');
            
            // Set coordinates if provided
            if (isset($data['latitude']) && isset($data['longitude'])) {
                $location->setLatitude((float)$data['latitude']);
                $location->setLongitude((float)$data['longitude']);
            }
            
            // Set customer ID if provided
            if (isset($data['customer_id'])) {
                $location->setCustomerId((int)$data['customer_id']);
            }
            
            // For new locations, we need to save first before changing status
            $isNew = !$locationId;
            
            // Save tags if provided
            if (isset($data['tag_ids'])) {
                $location->setData('tag_ids', $data['tag_ids']);
            }
            
            // Handle status changes
            if (isset($data['status'])) {
                $currentStatus = $location->getStatus();
                $newStatus = $data['status'];
                $adminUserId = (int)$this->authSession->getUser()->getId();
                
                // For new locations being created with approved/rejected status
                if ($isNew && ($newStatus === LocationInterface::STATUS_APPROVED || $newStatus === LocationInterface::STATUS_REJECTED)) {
                    // Save the location first with pending status
                    $location->setStatus(LocationInterface::STATUS_PENDING);
                    $savedLocation = $this->locationRepository->save($location);
                    $locationId = (int)$savedLocation->getLocationId();
                    
                    // Now approve/reject using the repository methods
                    if ($newStatus === LocationInterface::STATUS_APPROVED) {
                        $this->locationRepository->approve($locationId, $adminUserId);
                    } elseif ($newStatus === LocationInterface::STATUS_REJECTED) {
                        $reason = $data['rejection_reason'] ?? __('Rejected by admin');
                        $this->locationRepository->reject($locationId, (string)$reason, $adminUserId);
                    }
                } elseif (!$isNew && $currentStatus !== $newStatus && 
                    ($newStatus === LocationInterface::STATUS_APPROVED || $newStatus === LocationInterface::STATUS_REJECTED)) {
                    // For existing locations changing to approved/rejected
                    // Save first to persist any other changes
                    $savedLocation = $this->locationRepository->save($location);
                    $locationId = (int)$savedLocation->getLocationId();
                    
                    // Then change status
                    if ($newStatus === LocationInterface::STATUS_APPROVED) {
                        $this->locationRepository->approve($locationId, $adminUserId);
                    } elseif ($newStatus === LocationInterface::STATUS_REJECTED) {
                        $reason = $data['rejection_reason'] ?? __('Rejected by admin');
                        $this->locationRepository->reject($locationId, (string)$reason, $adminUserId);
                    }
                } else {
                    // For all other cases (new location with pending status or status not changing)
                    $location->setStatus($newStatus);
                    $savedLocation = $this->locationRepository->save($location);
                    $locationId = (int)$savedLocation->getLocationId();
                }
            } else {
                // No status change, just save
                $savedLocation = $this->locationRepository->save($location);
                $locationId = (int)$savedLocation->getLocationId();
            }

            $this->messageManager->addSuccessMessage(__('You saved the location.'));
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
            
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['location_id' => $locationId]);
            }
            
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the location.'));
        }

        $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
        
        if ($locationId) {
            return $resultRedirect->setPath('*/*/edit', ['location_id' => $locationId]);
        }
        
        return $resultRedirect->setPath('*/*/new');
    }
}