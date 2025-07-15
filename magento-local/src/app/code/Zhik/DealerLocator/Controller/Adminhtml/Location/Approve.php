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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Approve location controller
 */
class Approve extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::manage';

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
     * Approve location
     *
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        
        $locationId = (int)$this->getRequest()->getParam('location_id');
        if (!$locationId) {
            return $resultJson->setData([
                'error' => true,
                'message' => __('Invalid location ID.')
            ]);
        }

        try {
            $adminUserId = (int)$this->authSession->getUser()->getId();
            $this->locationRepository->approve($locationId, $adminUserId);
            
            return $resultJson->setData([
                'success' => true,
                'message' => __('Location has been approved.')
            ]);
        } catch (LocalizedException $e) {
            return $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'error' => true,
                'message' => __('An error occurred while approving the location.')
            ]);
        }
    }
}