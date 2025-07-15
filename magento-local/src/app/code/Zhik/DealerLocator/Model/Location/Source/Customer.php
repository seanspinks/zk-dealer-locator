<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Location\Source;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Customer source model
 */
class Customer implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect(['firstname', 'lastname', 'email'])
                   ->setOrder('email', 'ASC');
        
        foreach ($collection as $customer) {
            $options[] = [
                'value' => $customer->getId(),
                'label' => sprintf(
                    '%s %s (%s)',
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail()
                )
            ];
        }
        
        return $options;
    }
}