<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for dealer location search results
 * @api
 */
interface LocationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get locations list
     *
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface[]
     */
    public function getItems();

    /**
     * Set locations list
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}