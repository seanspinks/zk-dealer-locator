<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zhik\DealerLocator\Api\Data\TagInterface;
use Zhik\DealerLocator\Api\Data\TagSearchResultsInterface;

/**
 * Dealer tag CRUD interface
 * @api
 */
interface TagRepositoryInterface
{
    /**
     * Save tag
     *
     * @param TagInterface $tag
     * @return TagInterface
     * @throws LocalizedException
     */
    public function save(TagInterface $tag): TagInterface;

    /**
     * Get tag by ID
     *
     * @param int $tagId
     * @return TagInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $tagId): TagInterface;

    /**
     * Get list of tags
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TagSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TagSearchResultsInterface;

    /**
     * Delete tag
     *
     * @param TagInterface $tag
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(TagInterface $tag): bool;

    /**
     * Delete tag by ID
     *
     * @param int $tagId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $tagId): bool;

    /**
     * Get all active tags
     *
     * @return TagInterface[]
     * @throws LocalizedException
     */
    public function getActiveTags(): array;
}