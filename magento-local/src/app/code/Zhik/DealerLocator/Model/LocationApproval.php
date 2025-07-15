<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Backend\Model\Auth\Session;
use Zhik\DealerLocator\Api\LocationApprovalInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Location approval implementation
 */
class LocationApproval implements LocationApprovalInterface
{
    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @param LocationRepositoryInterface $locationRepository
     * @param Session $authSession
     */
    public function __construct(
        LocationRepositoryInterface $locationRepository,
        Session $authSession
    ) {
        $this->locationRepository = $locationRepository;
        $this->authSession = $authSession;
    }

    /**
     * @inheritdoc
     */
    public function approve(int $locationId): \Zhik\DealerLocator\Api\Data\LocationInterface
    {
        $adminUserId = $this->getAdminUserId();
        return $this->locationRepository->approve($locationId, $adminUserId);
    }

    /**
     * @inheritdoc
     */
    public function reject(int $locationId, string $reason): \Zhik\DealerLocator\Api\Data\LocationInterface
    {
        $adminUserId = $this->getAdminUserId();
        return $this->locationRepository->reject($locationId, $reason, $adminUserId);
    }

    /**
     * Get current admin user ID
     *
     * @return int
     */
    private function getAdminUserId(): int
    {
        $user = $this->authSession->getUser();
        return $user ? (int)$user->getId() : 0;
    }
}