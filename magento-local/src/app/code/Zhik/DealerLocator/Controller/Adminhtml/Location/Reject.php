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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Reject location controller
 */
class Reject extends Action implements HttpPostActionInterface
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
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param JsonFactory $resultJsonFactory
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        JsonFactory $resultJsonFactory,
        Session $authSession
    ) {
        parent::__construct($context);
        $this->locationRepository = $locationRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authSession = $authSession;
    }

    /**
     * Reject location
     *
     * @return Json|Redirect
     */
    public function execute()
    {
        $locationId = (int)$this->getRequest()->getParam('location_id');
        $reason = $this->getRequest()->getParam('reason');
        
        // Check if this is an AJAX request
        $isAjax = $this->getRequest()->isAjax();
        
        if (!$locationId) {
            if ($isAjax) {
                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('Invalid location ID.')
                ]);
            } else {
                $this->messageManager->addErrorMessage(__('Invalid location ID.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }
        
        if (!$reason) {
            if ($isAjax) {
                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('Please provide a reason for rejection.')
                ]);
            } else {
                $this->messageManager->addErrorMessage(__('Please provide a reason for rejection.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['location_id' => $locationId]);
            }
        }

        try {
            $adminUserId = (int)$this->authSession->getUser()->getId();
            $this->locationRepository->reject($locationId, $reason, $adminUserId);
            
            if ($isAjax) {
                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'success' => true,
                    'message' => __('Location has been rejected.')
                ]);
            } else {
                $this->messageManager->addSuccessMessage(__('Location has been rejected.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        } catch (LocalizedException $e) {
            if ($isAjax) {
                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('An error occurred while rejecting the location.')
                ]);
            } else {
                $this->messageManager->addErrorMessage(__('An error occurred while rejecting the location.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }
    }
}