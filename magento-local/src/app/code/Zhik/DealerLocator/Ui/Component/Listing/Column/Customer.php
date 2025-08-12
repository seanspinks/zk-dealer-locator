<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Ui\Component\Listing\Column;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Customer column for locations grid
 */
class Customer extends Column
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerRepository = $customerRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['customer_id'])) {
                    try {
                        $customer = $this->customerRepository->getById($item['customer_id']);
                        $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                        $customerUrl = $this->urlBuilder->getUrl(
                            'customer/index/edit',
                            ['id' => $item['customer_id']]
                        );
                        $item[$this->getData('name')] = sprintf(
                            '<a href="%s">%s (%s)</a>',
                            $customerUrl,
                            $name,
                            $customer->getEmail()
                        );
                    } catch (\Exception $e) {
                        $item[$this->getData('name')] = 'Customer #' . $item['customer_id'] . ' (Deleted)';
                    }
                }
            }
        }
        return $dataSource;
    }
}