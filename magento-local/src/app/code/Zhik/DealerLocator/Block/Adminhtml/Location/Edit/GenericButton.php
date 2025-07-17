<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\Location\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Generic button
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->locationRepository = $locationRepository;
        $this->logger = $logger;
    }

    /**
     * Return Location ID
     *
     * @return int|null
     */
    public function getLocationId()
    {
        $locationId = $this->context->getRequest()->getParam('location_id');
        return $locationId ? (int)$locationId : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}