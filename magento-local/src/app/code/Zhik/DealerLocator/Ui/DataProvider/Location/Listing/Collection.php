<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Ui\DataProvider\Location\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package Zhik\DealerLocator\Ui\DataProvider\Location\Listing
 */
class Collection extends SearchResult
{
    /**
     * Init collection select
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        // Only show latest versions in admin grid
        $this->addFieldToFilter('is_latest', 1);
    }
}