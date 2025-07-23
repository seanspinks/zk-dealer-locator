# Developer Guide - Zhik Dealer Locator

Technical documentation for developers working with the Zhik Dealer Locator module.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Database Schema](#database-schema)
3. [Models and Repositories](#models-and-repositories)
4. [Service Contracts](#service-contracts)
5. [Controllers](#controllers)
6. [Blocks and View Models](#blocks-and-view-models)
7. [JavaScript Components](#javascript-components)
8. [Events and Observers](#events-and-observers)
9. [Plugins and Preferences](#plugins-and-preferences)
10. [API Endpoints](#api-endpoints)
11. [Testing](#testing)
12. [Extension Points](#extension-points)
13. [Performance Considerations](#performance-considerations)
14. [Security](#security)

## Architecture Overview

### Module Structure

```
DealerLocator/
├── Api/                        # Service contracts
│   ├── Data/                  # Data interfaces
│   │   ├── LocationInterface.php
│   │   ├── LocationSearchResultsInterface.php
│   │   ├── TagInterface.php
│   │   └── TagSearchResultsInterface.php
│   ├── LocationRepositoryInterface.php
│   ├── LocationSearchInterface.php
│   └── TagRepositoryInterface.php
├── Block/                      # Block classes
│   ├── Adminhtml/             # Admin blocks
│   ├── Customer/              # Customer account blocks
│   └── Map/                   # Map display blocks
├── Controller/                 # Controllers
│   ├── Adminhtml/             # Admin controllers
│   ├── Customer/              # Customer controllers
│   └── Index/                 # Frontend controllers
├── Helper/                     # Helper classes
│   └── Email.php              # Email functionality
├── Model/                      # Business logic
│   ├── Location.php           # Location model
│   ├── Tag.php                # Tag model
│   ├── LocationRepository.php # Repository implementation
│   └── ResourceModel/         # Database resources
├── Plugin/                     # Plugins
│   └── LocationRepositoryPlugin.php
├── Setup/                      # Installation/upgrade
│   └── Patch/                 # Data patches
├── Ui/                        # UI components
│   ├── Component/             # Grid customizations
│   └── DataProvider/          # Data providers
├── etc/                       # Configuration
│   ├── module.xml             # Module declaration
│   ├── di.xml                 # Dependency injection
│   ├── db_schema.xml          # Database schema
│   ├── routes.xml             # Frontend routes
│   ├── adminhtml/             # Admin configurations
│   └── frontend/              # Frontend configurations
└── view/                      # View layer
    ├── adminhtml/             # Admin templates/layouts
    └── frontend/              # Frontend templates/layouts
```

### Design Patterns

1. **Repository Pattern**
   - Clean data access layer
   - Implements service contracts
   - Handles CRUD operations

2. **Service Contracts**
   - API-first approach
   - Stable interfaces
   - Versioning support

3. **Dependency Injection**
   - Constructor injection
   - Virtual types for customization
   - Interface preferences

4. **UI Components**
   - Declarative grid configuration
   - Form components
   - Data providers

## Database Schema

### Tables Structure

#### dealer_location
Primary location data table.

```sql
CREATE TABLE `dealer_location` (
  `location_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(2) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `hours` text,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `customer_id` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `is_latest` tinyint(1) NOT NULL DEFAULT '1',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `created_by` varchar(40) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`),
  KEY `idx_status` (`status`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_coordinates` (`latitude`,`longitude`),
  KEY `idx_latest` (`is_latest`),
  KEY `idx_store` (`store_id`),
  CONSTRAINT `fk_location_customer` FOREIGN KEY (`customer_id`) 
    REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_location_store` FOREIGN KEY (`store_id`) 
    REFERENCES `store` (`store_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### dealer_location_tag
Tag definitions table.

```sql
CREATE TABLE `dealer_location_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `color` varchar(7) DEFAULT '#000000',
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### dealer_location_tag_relation
Many-to-many relationship table.

```sql
CREATE TABLE `dealer_location_tag_relation` (
  `location_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_id`,`tag_id`),
  KEY `fk_tag` (`tag_id`),
  CONSTRAINT `fk_relation_location` FOREIGN KEY (`location_id`) 
    REFERENCES `dealer_location` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_relation_tag` FOREIGN KEY (`tag_id`) 
    REFERENCES `dealer_location_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Status Values

```php
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';
const STATUS_REJECTED = 'rejected';
const STATUS_PENDING_DELETION = 'pending_deletion';
```

### Version Control

Locations use `parent_id` and `is_latest` for versioning:
- New version: Copy location, set `parent_id`, update `is_latest`
- History: Query by `parent_id` chain
- Current: Filter by `is_latest = 1`

## Models and Repositories

### Location Model

```php
namespace Zhik\DealerLocator\Model;

use Zhik\DealerLocator\Api\Data\LocationInterface;
use Magento\Framework\Model\AbstractModel;

class Location extends AbstractModel implements LocationInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Location::class);
    }
    
    // Implement interface methods
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }
    
    public function setName(string $name): LocationInterface
    {
        return $this->setData(self::NAME, $name);
    }
    
    // Additional methods...
}
```

### Location Repository

```php
namespace Zhik\DealerLocator\Model;

use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

class LocationRepository implements LocationRepositoryInterface
{
    public function save(LocationInterface $location): LocationInterface
    {
        try {
            // Version control logic
            if ($location->getId() && $this->hasChanges($location)) {
                $location = $this->createVersion($location);
            }
            
            // Geocoding logic
            if ($this->needsGeocoding($location)) {
                $this->geocodeLocation($location);
            }
            
            $this->resource->save($location);
            
            // Clear cache
            $this->cacheManager->clean(['dealer_locator']);
            
            return $location;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }
    
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        
        // Apply search criteria
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        // Build result
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }
}
```

### Resource Model

```php
namespace Zhik\DealerLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Location extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dealer_location', 'location_id');
    }
    
    public function getLocationTags($locationId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('dealer_location_tag_relation'), 'tag_id')
            ->where('location_id = ?', $locationId);
            
        return $connection->fetchCol($select);
    }
    
    public function saveLocationTags($locationId, array $tagIds)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('dealer_location_tag_relation');
        
        // Delete existing
        $connection->delete($table, ['location_id = ?' => $locationId]);
        
        // Insert new
        if (!empty($tagIds)) {
            $data = [];
            foreach ($tagIds as $tagId) {
                $data[] = [
                    'location_id' => $locationId,
                    'tag_id' => $tagId
                ];
            }
            $connection->insertMultiple($table, $data);
        }
    }
}
```

## Service Contracts

### Location Repository Interface

```php
namespace Zhik\DealerLocator\Api;

interface LocationRepositoryInterface
{
    /**
     * Save location
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\LocationInterface $location);
    
    /**
     * Get location by ID
     *
     * @param int $locationId
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($locationId);
    
    /**
     * Delete location
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\LocationInterface $location);
    
    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
```

### Location Search Interface

```php
namespace Zhik\DealerLocator\Api;

interface LocationSearchInterface
{
    /**
     * Search locations
     *
     * @param string|null $query
     * @param int[]|null $tagIds
     * @param string|null $country
     * @param string|null $state
     * @param string|null $city
     * @param int|null $limit
     * @param int|null $page
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function search(
        ?string $query = null,
        ?array $tagIds = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?int $limit = null,
        ?int $page = null
    ): Data\LocationSearchResultsInterface;
    
    /**
     * Search nearby locations
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radius in kilometers
     * @param int[]|null $tagIds
     * @param int|null $limit
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function searchNearby(
        float $latitude,
        float $longitude,
        float $radius,
        ?array $tagIds = null,
        ?int $limit = null
    ): Data\LocationSearchResultsInterface;
}
```

## Controllers

### Frontend Controller Example

```php
namespace Zhik\DealerLocator\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index implements HttpGetActionInterface
{
    private $resultFactory;
    private $configHelper;
    
    public function __construct(
        ResultFactory $resultFactory,
        ConfigHelper $configHelper
    ) {
        $this->resultFactory = $resultFactory;
        $this->configHelper = $configHelper;
    }
    
    public function execute()
    {
        if (!$this->configHelper->isEnabled()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $result->forward('noroute');
        }
        
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->getConfig()->getTitle()->set(__('Find a Dealer'));
        
        return $page;
    }
}
```

### Admin Controller Example

```php
namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Save extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_save';
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            try {
                $location = $this->processLocationData($data);
                $this->locationRepository->save($location);
                
                $this->messageManager->addSuccessMessage(__('Location saved.'));
                
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'location_id' => $location->getId()
                    ]);
                }
                
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', [
                    'location_id' => $this->getRequest()->getParam('location_id')
                ]);
            }
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}
```

### Customer Controller Example

```php
namespace Zhik\DealerLocator\Controller\Customer\Location;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Save extends AbstractAccount implements HttpPostActionInterface
{
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }
        
        $data = $this->getRequest()->getPostValue();
        
        try {
            $location = $this->locationFactory->create();
            $location->setData($data);
            $location->setCustomerId($this->customerSession->getCustomerId());
            $location->setStatus(LocationInterface::STATUS_PENDING);
            
            $this->locationRepository->save($location);
            
            $this->messageManager->addSuccessMessage(
                __('Your location has been submitted for review.')
            );
            
            // Send notifications
            $this->emailHelper->sendNewSubmissionNotification($location);
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
```

## Blocks and View Models

### Map Display Block

```php
namespace Zhik\DealerLocator\Block\Map;

use Magento\Framework\View\Element\Template;
use Zhik\DealerLocator\Model\Config;

class View extends Template
{
    private $config;
    private $jsonEncoder;
    private $locationSearch;
    
    public function getMapConfig()
    {
        return $this->jsonEncoder->encode([
            'apiKey' => $this->config->getGoogleMapsApiKey(),
            'center' => [
                'lat' => (float) $this->config->getDefaultLatitude(),
                'lng' => (float) $this->config->getDefaultLongitude()
            ],
            'zoom' => (int) $this->config->getDefaultZoom(),
            'searchUrl' => $this->getUrl('dealerlocator/ajax/search'),
            'detailsUrl' => $this->getUrl('dealerlocator/ajax/details'),
            'clustering' => $this->config->isClusteringEnabled(),
            'clusterMinimum' => (int) $this->config->getClusterMinimum()
        ]);
    }
    
    public function getInitialLocations()
    {
        $searchResults = $this->locationSearch->search(null, null, null, null, null, 100);
        
        $locations = [];
        foreach ($searchResults->getItems() as $location) {
            $locations[] = $this->formatLocationForJs($location);
        }
        
        return $this->jsonEncoder->encode($locations);
    }
}
```

### Customer Account Block

```php
namespace Zhik\DealerLocator\Block\Customer\Account;

use Magento\Framework\View\Element\Template;

class Locations extends Template
{
    public function getCustomerLocations()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return [];
        }
        
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)
            ->addFilter('is_latest', 1)
            ->create();
            
        return $this->locationRepository->getList($searchCriteria)->getItems();
    }
    
    public function getLocationsByStatus()
    {
        $locations = $this->getCustomerLocations();
        $grouped = [];
        
        foreach ($locations as $location) {
            $status = $location->getStatus();
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = $location;
        }
        
        return $grouped;
    }
}
```

## JavaScript Components

### Map Component

```javascript
// view/frontend/web/js/map.js
define([
    'jquery',
    'mage/url',
    'Zhik_DealerLocator/js/map/loader',
    'Zhik_DealerLocator/js/map/markers',
    'Zhik_DealerLocator/js/map/search'
], function ($, urlBuilder, loader, markers, search) {
    'use strict';
    
    return function (config, element) {
        var map,
            mapElement = $(element),
            searchBox;
        
        // Initialize map
        loader.load(config.apiKey).then(function () {
            map = new google.maps.Map(mapElement[0], {
                center: config.center,
                zoom: config.zoom
            });
            
            // Initialize markers
            markers.init(map, config);
            
            // Initialize search
            if (config.showSearch) {
                searchBox = search.init(map, config.searchUrl);
            }
            
            // Load initial locations
            loadLocations();
        });
        
        function loadLocations() {
            $.ajax({
                url: config.searchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    markers.addLocations(response.items);
                }
            });
        }
    };
});
```

### AJAX Search Handler

```javascript
// view/frontend/web/js/map/search.js
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';
    
    return {
        init: function (map, searchUrl) {
            var searchBox = new google.maps.places.SearchBox(
                document.getElementById('dealer-search-input')
            );
            
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(
                document.getElementById('dealer-search-box')
            );
            
            searchBox.addListener('places_changed', function () {
                var places = searchBox.getPlaces();
                
                if (places.length === 0) {
                    return;
                }
                
                var place = places[0];
                
                if (place.geometry) {
                    this.searchNearby(
                        place.geometry.location.lat(),
                        place.geometry.location.lng()
                    );
                }
            }.bind(this));
            
            return this;
        },
        
        searchNearby: function (lat, lng, radius) {
            $.ajax({
                url: this.searchUrl,
                type: 'GET',
                data: {
                    latitude: lat,
                    longitude: lng,
                    radius: radius || 50
                },
                success: function (response) {
                    this.updateResults(response.items);
                }.bind(this)
            });
        }
    };
});
```

## Events and Observers

### Available Events

While the module doesn't dispatch custom events by default, you can observe standard Magento events:

```xml
<!-- etc/events.xml -->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    
    <!-- Admin events -->
    <event name="adminhtml_customer_save_after">
        <observer name="dealerlocator_update_customer_locations" 
                  instance="Zhik\DealerLocator\Observer\UpdateCustomerLocations" />
    </event>
    
    <!-- Model events -->
    <event name="model_save_before">
        <observer name="dealerlocator_location_save_before" 
                  instance="Zhik\DealerLocator\Observer\LocationSaveBefore" />
    </event>
</config>
```

### Custom Event Dispatching

Add custom events in your code:

```php
// In LocationRepository::save()
$this->eventManager->dispatch(
    'dealerlocator_location_save_before',
    ['location' => $location]
);

// After save
$this->eventManager->dispatch(
    'dealerlocator_location_save_after',
    ['location' => $location]
);
```

### Observer Example

```php
namespace Zhik\DealerLocator\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LocationSaveBefore implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $location = $observer->getEvent()->getLocation();
        
        // Add custom logic
        if (!$location->getCreatedBy()) {
            $location->setCreatedBy($this->getUserIdentifier());
        }
        
        // Validate data
        if ($this->needsValidation($location)) {
            $this->validateLocation($location);
        }
    }
}
```

## Plugins and Preferences

### Repository Plugin

```php
namespace Zhik\DealerLocator\Plugin;

use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

class LocationRepositoryPlugin
{
    /**
     * Add extension attributes after load
     */
    public function afterGetById(
        LocationRepositoryInterface $subject,
        LocationInterface $result
    ) {
        $extensionAttributes = $result->getExtensionAttributes();
        
        // Add custom data
        $tags = $this->getLocationTags($result->getId());
        $extensionAttributes->setTags($tags);
        
        $result->setExtensionAttributes($extensionAttributes);
        
        return $result;
    }
    
    /**
     * Save extension attributes
     */
    public function afterSave(
        LocationRepositoryInterface $subject,
        LocationInterface $result,
        LocationInterface $location
    ) {
        $extensionAttributes = $location->getExtensionAttributes();
        
        if ($extensionAttributes && $extensionAttributes->getTags()) {
            $this->saveLocationTags($result->getId(), $extensionAttributes->getTags());
        }
        
        return $result;
    }
}
```

### Preference Example

```xml
<!-- etc/di.xml -->
<preference for="Zhik\DealerLocator\Api\LocationRepositoryInterface" 
            type="Zhik\DealerLocator\Model\LocationRepository" />

<!-- Virtual Type Example -->
<virtualType name="DealerLocatorGirdFilterPool" 
             type="Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool">
    <arguments>
        <argument name="appliers" xsi:type="array">
            <item name="regular" xsi:type="object">
                Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter
            </item>
            <item name="fulltext" xsi:type="object">
                Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter
            </item>
        </argument>
    </arguments>
</virtualType>
```

## API Endpoints

### REST API Endpoints

All endpoints follow Magento's REST API standards.

#### Search Locations
```
GET /rest/V1/dealerlocator/search
```

Parameters:
- `searchCriteria[filter_groups][0][filters][0][field]` - Field to filter
- `searchCriteria[filter_groups][0][filters][0][value]` - Value to filter
- `searchCriteria[filter_groups][0][filters][0][condition_type]` - Condition (eq, like, gt, etc.)
- `searchCriteria[pageSize]` - Page size
- `searchCriteria[currentPage]` - Current page

Example:
```bash
curl -X GET "https://example.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=status&\
searchCriteria[filter_groups][0][filters][0][value]=approved&\
searchCriteria[pageSize]=20"
```

#### Search Nearby
```
GET /rest/V1/dealerlocator/search/nearby
```

Parameters:
- `latitude` - Center latitude
- `longitude` - Center longitude  
- `radius` - Search radius in kilometers
- `tagIds` - Array of tag IDs (optional)
- `limit` - Result limit (optional)

Example:
```bash
curl -X GET "https://example.com/rest/V1/dealerlocator/search/nearby?\
latitude=40.7128&longitude=-74.0060&radius=50"
```

#### Get Single Location
```
GET /rest/V1/dealerlocator/location/:locationId
```

#### Customer Endpoints

##### Get Customer Locations
```
GET /rest/V1/dealerlocator/mine/locations
```

Requires customer token.

##### Submit Location
```
POST /rest/V1/dealerlocator/mine/locations
```

Request body:
```json
{
    "location": {
        "name": "Store Name",
        "street": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postal_code": "10001",
        "country": "US",
        "phone": "555-1234",
        "email": "store@example.com",
        "website": "https://example.com",
        "description": "Store description"
    }
}
```

### GraphQL Support

While not implemented by default, GraphQL can be added:

```graphql
type Query {
    dealerLocations(
        filter: LocationFilterInput
        pageSize: Int = 20
        currentPage: Int = 1
    ): LocationsOutput
    
    dealerLocation(id: Int!): Location
}

type LocationsOutput {
    total_count: Int
    items: [Location]
}

type Location {
    location_id: Int
    name: String
    address: Address
    coordinates: Coordinates
    contact: ContactInfo
    status: String
    tags: [Tag]
}
```

## Testing

### Unit Test Structure

```
Test/Unit/
├── Model/
│   ├── LocationTest.php
│   ├── LocationRepositoryTest.php
│   └── TagTest.php
├── Controller/
│   └── Customer/
│       └── Location/
│           └── SaveTest.php
└── Helper/
    └── EmailTest.php
```

### Unit Test Example

```php
namespace Zhik\DealerLocator\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Zhik\DealerLocator\Model\Location;

class LocationTest extends TestCase
{
    private $location;
    
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        
        $this->location = $objectManager->getObject(Location::class);
    }
    
    public function testGettersAndSetters()
    {
        $name = 'Test Location';
        $this->location->setName($name);
        $this->assertEquals($name, $this->location->getName());
    }
    
    public function testStatusConstants()
    {
        $this->assertEquals('pending', Location::STATUS_PENDING);
        $this->assertEquals('approved', Location::STATUS_APPROVED);
        $this->assertEquals('rejected', Location::STATUS_REJECTED);
    }
}
```

### Integration Test Example

```php
namespace Zhik\DealerLocator\Test\Integration\Model;

use Magento\TestFramework\TestCase\AbstractController;

class LocationRepositoryTest extends AbstractController
{
    private $locationRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->locationRepository = $this->_objectManager->get(
            \Zhik\DealerLocator\Api\LocationRepositoryInterface::class
        );
    }
    
    /**
     * @magentoDataFixture Zhik_DealerLocator::Test/_files/locations.php
     */
    public function testGetById()
    {
        $location = $this->locationRepository->getById(1);
        $this->assertNotNull($location);
        $this->assertEquals('Test Location', $location->getName());
    }
}
```

### API Functional Test

```php
namespace Zhik\DealerLocator\Test\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class SearchTest extends WebapiAbstract
{
    public function testSearchLocations()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/dealerlocator/search',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ]
        ];
        
        $requestData = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'status',
                                'value' => 'approved',
                                'condition_type' => 'eq'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $result = $this->_webApiCall($serviceInfo, $requestData);
        
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total_count', $result);
    }
}
```

## Extension Points

### Adding Custom Fields

1. **Update Database Schema**
```xml
<!-- etc/db_schema_whitelist.json -->
{
    "dealer_location": {
        "column": {
            "custom_field": true
        }
    }
}
```

2. **Extend Interface**
```php
// In your module's etc/di.xml
<preference for="Zhik\DealerLocator\Api\Data\LocationInterface" 
            type="YourVendor\YourModule\Model\Location" />
```

3. **Add to Forms and Grids**
```xml
<!-- view/adminhtml/ui_component/dealerlocator_location_form.xml -->
<field name="custom_field">
    <settings>
        <dataType>text</dataType>
        <label translate="true">Custom Field</label>
        <dataScope>custom_field</dataScope>
    </settings>
</field>
```

### Adding Map Providers

Create alternative map provider:

```php
namespace YourVendor\YourModule\Model\Map;

class OpenStreetMapProvider implements MapProviderInterface
{
    public function getMapHtml($config)
    {
        // Return OpenStreetMap implementation
    }
    
    public function geocodeAddress($address)
    {
        // Use Nominatim API
    }
}
```

Configure in `di.xml`:
```xml
<preference for="Zhik\DealerLocator\Model\Map\ProviderInterface" 
            type="YourVendor\YourModule\Model\Map\OpenStreetMapProvider" />
```

### Custom Import/Export

Implement custom import adapter:

```php
namespace YourVendor\YourModule\Model\Import;

class LocationImporter
{
    public function import($file)
    {
        $csv = $this->csvProcessor->getData($file);
        
        foreach ($csv as $row) {
            $location = $this->locationFactory->create();
            $location->setData($this->mapCsvToLocation($row));
            
            $this->locationRepository->save($location);
        }
    }
}
```

## Performance Considerations

### Database Optimization

1. **Indexes**
   - Status index for filtering
   - Customer ID for account queries
   - Coordinates for geographic searches
   - Store ID for multi-store

2. **Query Optimization**
   ```php
   // Use collection for bulk operations
   $collection = $this->collectionFactory->create();
   $collection->addFieldToFilter('status', 'approved')
              ->addFieldToFilter('is_latest', 1)
              ->setPageSize(100);
   ```

3. **Caching Strategy**
   ```php
   // Cache tag usage
   const CACHE_TAG = 'dealer_location';
   
   public function getCacheTags()
   {
       return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
   }
   ```

### Frontend Performance

1. **Lazy Loading**
   ```javascript
   // Load markers on demand
   google.maps.event.addListener(map, 'idle', function() {
       var bounds = map.getBounds();
       loadLocationsInBounds(bounds);
   });
   ```

2. **Marker Clustering**
   ```javascript
   var markerCluster = new MarkerClusterer(map, markers, {
       maxZoom: 15,
       gridSize: 60
   });
   ```

3. **AJAX Pagination**
   ```javascript
   function loadMore() {
       $.ajax({
           url: config.searchUrl,
           data: {
               p: ++currentPage,
               limit: pageSize
           },
           success: function(response) {
               if (response.items.length) {
                   addMarkers(response.items);
               }
           }
       });
   }
   ```

### API Performance

1. **Response Caching**
   ```php
   public function getList($searchCriteria)
   {
       $cacheKey = $this->getCacheKey($searchCriteria);
       
       if ($cached = $this->cache->load($cacheKey)) {
           return $this->serializer->unserialize($cached);
       }
       
       $result = $this->performSearch($searchCriteria);
       
       $this->cache->save(
           $this->serializer->serialize($result),
           $cacheKey,
           [self::CACHE_TAG],
           3600
       );
       
       return $result;
   }
   ```

## Security

### Input Validation

```php
// Validate coordinates
if (!$this->isValidLatitude($location->getLatitude())) {
    throw new \InvalidArgumentException('Invalid latitude');
}

// Sanitize HTML
$description = $this->filterManager->stripTags(
    $location->getDescription(),
    ['allowableTags' => '<p><br><strong><em>']
);

// Validate URL
if (!filter_var($location->getWebsite(), FILTER_VALIDATE_URL)) {
    throw new \InvalidArgumentException('Invalid website URL');
}
```

### SQL Injection Prevention

Always use parameterized queries:

```php
// Good
$select = $connection->select()
    ->from($table)
    ->where('location_id = ?', $locationId);

// Bad - Never do this
$select = $connection->select()
    ->from($table)
    ->where("location_id = {$locationId}");
```

### XSS Prevention

Escape output in templates:

```php
<!-- Good -->
<h1><?= $block->escapeHtml($location->getName()) ?></h1>
<div><?= $block->escapeHtml($location->getDescription()) ?></div>

<!-- For URLs -->
<a href="<?= $block->escapeUrl($location->getWebsite()) ?>">Website</a>

<!-- For JavaScript -->
<script>
var locationName = <?= $block->escapeJs($location->getName()) ?>;
</script>
```

### CSRF Protection

Controllers extend Magento's base classes which include CSRF protection:

```php
class Save extends \Magento\Customer\Controller\AbstractAccount 
    implements HttpPostActionInterface
{
    // CSRF token automatically validated for POST requests
}
```

### Access Control

Always check permissions:

```php
// Admin controller
const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_save';

// Customer controller
if (!$this->customerSession->isLoggedIn()) {
    throw new LocalizedException(__('Authentication required'));
}

if ($location->getCustomerId() != $this->customerSession->getCustomerId()) {
    throw new LocalizedException(__('Access denied'));
}
```

---

For more information on using the module, see the [Admin Guide](ADMIN-GUIDE.md) and [API Documentation](API.md).