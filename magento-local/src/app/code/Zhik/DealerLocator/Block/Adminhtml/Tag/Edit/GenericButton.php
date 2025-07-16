<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\Tag\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

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
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @param Context $context
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        Context $context,
        TagRepositoryInterface $tagRepository
    ) {
        $this->context = $context;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Return Tag ID
     *
     * @return int|null
     */
    public function getTagId()
    {
        $tagId = $this->context->getRequest()->getParam('tag_id');
        if (!$tagId) {
            return null;
        }
        
        try {
            return $this->tagRepository->getById((int)$tagId)->getTagId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
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