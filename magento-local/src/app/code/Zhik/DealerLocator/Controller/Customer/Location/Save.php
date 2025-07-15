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
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Save location controller
 */
class Save extends AbstractAccount implements HttpPostActionInterface
{
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
    private $customerSession;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param LocationInterfaceFactory $locationFactory
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        LocationInterfaceFactory $locationFactory,
        Session $customerSession,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->locationRepository = $locationRepository;
        $this->locationFactory = $locationFactory;
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Save customer location
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('*/*/add');
        }

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $locationId = isset($data['location_id']) ? (int)$data['location_id'] : null;
            
            if ($locationId) {
                $location = $this->locationRepository->getById($locationId);
                // Verify ownership
                if ($location->getCustomerId() != $this->customerSession->getCustomerId()) {
                    throw new LocalizedException(__('You are not authorized to edit this location.'));
                }
            } else {
                $location = $this->locationFactory->create();
                $location->setCustomerId($this->customerSession->getCustomerId());
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
            
            // Set IP address
            $location->setIpAddress($this->getRequest()->getClientIp());
            
            // Save tags if provided
            if (isset($data['tag_ids'])) {
                $location->setData('tag_ids', $data['tag_ids']);
            }

            $this->locationRepository->save($location);
            
            $this->messageManager->addSuccessMessage(__('Location has been saved successfully.'));
            return $resultRedirect->setPath('*/*/');
            
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the location.'));
        }

        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/add', ['id' => $locationId]);
    }
}