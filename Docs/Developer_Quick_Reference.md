# Dealer Locator Developer Quick Reference

## Module Structure
```
Zhik/DealerLocator/
├── Api/                          # Service contracts
│   ├── Data/                    # Data interfaces
│   │   ├── LocationInterface.php
│   │   ├── LocationSearchResultsInterface.php
│   │   ├── TagInterface.php
│   │   └── TagSearchResultsInterface.php
│   ├── LocationApprovalInterface.php
│   ├── LocationManagementInterface.php
│   ├── LocationRepositoryInterface.php
│   ├── LocationSearchInterface.php
│   └── TagRepositoryInterface.php
├── Block/                       # Frontend blocks
├── Controller/                  # Controllers
│   ├── Adminhtml/              # Admin controllers
│   │   ├── Location/
│   │   └── Tag/
│   └── Account/                # Customer account
├── Helper/                     # Helper classes
│   └── Email.php
├── Model/                      # Business logic
│   ├── Export/                # Export models
│   ├── Import/                # Import models
│   ├── ResourceModel/         # Database operations
│   ├── Location.php
│   ├── LocationApproval.php
│   ├── LocationManagement.php
│   ├── LocationRepository.php
│   ├── LocationSearch.php
│   ├── Tag.php
│   └── TagRepository.php
├── Plugin/                    # Plugins
│   └── LocationRepositoryPlugin.php
├── Setup/                     # Installation scripts
│   └── Patch/
├── Ui/                       # UI components
│   └── Component/
├── etc/                      # Configuration
│   ├── adminhtml/           # Admin configuration
│   ├── db_schema.xml       # Database schema
│   ├── di.xml             # Dependency injection
│   ├── email_templates.xml # Email templates
│   ├── events.xml         # Event observers
│   ├── export.xml         # Export configuration
│   ├── import.xml         # Import configuration
│   ├── module.xml         # Module declaration
│   └── webapi.xml         # REST API routes
├── i18n/                   # Translations
├── view/                   # Frontend/admin templates
│   ├── adminhtml/
│   └── frontend/
└── Samples/               # Sample import files
```

## Key Classes & Interfaces

### Repository Pattern
```php
// Get location by ID
$location = $locationRepository->getById($id);

// Save location
$locationRepository->save($location);

// Delete location
$locationRepository->delete($location);

// Search with filters
$searchCriteria = $searchCriteriaBuilder
    ->addFilter('status', 'approved')
    ->addFilter('is_active', 1)
    ->create();
$results = $locationRepository->getList($searchCriteria);
```

### Location Management
```php
// Get customer locations
$locations = $locationManagement->getCustomerLocations($customerId);

// Submit new location
$location = $locationManagement->submitLocation($locationData, $customerId);
```

### Search Operations
```php
// Text search
$results = $locationSearch->search('bike shop', ['city' => 'New York']);

// Nearby search
$results = $locationSearch->searchNearby(
    40.7128,  // latitude
    -74.0060, // longitude
    10,       // radius in km
    [1, 2],   // tag IDs (optional)
    20        // limit (optional)
);
```

### Approval Workflow
```php
// Approve location
$locationApproval->approve($locationId, $adminUserId);

// Reject location
$locationApproval->reject($locationId, $adminUserId, 'Incomplete information');
```

## Database Queries

### Direct Collection Usage
```php
// Get locations collection
$collection = $locationCollectionFactory->create();
$collection->addFieldToFilter('status', 'approved')
           ->addFieldToFilter('is_active', 1)
           ->setOrder('name', 'ASC');

// Join with tags
$collection->getSelect()->joinLeft(
    ['lt' => $collection->getTable('zhik_dealer_location_tag')],
    'main_table.location_id = lt.location_id',
    []
)->where('lt.tag_id IN (?)', [1, 2]);
```

### Resource Model Methods
```php
// Get location tags
$tagIds = $locationResource->getLocationTags($locationId);

// Save location tags
$locationResource->saveLocationTags($locationId, [1, 2, 3]);
```

## API Usage Examples

### PHP API Client
```php
// Initialize API
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://localhost/rest/V1/dealerlocator/locations/nearby?latitude=40.7128&longitude=-74.0060&radius=10',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer YOUR_TOKEN'
    ]
]);
$response = curl_exec($curl);
$locations = json_decode($response, true);
```

### JavaScript/Fetch
```javascript
// Search nearby locations
fetch('/rest/V1/dealerlocator/locations/nearby?latitude=40.7128&longitude=-74.0060&radius=10', {
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    }
})
.then(response => response.json())
.then(data => console.log(data.items));
```

## Extension Attributes

### Adding Custom Extension Attributes
```xml
<!-- etc/extension_attributes.xml -->
<extension_attributes for="Zhik\DealerLocator\Api\Data\LocationInterface">
    <attribute code="custom_field" type="string"/>
    <attribute code="custom_array" type="string[]"/>
</extension_attributes>
```

### Plugin Implementation
```php
public function afterGetById(
    LocationRepositoryInterface $subject,
    LocationInterface $location
): LocationInterface {
    $extensionAttributes = $location->getExtensionAttributes();
    $extensionAttributes->setCustomField('value');
    $location->setExtensionAttributes($extensionAttributes);
    return $location;
}
```

## Event Observers

### Available Events
```xml
<!-- etc/events.xml -->
<event name="dealer_location_submit_before">
    <observer name="your_observer" instance="Your\Module\Observer\LocationSubmitBefore"/>
</event>
```

### Observer Implementation
```php
public function execute(\Magento\Framework\Event\Observer $observer)
{
    $location = $observer->getEvent()->getLocation();
    $customerId = $observer->getEvent()->getCustomerId();
    // Your logic here
}
```

## Admin Grid Customization

### Adding Custom Column
```xml
<!-- view/adminhtml/ui_component/dealerlocator_location_listing.xml -->
<column name="custom_field" class="Your\Module\Ui\Component\Listing\Column\CustomField">
    <settings>
        <filter>text</filter>
        <label translate="true">Custom Field</label>
    </settings>
</column>
```

## CLI Commands

### Testing Import/Export
```bash
# Test import
bin/magento import:data:validate --entity=dealerlocator_location --import-file=locations.csv

# Run import
bin/magento import:data:run --entity=dealerlocator_location --import-file=locations.csv

# Export locations
bin/magento export:data:run --entity=dealerlocator_location --file-format=csv
```

## Common Code Snippets

### Get Current Admin User
```php
$adminSession = $this->authSession;
$adminUser = $adminSession->getUser();
$adminUserId = $adminUser->getId();
```

### Send Email Notification
```php
$this->emailHelper->sendNewLocationNotification($location);
$this->emailHelper->sendApprovalNotification($location);
$this->emailHelper->sendRejectionNotification($location, $reason);
```

### Add Success/Error Messages
```php
$this->messageManager->addSuccessMessage(__('Location has been saved.'));
$this->messageManager->addErrorMessage(__('Unable to save location.'));
```

### Calculate Distance (Haversine)
```php
$distance = $this->locationSearch->calculateDistance(
    $lat1, $lon1,  // Point 1
    $lat2, $lon2   // Point 2
); // Returns distance in km
```

## ACL Resources

```xml
<!-- etc/acl.xml -->
Zhik_DealerLocator::dealer_locator
Zhik_DealerLocator::locations_view
Zhik_DealerLocator::locations_save
Zhik_DealerLocator::locations_delete
Zhik_DealerLocator::locations_approve
Zhik_DealerLocator::tags_view
Zhik_DealerLocator::tags_save
Zhik_DealerLocator::tags_delete
Zhik_DealerLocator::configuration
```

## Performance Tips

1. **Use Indexes**: All foreign keys and search fields are indexed
2. **Limit Results**: Always use pagination for large datasets
3. **Cache Results**: Implement full page cache for location pages
4. **Lazy Load**: Tags are loaded via extension attributes only when needed
5. **Batch Operations**: Use collections for bulk updates

## Testing

### Unit Test Example
```php
public function testApproveLocation()
{
    $locationId = 1;
    $adminUserId = 1;
    
    $location = $this->createMock(LocationInterface::class);
    $location->expects($this->once())
        ->method('setStatus')
        ->with('approved');
    
    $this->locationRepository->expects($this->once())
        ->method('getById')
        ->with($locationId)
        ->willReturn($location);
    
    $this->locationApproval->approve($locationId, $adminUserId);
}
```