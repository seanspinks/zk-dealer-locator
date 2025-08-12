<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\Api\SearchResults;
use Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface;

/**
 * Location search results implementation
 */
class LocationSearchResults extends SearchResults implements LocationSearchResultsInterface
{
}