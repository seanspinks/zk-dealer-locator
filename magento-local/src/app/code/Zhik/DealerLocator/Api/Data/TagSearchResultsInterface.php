<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for dealer tag search results
 * @api
 */
interface TagSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get tags list
     *
     * @return \Zhik\DealerLocator\Api\Data\TagInterface[]
     */
    public function getItems();

    /**
     * Set tags list
     *
     * @param \Zhik\DealerLocator\Api\Data\TagInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}