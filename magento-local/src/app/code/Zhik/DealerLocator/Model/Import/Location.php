<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Import;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * Location import model
 */
class Location extends AbstractEntity
{
    const ENTITY_CODE = 'dealerlocator_location';
    const TABLE = 'zhik_dealer_locations';
    const ENTITY_ID_COLUMN = 'location_id';

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
    const COLUMN_APPROVED_BY = 'approved_by';

    /**
     * Valid column names
     *
     * @var array
     */
    protected $validColumnNames = [
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
        self::COLUMN_APPROVED_BY
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var LocationInterfaceFactory
     */
    protected $locationFactory;

    /**
     * @var TagCollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * @var UserCollectionFactory
     */
    protected $userCollectionFactory;

    /**
     * @var array
     */
    protected $tagIds = [];

    /**
     * @var array
     */
    protected $customerIds = [];

    /**
     * @var array
     */
    protected $adminUserIds = [];

    /**
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param CustomerRepositoryInterface $customerRepository
     * @param LocationRepositoryInterface $locationRepository
     * @param LocationInterfaceFactory $locationFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param UserCollectionFactory $userCollectionFactory
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        CustomerRepositoryInterface $customerRepository,
        LocationRepositoryInterface $locationRepository,
        LocationInterfaceFactory $locationFactory,
        TagCollectionFactory $tagCollectionFactory,
        UserCollectionFactory $userCollectionFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->customerRepository = $customerRepository;
        $this->locationRepository = $locationRepository;
        $this->locationFactory = $locationFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->_initTags();
        $this->_initAdminUsers();
    }

    /**
     * Initialize tags
     *
     * @return void
     */
    protected function _initTags()
    {
        $collection = $this->tagCollectionFactory->create();
        foreach ($collection as $tag) {
            $this->tagIds[strtolower($tag->getName())] = $tag->getTagId();
        }
    }

    /**
     * Initialize admin users
     *
     * @return void
     */
    protected function _initAdminUsers()
    {
        $collection = $this->userCollectionFactory->create();
        foreach ($collection as $user) {
            $this->adminUserIds[strtolower($user->getUsername())] = $user->getUserId();
        }
    }

    /**
     * Entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_CODE;
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COLUMN_LOCATION_ID])) {
                $this->addRowError('LocationIdIsRequired', $rowNum);
                return false;
            }
        } else {
            // Validate required fields
            if (empty($rowData[self::COLUMN_NAME])) {
                $this->addRowError('NameIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_ADDRESS])) {
                $this->addRowError('AddressIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_CITY])) {
                $this->addRowError('CityIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_POSTAL_CODE])) {
                $this->addRowError('PostalCodeIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_COUNTRY])) {
                $this->addRowError('CountryIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_PHONE])) {
                $this->addRowError('PhoneIsRequired', $rowNum);
                return false;
            }
            if (empty($rowData[self::COLUMN_EMAIL])) {
                $this->addRowError('EmailIsRequired', $rowNum);
                return false;
            }
            
            // Validate customer
            if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
                if (!$this->_validateCustomerEmail($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
                    $this->addRowError('InvalidCustomerEmail', $rowNum);
                    return false;
                }
            } elseif (!empty($rowData[self::COLUMN_CUSTOMER_ID])) {
                if (!$this->_validateCustomerId($rowData[self::COLUMN_CUSTOMER_ID])) {
                    $this->addRowError('InvalidCustomerId', $rowNum);
                    return false;
                }
            } else {
                $this->addRowError('CustomerIdOrEmailRequired', $rowNum);
                return false;
            }
            
            // Validate coordinates
            if (!empty($rowData[self::COLUMN_LATITUDE])) {
                if (!is_numeric($rowData[self::COLUMN_LATITUDE]) || 
                    $rowData[self::COLUMN_LATITUDE] < -90 || 
                    $rowData[self::COLUMN_LATITUDE] > 90) {
                    $this->addRowError('InvalidLatitude', $rowNum);
                    return false;
                }
            }
            if (!empty($rowData[self::COLUMN_LONGITUDE])) {
                if (!is_numeric($rowData[self::COLUMN_LONGITUDE]) || 
                    $rowData[self::COLUMN_LONGITUDE] < -180 || 
                    $rowData[self::COLUMN_LONGITUDE] > 180) {
                    $this->addRowError('InvalidLongitude', $rowNum);
                    return false;
                }
            }
            
            // Validate status
            if (!empty($rowData[self::COLUMN_STATUS])) {
                $validStatuses = ['pending', 'approved', 'rejected'];
                if (!in_array($rowData[self::COLUMN_STATUS], $validStatuses)) {
                    $this->addRowError('InvalidStatus', $rowNum);
                    return false;
                }
            }
        }
        
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Validate customer email
     *
     * @param string $email
     * @return bool
     */
    protected function _validateCustomerEmail($email)
    {
        if (!isset($this->customerIds[$email])) {
            try {
                $customer = $this->customerRepository->get($email);
                $this->customerIds[$email] = $customer->getId();
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate customer ID
     *
     * @param int $customerId
     * @return bool
     */
    protected function _validateCustomerId($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $this->customerIds[$customer->getEmail()] = $customer->getId();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Import data
     *
     * @return bool
     * @throws \Exception
     */
    protected function _importData()
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteLocations();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->replaceLocations();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveLocations();
                break;
        }
        
        return true;
    }

    /**
     * Save locations
     *
     * @return $this
     */
    protected function saveLocations()
    {
        $this->saveAndReplaceLocations();
        return $this;
    }

    /**
     * Replace locations
     *
     * @return $this
     */
    protected function replaceLocations()
    {
        $this->saveAndReplaceLocations();
        return $this;
    }

    /**
     * Save and replace locations
     *
     * @return $this
     */
    protected function saveAndReplaceLocations()
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowId = $row[self::COLUMN_LOCATION_ID] ?? null;
                $rows[] = $this->_prepareRowData($row);
            }
        }
        
        if ($rows) {
            $this->_saveLocations($rows);
        }
        
        return $this;
    }

    /**
     * Prepare row data
     *
     * @param array $row
     * @return array
     */
    protected function _prepareRowData($row)
    {
        // Get customer ID
        $customerId = null;
        if (!empty($row[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->customerIds[$row[self::COLUMN_CUSTOMER_EMAIL]] ?? null;
        } elseif (!empty($row[self::COLUMN_CUSTOMER_ID])) {
            $customerId = $row[self::COLUMN_CUSTOMER_ID];
        }
        
        $locationData = [
            'customer_id' => $customerId,
            'name' => $row[self::COLUMN_NAME],
            'address' => $row[self::COLUMN_ADDRESS],
            'city' => $row[self::COLUMN_CITY],
            'state' => $row[self::COLUMN_STATE] ?? '',
            'postal_code' => $row[self::COLUMN_POSTAL_CODE],
            'country' => $row[self::COLUMN_COUNTRY],
            'phone' => $row[self::COLUMN_PHONE],
            'email' => $row[self::COLUMN_EMAIL],
            'website' => $row[self::COLUMN_WEBSITE] ?? '',
            'hours' => $row[self::COLUMN_HOURS] ?? '',
            'description' => $row[self::COLUMN_DESCRIPTION] ?? '',
            'latitude' => !empty($row[self::COLUMN_LATITUDE]) ? (float)$row[self::COLUMN_LATITUDE] : null,
            'longitude' => !empty($row[self::COLUMN_LONGITUDE]) ? (float)$row[self::COLUMN_LONGITUDE] : null,
            'status' => $row[self::COLUMN_STATUS] ?? 'pending',
            'is_active' => isset($row[self::COLUMN_IS_ACTIVE]) ? (int)$row[self::COLUMN_IS_ACTIVE] : 1,
            'is_latest' => 1
        ];
        
        if (!empty($row[self::COLUMN_LOCATION_ID])) {
            $locationData['location_id'] = $row[self::COLUMN_LOCATION_ID];
        }
        
        // Parse tags
        if (!empty($row[self::COLUMN_TAGS])) {
            $tagNames = array_map('trim', explode(',', $row[self::COLUMN_TAGS]));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $tagKey = strtolower($tagName);
                if (isset($this->tagIds[$tagKey])) {
                    $tagIds[] = $this->tagIds[$tagKey];
                }
            }
            $locationData['tag_ids'] = $tagIds;
        }
        
        // Parse approved by
        if (!empty($row[self::COLUMN_APPROVED_BY])) {
            $adminKey = strtolower($row[self::COLUMN_APPROVED_BY]);
            if (isset($this->adminUserIds[$adminKey])) {
                $locationData['approved_by'] = $this->adminUserIds[$adminKey];
            }
        }
        
        return $locationData;
    }

    /**
     * Save locations
     *
     * @param array $locations
     * @return void
     */
    protected function _saveLocations($locations)
    {
        foreach ($locations as $locationData) {
            try {
                if (!empty($locationData['location_id'])) {
                    $location = $this->locationRepository->getById($locationData['location_id']);
                } else {
                    $location = $this->locationFactory->create();
                }
                
                $tagIds = $locationData['tag_ids'] ?? [];
                unset($locationData['tag_ids']);
                
                foreach ($locationData as $key => $value) {
                    $location->setData($key, $value);
                }
                
                if ($tagIds) {
                    $location->setData('tag_ids', $tagIds);
                }
                
                $this->locationRepository->save($location);
            } catch (\Exception $e) {
                $this->getErrorAggregator()->addError(
                    $e->getCode(),
                    ProcessingErrorAggregatorInterface::ERROR_LEVEL_NOT_CRITICAL,
                    null,
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Delete locations
     *
     * @return $this
     */
    protected function deleteLocations()
    {
        $idsToDelete = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum)) {
                    $idsToDelete[] = $rowData[self::COLUMN_LOCATION_ID];
                }
            }
        }
        
        if ($idsToDelete) {
            foreach ($idsToDelete as $locationId) {
                try {
                    $this->locationRepository->deleteById($locationId);
                } catch (\Exception $e) {
                    $this->getErrorAggregator()->addError(
                        $e->getCode(),
                        ProcessingErrorAggregatorInterface::ERROR_LEVEL_NOT_CRITICAL,
                        null,
                        $e->getMessage()
                    );
                }
            }
        }
        
        return $this;
    }
}