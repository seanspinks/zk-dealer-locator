# Zhik Dealer Locator Module for Magento 2

## Overview

The Zhik Dealer Locator module provides a comprehensive dealer/location management system for Magento 2.4.8+. It allows customers to submit dealer locations, administrators to manage and approve them, and provides a robust API for integration.

## Features

### Customer Features
- Submit new dealer locations
- Manage their own submitted locations
- Search dealers by location, tags, and proximity
- View dealer details and contact information

### Admin Features
- Review and approve/reject dealer submissions
- Full CRUD operations on locations
- Tag management system
- Email notifications for submissions and status changes
- Comprehensive admin grids with filtering

### Technical Features
- RESTful API endpoints
- Extension attributes support
- Repository pattern implementation
- Service contracts
- Plugin system for extensibility
- Database indexing for performance

## Requirements

- Magento 2.4.8 or higher
- PHP 8.1 or higher
- MySQL 8.0 or MariaDB 10.6+

## Installation

### Via Composer (Recommended)

```bash
composer require zhik/module-dealer-locator
bin/magento module:enable Zhik_DealerLocator
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean
```

### Manual Installation

1. Create directory: `app/code/Zhik/DealerLocator`
2. Copy module files to the directory
3. Run installation commands:

```bash
bin/magento module:enable Zhik_DealerLocator
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean
```

## Configuration

### Admin Configuration

1. Navigate to **Stores > Configuration > Zhik > Dealer Locator**
2. Configure the following settings:
   - **Enable Module**: Yes/No
   - **Email Notifications**: Enable/disable email notifications
   - **Notification Recipients**: Admin emails for notifications
   - **Auto-approve Locations**: Automatically approve submissions
   - **Google Maps API Key**: For map functionality (optional)

### Email Configuration

1. Navigate to **Marketing > Communications > Email Templates**
2. The module provides these email templates:
   - **New Dealer Location Submission** - Sent to admins
   - **Dealer Location Approved** - Sent to customers
   - **Dealer Location Rejected** - Sent to customers

## Usage

### Admin Usage

#### Managing Locations

1. Navigate to **Dealer Locator > Manage Locations**
2. Use the grid to:
   - View all locations
   - Filter by status, customer, tags
   - Edit location details
   - Approve/Reject pending locations
   - Delete locations

#### Managing Tags

1. Navigate to **Dealer Locator > Manage Tags**
2. Create tags like:
   - Authorized Dealer
   - Service Center
   - Premium Partner
3. Assign tags to locations during edit

### Customer Usage

#### Submit Location

1. Customers must be logged in
2. Navigate to account dashboard
3. Click "Dealer Locations" or "Submit Location"
4. Fill out the location form
5. Submit for approval

#### Manage Locations

1. View submitted locations in account
2. Edit pending locations
3. View approval status
4. Delete own locations

## API Documentation

### REST API Endpoints

#### Public Endpoints
```
GET  /V1/dealerlocator/locations
GET  /V1/dealerlocator/locations/:id
GET  /V1/dealerlocator/locations/search
GET  /V1/dealerlocator/locations/nearby
GET  /V1/dealerlocator/tags
```

#### Customer Endpoints
```
GET    /V1/dealerlocator/mine/locations
POST   /V1/dealerlocator/mine/locations
PUT    /V1/dealerlocator/mine/locations/:id
DELETE /V1/dealerlocator/mine/locations/:id
```

#### Admin Endpoints
```
POST   /V1/dealerlocator/locations
PUT    /V1/dealerlocator/locations/:id
DELETE /V1/dealerlocator/locations/:id
POST   /V1/dealerlocator/locations/:id/approve
POST   /V1/dealerlocator/locations/:id/reject
POST   /V1/dealerlocator/tags
PUT    /V1/dealerlocator/tags/:id
DELETE /V1/dealerlocator/tags/:id
```

See API_Examples.md for detailed usage examples.

## Database Schema

### Tables

#### zhik_dealer_locations
- Primary storage for dealer locations
- Indexes on customer_id, status, coordinates

#### zhik_dealer_tags
- Tag definitions
- Unique index on name

#### zhik_dealer_location_tag
- Many-to-many relationship
- Composite primary key

## Development

### Extending the Module

#### Adding Custom Fields

1. Add database column via db_schema.xml
2. Update model interfaces
3. Add to admin forms
4. Update API interfaces

#### Creating Plugins

```php
<type name="Zhik\DealerLocator\Api\LocationRepositoryInterface">
    <plugin name="your_plugin_name" 
            type="Your\Module\Plugin\LocationPlugin"/>
</type>
```

#### Events

The module dispatches these events:
- `dealer_location_submit_before`
- `dealer_location_submit_after`
- `dealer_location_approve_after`
- `dealer_location_reject_after`

## Troubleshooting

### Common Issues

1. **Module not appearing in admin**
   - Clear cache: `bin/magento cache:clean`
   - Check module status: `bin/magento module:status`

2. **Emails not sending**
   - Check email configuration in Stores > Configuration
   - Verify cron is running
   - Check email logs

3. **API errors**
   - Check authentication token
   - Verify permissions/ACL
   - Check request format

### Debug Mode

Enable debug logging:
```php
// In di.xml
<preference for="Psr\Log\LoggerInterface" 
            type="Magento\Framework\Logger\Monolog"/>
```

## Support

For issues and feature requests:
- GitHub: [github.com/zhik/dealer-locator](https://github.com/zhik/dealer-locator)
- Email: support@zhik.com

## License

Proprietary - See COPYING.txt for details

## Version History

### 1.2.0
- REST API implementation
- Email notifications

### 1.1.0
- Tag management system
- Admin approval workflow

### 1.0.0
- Initial release
- Basic location management