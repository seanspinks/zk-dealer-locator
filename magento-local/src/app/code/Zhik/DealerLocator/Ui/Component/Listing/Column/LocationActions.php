<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class LocationActions
 */
class LocationActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'dealerlocator/location/edit';
    const URL_PATH_DELETE = 'dealerlocator/location/delete';
    const URL_PATH_APPROVE = 'dealerlocator/location/approve';
    const URL_PATH_REJECT = 'dealerlocator/location/reject';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['location_id'])) {
                    $name = $this->getData('name');
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_EDIT,
                            ['location_id' => $item['location_id']]
                        ),
                        'label' => __('Edit')
                    ];
                    
                    if ($item['status'] == 'pending') {
                        $item[$name]['approve'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_APPROVE,
                                ['location_id' => $item['location_id']]
                            ),
                            'label' => __('Approve'),
                            'confirm' => [
                                'title' => __('Approve Location'),
                                'message' => __('Are you sure you want to approve this location?')
                            ],
                            'post' => true
                        ];
                        $item[$name]['reject'] = [
                            'href' => '#',
                            'label' => __('Reject'),
                            'onclick' => sprintf(
                                "require(['Zhik_DealerLocator/js/grid/actions'], function(actions) { actions.rejectLocation('%s'); }); return false;",
                                $this->urlBuilder->getUrl(static::URL_PATH_REJECT, ['location_id' => $item['location_id']])
                            )
                        ];
                    }
                    
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_DELETE,
                            ['location_id' => $item['location_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Location'),
                            'message' => __('Are you sure you want to delete this location?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}