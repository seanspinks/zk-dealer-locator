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
use Magento\Framework\View\Result\PageFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Edit location controller
 */
class Edit extends AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LocationRepositoryInterface $locationRepository
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LocationRepositoryInterface $locationRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->locationRepository = $locationRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Edit location form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $locationId = (int)$this->getRequest()->getParam('id');
        
        if (!$locationId) {
            $this->messageManager->addErrorMessage(__('Invalid location ID.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $location = $this->locationRepository->getById($locationId);
            
            // Verify ownership
            if ($location->getCustomerId() != $this->customerSession->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('You are not authorized to edit this location.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
            
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Location'));

            return $resultPage;
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Location not found.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }
}