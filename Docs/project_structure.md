# Project Structure

## Root Directory
```
magento-local/src/app/code/Zhik/DealerLocator/
├── Api/
│   ├── Data/
│   │   ├── LocationInterface.php
│   │   ├── LocationSearchResultsInterface.php
│   │   ├── TagInterface.php
│   │   └── TagSearchResultsInterface.php
│   ├── LocationRepositoryInterface.php
│   ├── TagRepositoryInterface.php
│   └── LocationManagementInterface.php
├── Block/
│   ├── Adminhtml/
│   │   ├── Location/
│   │   │   ├── Edit/
│   │   │   │   ├── Form.php
│   │   │   │   └── Tabs.php
│   │   │   └── Grid.php
│   │   └── Tag/
│   │       ├── Edit/
│   │       │   └── Form.php
│   │       └── Grid.php
│   └── Customer/
│       ├── Account/
│       │   ├── Locations.php
│       │   └── Location/
│       │       ├── Edit.php
│       │       └── View.php
│       └── Location/
│           └── Form.php
├── Controller/
│   ├── Adminhtml/
│   │   ├── Location/
│   │   │   ├── Index.php
│   │   │   ├── Edit.php
│   │   │   ├── Save.php
│   │   │   ├── Delete.php
│   │   │   ├── MassApprove.php
│   │   │   ├── MassReject.php
│   │   │   ├── MassDelete.php
│   │   │   ├── Import.php
│   │   │   └── Export.php
│   │   └── Tag/
│   │       ├── Index.php
│   │       ├── Edit.php
│   │       ├── Save.php
│   │       └── Delete.php
│   ├── Customer/
│   │   └── Location/
│   │       ├── Index.php
│   │       ├── Add.php
│   │       ├── Edit.php
│   │       ├── Save.php
│   │       ├── Delete.php
│   │       └── View.php
│   └── Maps/
│       ├── Autocomplete.php
│       ├── Geocode.php
│       └── ReverseGeocode.php
├── Cron/
│   └── PendingNotification.php
├── Helper/
│   ├── Data.php
│   ├── Email.php
│   └── Maps.php
├── Model/
│   ├── Location.php
│   ├── Tag.php
│   ├── LocationRepository.php
│   ├── TagRepository.php
│   ├── LocationManagement.php
│   ├── ResourceModel/
│   │   ├── Location.php
│   │   ├── Tag.php
│   │   ├── Location/
│   │   │   ├── Collection.php
│   │   │   └── Grid/
│   │   │       └── Collection.php
│   │   └── Tag/
│   │       └── Collection.php
│   ├── Source/
│   │   ├── Status.php
│   │   └── Countries.php
│   └── Import/
│       └── Location.php
├── Observer/
│   └── CustomerLogin.php
├── Plugin/
│   └── CustomerData.php
├── Setup/
│   ├── InstallSchema.php
│   ├── InstallData.php
│   ├── UpgradeSchema.php
│   ├── UpgradeData.php
│   └── Patch/
│       └── Data/
│           └── InitialTagData.php
├── Ui/
│   ├── Component/
│   │   ├── Listing/
│   │   │   ├── Column/
│   │   │   │   ├── LocationActions.php
│   │   │   │   ├── TagActions.php
│   │   │   │   └── Status.php
│   │   │   └── DataProvider.php
│   │   └── Form/
│   │       └── DataProvider.php
│   └── DataProvider/
│       ├── Location/
│       │   └── ListingDataProvider.php
│       └── Tag/
│           └── ListingDataProvider.php
├── etc/
│   ├── module.xml
│   ├── di.xml
│   ├── db_schema.xml
│   ├── db_schema_whitelist.json
│   ├── acl.xml
│   ├── crontab.xml
│   ├── email_templates.xml
│   ├── events.xml
│   ├── routes.xml
│   ├── adminhtml/
│   │   ├── menu.xml
│   │   ├── routes.xml
│   │   └── system.xml
│   ├── frontend/
│   │   ├── routes.xml
│   │   ├── sections.xml
│   │   └── events.xml
│   └── webapi.xml
├── i18n/
│   └── en_US.csv
├── view/
│   ├── adminhtml/
│   │   ├── layout/
│   │   │   ├── dealerlocator_location_index.xml
│   │   │   ├── dealerlocator_location_edit.xml
│   │   │   ├── dealerlocator_tag_index.xml
│   │   │   └── dealerlocator_tag_edit.xml
│   │   ├── templates/
│   │   │   ├── location/
│   │   │   │   ├── edit.phtml
│   │   │   │   └── import.phtml
│   │   │   └── tag/
│   │   │       └── edit.phtml
│   │   ├── ui_component/
│   │   │   ├── dealerlocator_location_listing.xml
│   │   │   ├── dealerlocator_location_form.xml
│   │   │   ├── dealerlocator_tag_listing.xml
│   │   │   └── dealerlocator_tag_form.xml
│   │   └── web/
│   │       ├── css/
│   │       │   └── source/
│   │       │       └── _module.less
│   │       └── js/
│   │           ├── location/
│   │           │   └── edit.js
│   │           └── maps/
│   │               └── admin-autocomplete.js
│   ├── frontend/
│   │   ├── layout/
│   │   │   ├── customer_account.xml
│   │   │   ├── dealerlocator_customer_location_index.xml
│   │   │   ├── dealerlocator_customer_location_add.xml
│   │   │   ├── dealerlocator_customer_location_edit.xml
│   │   │   └── dealerlocator_customer_location_view.xml
│   │   ├── templates/
│   │   │   ├── customer/
│   │   │   │   ├── location/
│   │   │   │   │   ├── list.phtml
│   │   │   │   │   ├── form.phtml
│   │   │   │   │   └── view.phtml
│   │   │   │   └── account/
│   │   │   │       └── link.phtml
│   │   │   └── location/
│   │   │       └── form/
│   │   │           └── renderer/
│   │   │               └── tags.phtml
│   │   ├── email/
│   │   │   ├── submission_confirmation.html
│   │   │   ├── location_approved.html
│   │   │   ├── location_rejected.html
│   │   │   └── admin_new_submission.html
│   │   └── web/
│   │       ├── css/
│   │       │   └── source/
│   │       │       ├── _module.less
│   │       │       └── _location-form.less
│   │       ├── js/
│   │       │   ├── location/
│   │       │   │   ├── form.js
│   │       │   │   └── list.js
│   │       │   └── maps/
│   │       │       ├── autocomplete.js
│   │       │       └── geocoder.js
│   │       └── template/
│   │           └── location/
│   │               └── form.html
│   └── base/
│       └── requirejs-config.js
├── Test/
│   ├── Unit/
│   │   ├── Model/
│   │   │   ├── LocationTest.php
│   │   │   ├── TagTest.php
│   │   │   └── LocationRepositoryTest.php
│   │   └── Helper/
│   │       └── DataTest.php
│   ├── Integration/
│   │   └── Model/
│   │       └── LocationRepositoryTest.php
│   └── Mftf/
│       └── Test/
│           └── CustomerLocationManagementTest.xml
├── composer.json
└── registration.php
```

## Detailed Structure

### API Directory (`Api/`)
Contains all service contracts and interfaces following Magento 2 best practices:
- **Data interfaces** define the data structure for locations and tags
- **Repository interfaces** define CRUD operations
- **Management interfaces** define business logic operations

### Block Directory (`Block/`)
Contains PHP classes for rendering logic:
- **Adminhtml blocks** for backend interfaces
- **Customer blocks** for frontend customer account pages
- Follows Magento's MVC pattern

### Controller Directory (`Controller/`)
Action controllers for handling HTTP requests:
- **Adminhtml controllers** for admin panel actions
- **Customer controllers** for customer account actions
- **Maps controllers** for Google Maps API proxy endpoints

### Model Directory (`Model/`)
Business logic and data persistence:
- **Entity models** (Location, Tag)
- **Repository implementations** following repository pattern
- **Resource models** for database operations
- **Collections** for handling multiple entities
- **Source models** for configuration options

### Setup Directory (`Setup/`)
Database installation and upgrade scripts:
- **Declarative schema** (db_schema.xml) for table structure
- **Data patches** for initial data
- Following Magento 2.4 best practices

### UI Component Directory (`Ui/`)
Modern Magento 2 UI components:
- **Data providers** for grids and forms
- **UI component XML** definitions
- Leverages Magento's UI framework

### View Directory (`view/`)
Frontend assets and templates:
- **Layout XML** files for page structure
- **Templates** (.phtml) for HTML rendering
- **Web assets** (JS, CSS, images)
- **Email templates** for notifications
- **RequireJS** configuration

### Configuration Directory (`etc/`)
Module configuration files:
- `module.xml` - Module declaration
- `di.xml` - Dependency injection configuration
- `db_schema.xml` - Database structure
- `routes.xml` - URL routing
- `acl.xml` - Access control permissions
- `webapi.xml` - REST API endpoints

### Test Directory (`Test/`)
Automated tests:
- **Unit tests** for isolated component testing
- **Integration tests** for system integration
- **MFTF tests** for functional testing

## File Naming Conventions
- **PHP Classes**: PascalCase (e.g., `LocationRepository.php`)
- **XML Files**: lowercase with underscores (e.g., `db_schema.xml`)
- **Templates**: lowercase with underscores (e.g., `location_form.phtml`)
- **JavaScript**: lowercase with hyphens (e.g., `location-form.js`)
- **CSS/LESS**: lowercase with hyphens (e.g., `location-form.less`)

## Module Configuration
- **Vendor Name**: Zhik
- **Module Name**: DealerLocator
- **Composer Package**: zhik/module-dealer-locator
- **Module Version**: 1.0.0

## Environment-Specific Configurations
- Development: Enable developer mode, disable caching
- Staging: Production mode with debugging enabled
- Production: Full optimization, caching enabled, logging minimized

## Build and Deployment Structure
```
deployment/
├── composer.json
├── bin/
│   └── deploy.sh
├── config/
│   ├── env.php.dist
│   └── config.php.dist
└── scripts/
    ├── pre-deploy.sh
    └── post-deploy.sh
```

This structure follows Magento 2 best practices and coding standards, ensuring maintainability, scalability, and compatibility with the Magento ecosystem.