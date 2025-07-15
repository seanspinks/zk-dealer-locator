<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;
use Zhik\DealerLocator\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * Location export model
 */
class Location extends AbstractEntity
{
    /**
     * Permanent entity columns
     */
    const COLUMN_LOCATION_ID = 'location_id';
    const COLUMN_CUSTOMER_ID = 'customer_id';
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';
    const COLUMN_NAME = 'name';
    const COLUMN_ADDRESS = 'address';
    const COLUMN_CITY = 'city';
    const COLUMN_STATE = 'state';
    const COLUMN_POSTAL_CODE = 'postal_code';
    const COLUMN_COUNTRY = 'country';
    const COLUMN_PHONE = 'phone';
    const COLUMN_EMAIL = 'email';
    const COLUMN_WEBSITE = 'website';
    const COLUMN_HOURS = 'hours';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_LATITUDE = 'latitude';
    const COLUMN_LONGITUDE = 'longitude';
    const COLUMN_STATUS = 'status';
    const COLUMN_IS_ACTIVE = 'is_active';
    const COLUMN_TAGS = 'tags';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';
    const COLUMN_APPROVED_AT = 'approved_at';
    const COLUMN_APPROVED_BY = 'approved_by';
    const COLUMN_REJECTION_REASON = 'rejection_reason';

    /**
     * @var CollectionFactory
     */
    protected $_locationCollectionFactory;

    /**
     * @var TagCollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * @var array
     */
    protected $_tagNames = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param CollectionFactory $locationCollectionFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $locationCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->_locationCollectionFactory = $locationCollectionFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->_initTagNames();
    }

    /**
     * Initialize tag names
     *
     * @return void
     */
    protected function _initTagNames()
    {
        $collection = $this->_tagCollectionFactory->create();
        foreach ($collection as $tag) {
            $this->_tagNames[$tag->getTagId()] = $tag->getName();
        }
    }

    /**
     * Get entity type code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'dealerlocator_location';
    }

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        return [
            self::COLUMN_LOCATION_ID,
            self::COLUMN_CUSTOMER_ID,
            self::COLUMN_CUSTOMER_EMAIL,
            self::COLUMN_NAME,
            self::COLUMN_ADDRESS,
            self::COLUMN_CITY,
            self::COLUMN_STATE,
            self::COLUMN_POSTAL_CODE,
            self::COLUMN_COUNTRY,
            self::COLUMN_PHONE,
            self::COLUMN_EMAIL,
            self::COLUMN_WEBSITE,
            self::COLUMN_HOURS,
            self::COLUMN_DESCRIPTION,
            self::COLUMN_LATITUDE,
            self::COLUMN_LONGITUDE,
            self::COLUMN_STATUS,
            self::COLUMN_IS_ACTIVE,
            self::COLUMN_TAGS,
            self::COLUMN_CREATED_AT,
            self::COLUMN_UPDATED_AT,
            self::COLUMN_APPROVED_AT,
            self::COLUMN_APPROVED_BY,
            self::COLUMN_REJECTION_REASON
        ];
    }

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        $collection = $this->_locationCollectionFactory->create();
        
        // Join customer email
        $collection->getSelect()->joinLeft(
            ['customer' => $collection->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['customer_email' => 'email']
        );
        
        // Join admin user name
        $collection->getSelect()->joinLeft(
            ['admin_user' => $collection->getTable('admin_user')],
            'main_table.approved_by = admin_user.user_id',
            ['approved_by_username' => 'username']
        );
        
        return $collection;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        $writer = $this->getWriter();
        $page = 0;
        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollection();
            $entityCollection->setOrder('location_id', 'asc');
            $entityCollection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->count() === 0) {
                break;
            }
            $exportData = $this->getExportData();
            if ($page === 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            foreach ($exportData as $dataRow) {
                $writer->writeRow($this->_customFieldsMapping($dataRow));
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
        return $writer->getContents();
    }

    /**
     * Get export data
     *
     * @return array
     */
    protected function getExportData()
    {
        $exportData = [];
        $entityCollection = $this->_getEntityCollection();
        
        foreach ($entityCollection as $item) {
            // Load tags for this location
            $tagIds = $item->getResource()->getLocationTags($item->getLocationId());
            $tagNames = [];
            foreach ($tagIds as $tagId) {
                if (isset($this->_tagNames[$tagId])) {
                    $tagNames[] = $this->_tagNames[$tagId];
                }
            }
            
            $exportData[] = [
                self::COLUMN_LOCATION_ID => $item->getLocationId(),
                self::COLUMN_CUSTOMER_ID => $item->getCustomerId(),
                self::COLUMN_CUSTOMER_EMAIL => $item->getCustomerEmail(),
                self::COLUMN_NAME => $item->getName(),
                self::COLUMN_ADDRESS => $item->getAddress(),
                self::COLUMN_CITY => $item->getCity(),
                self::COLUMN_STATE => $item->getState(),
                self::COLUMN_POSTAL_CODE => $item->getPostalCode(),
                self::COLUMN_COUNTRY => $item->getCountry(),
                self::COLUMN_PHONE => $item->getPhone(),
                self::COLUMN_EMAIL => $item->getEmail(),
                self::COLUMN_WEBSITE => $item->getWebsite(),
                self::COLUMN_HOURS => $item->getHours(),
                self::COLUMN_DESCRIPTION => $item->getDescription(),
                self::COLUMN_LATITUDE => $item->getLatitude(),
                self::COLUMN_LONGITUDE => $item->getLongitude(),
                self::COLUMN_STATUS => $item->getStatus(),
                self::COLUMN_IS_ACTIVE => $item->getIsActive(),
                self::COLUMN_TAGS => implode(',', $tagNames),
                self::COLUMN_CREATED_AT => $item->getCreatedAt(),
                self::COLUMN_UPDATED_AT => $item->getUpdatedAt(),
                self::COLUMN_APPROVED_AT => $item->getApprovedAt(),
                self::COLUMN_APPROVED_BY => $item->getApprovedByUsername(),
                self::COLUMN_REJECTION_REASON => $item->getRejectionReason()
            ];
        }
        
        return $exportData;
    }

    /**
     * Custom fields mapping
     *
     * @param array $dataRow
     * @return array
     */
    protected function _customFieldsMapping($dataRow)
    {
        return $dataRow;
    }

    /**
     * Entity attributes collection
     *
     * @return array
     */
    public function getAttributeCollection()
    {
        return [];
    }
}