<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Customer\Location;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Delete location controller
 */
class Delete extends AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->locationRepository = $locationRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Delete customer location
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $locationId = (int)$this->getRequest()->getParam('id');

        if (!$locationId) {
            $this->messageManager->addErrorMessage(__('Invalid location ID.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $location = $this->locationRepository->getById($locationId);
            
            // Verify ownership
            if ($location->getCustomerId() != $this->customerSession->getCustomerId()) {
                throw new LocalizedException(__('You are not authorized to delete this location.'));
            }
            
            // Check if already pending deletion
            if ($location->getStatus() === LocationInterface::STATUS_PENDING_DELETION) {
                throw new LocalizedException(__('This location is already pending deletion.'));
            }
            
            // For approved locations, mark as pending deletion instead of deleting
            if ($location->getStatus() === LocationInterface::STATUS_APPROVED) {
                $location->setStatus(LocationInterface::STATUS_PENDING_DELETION);
                $this->locationRepository->save($location);
                $this->messageManager->addSuccessMessage(__('Location has been marked for deletion. An administrator will review this request.'));
            } else {
                // For non-approved locations (pending, rejected), delete immediately
                $this->locationRepository->delete($location);
                $this->messageManager->addSuccessMessage(__('Location has been deleted.'));
            }
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}