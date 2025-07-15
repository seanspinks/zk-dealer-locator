<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Location;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;

/**
 * Location data provider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Zhik\DealerLocator\Model\ResourceModel\Location\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getLocationId()] = $model->getData();
            
            // Load tag IDs
            $tagIds = $model->getResource()->getLocationTags($model->getLocationId());
            $this->loadedData[$model->getLocationId()]['tag_ids'] = $tagIds;
        }
        
        $data = $this->dataPersistor->get('dealerlocator_location');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getLocationId()] = $model->getData();
            $this->dataPersistor->clear('dealerlocator_location');
        }
        
        return $this->loadedData;
    }
}