# Admin Guide - Zhik Dealer Locator

Complete guide for administrators to manage dealer locations, configure settings, and handle customer submissions.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Managing Locations](#managing-locations)
3. [Approval Workflow](#approval-workflow)
4. [Tag Management](#tag-management)
5. [Bulk Operations](#bulk-operations)
6. [Email Notifications](#email-notifications)
7. [Reports and Analytics](#reports-and-analytics)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

## Getting Started

### Accessing the Module

After installation, the Dealer Locator module can be accessed from:
- **Main Menu**: Marketing > Dealer Locator
- **Submenus**:
  - Manage Locations
  - Manage Tags
  - Configuration (redirects to Stores > Configuration)

### Required Permissions

Ensure admin users have the following ACL permissions:
- `Zhik_DealerLocator::locations` - View locations
- `Zhik_DealerLocator::locations_save` - Create/edit locations
- `Zhik_DealerLocator::locations_delete` - Delete locations
- `Zhik_DealerLocator::tags` - Manage tags
- `Zhik_DealerLocator::config` - Module configuration

To assign permissions:
1. Go to System > Permissions > User Roles
2. Select or create a role
3. In Resources, find "Dealer Locator" under Marketing
4. Select appropriate permissions
5. Save the role

## Managing Locations

### Location Grid Overview

The location management grid displays all dealer locations with the following columns:

| Column | Description | Sortable | Filterable |
|--------|-------------|----------|------------|
| ID | Unique identifier | Yes | Yes |
| Name | Location name | Yes | Yes |
| Address | Full address | Yes | Yes |
| City | City name | Yes | Yes |
| State | State/Province | Yes | Yes |
| Country | Country code | Yes | Yes |
| Status | Current status | Yes | Yes |
| Customer | Submitter name | Yes | Yes |
| Created | Submission date | Yes | Yes |
| Actions | Available actions | No | No |

### Location Statuses

1. **Pending** 🟡
   - New submissions awaiting review
   - Default status for customer submissions
   - Visible only in admin and customer account

2. **Approved** ✅
   - Reviewed and approved locations
   - Visible on public map
   - Can be edited by admin

3. **Rejected** ❌
   - Declined submissions
   - Visible only to submitter
   - Can be edited and resubmitted

4. **Pending Deletion** 🗑️
   - Approved locations marked for deletion
   - Remain visible until admin approval
   - Can be restored by rejecting deletion

### Adding a New Location

1. Click "Add New Location" button
2. Fill in required fields:
   - **Name**: Business name
   - **Address**: Street address
   - **City, State, Postal Code, Country**: Location details
3. Optional fields (if enabled):
   - Phone, Email, Website
   - Hours of Operation
   - Description
   - Tags
4. Set Status (defaults to Approved for admin-created)
5. Save the location

### Editing Locations

1. Click on any location row or "Edit" action
2. Modify fields as needed
3. Important considerations:
   - Changing address triggers re-geocoding
   - Status changes trigger email notifications
   - Edit history is tracked via parent_id

### Location Details

Each location edit page contains:

#### General Information Tab
- Basic details (name, address)
- Contact information
- Business hours
- Description

#### Map Preview Tab
- Visual confirmation of location
- Coordinate adjustment if needed
- Geocoding status

#### Tags Tab
- Assign/remove tags
- Create new tags inline

#### Customer Information Tab
- Submitter details
- Submission IP address
- Submission timestamp
- Version history

## Approval Workflow

### Standard Workflow

1. **Customer Submission**
   - Customer submits via frontend form
   - Status: Pending
   - Admin notification sent

2. **Admin Review**
   - Review in admin grid
   - Check for duplicates
   - Verify information accuracy

3. **Decision**
   - **Approve**: Location goes live
   - **Reject**: Provide reason (optional)
   - **Request Info**: Email customer

### Reviewing Pending Locations

1. **Filter by Status**
   - Use grid filter: Status = Pending
   - Sort by created date (oldest first)

2. **Quick Review**
   - Use column preview for basic info
   - Click "View on Map" for location check

3. **Detailed Review**
   - Click "Edit" for full details
   - Verify all information
   - Check for existing duplicates

### Approval Best Practices

✅ **Do:**
- Verify address accuracy
- Check for duplicate listings
- Ensure appropriate tags
- Review business legitimacy

❌ **Don't:**
- Approve without verification
- Ignore suspicious submissions
- Skip coordinate validation

### Handling Rejections

When rejecting a location:
1. Select "Rejected" status
2. Add internal notes (optional)
3. Email is sent to submitter
4. Customer can edit and resubmit

### Deletion Requests

For pending deletion requests:
1. Filter by Status = "Pending Deletion"
2. Review deletion reason (if provided)
3. Options:
   - **Approve Deletion**: Permanently remove
   - **Reject Deletion**: Restore to approved

## Tag Management

### Understanding Tags

Tags help categorize and filter locations:
- **Purpose**: Enable filtered searches
- **Display**: Shown on map markers
- **Assignment**: Multiple tags per location

### Creating Tags

1. Go to Marketing > Dealer Locator > Manage Tags
2. Click "Add New Tag"
3. Enter:
   - **Name**: Display name
   - **Code**: Unique identifier (auto-generated)
   - **Color**: Hex color for map markers
   - **Icon**: Optional icon class
   - **Sort Order**: Display order
4. Save tag

### Tag Examples

Common tag categories:
- **Service Types**: Sales, Service, Parts
- **Certifications**: Authorized, Premium, Elite
- **Features**: Wheelchair Accessible, Parking, 24/7
- **Products**: Specific product lines

### Managing Tag Assignments

#### Individual Assignment
1. Edit location
2. Go to Tags tab
3. Select/deselect tags
4. Save

#### Bulk Assignment
1. Select multiple locations in grid
2. Actions > "Update Tags"
3. Choose tags to add/remove
4. Submit

### Tag Strategy

Effective tag usage:
- Keep tags consistent and clear
- Limit to 5-10 active tags
- Use colors meaningfully
- Regular cleanup of unused tags

## Bulk Operations

### Available Mass Actions

From the location grid, select multiple items:

1. **Mass Delete**
   - Permanently removes selected
   - Confirmation required
   - No email notifications

2. **Mass Approve**
   - Approves pending locations
   - Sends approval emails
   - Updates status and timestamp

3. **Mass Reject**
   - Rejects selected locations
   - Sends rejection emails
   - Keeps in database

4. **Mass Update Status**
   - Change status in bulk
   - Applies validation rules
   - Triggers notifications

5. **Mass Approve Deletion**
   - Approves pending deletions
   - Permanently removes locations
   - Clears from map

6. **Mass Reject Deletion**
   - Restores to approved status
   - Cancels deletion request
   - Notifies requestor

### Bulk Import

For initial data or large updates:

1. **Prepare CSV File**
   ```csv
   name,street,city,state,postal_code,country,phone,email,website,tags
   "Store 1","123 Main St","New York","NY","10001","US","555-1234","store1@example.com","www.store1.com","sales,service"
   ```

2. **Import Process**
   - Use Data Transfer > Import
   - Entity Type: Dealer Locations
   - Follow standard Magento import

3. **Validation**
   - Check import results
   - Verify geocoding completed
   - Review on map

### Bulk Export

Export locations for analysis:
1. From grid, configure filters
2. Click "Export" button
3. Choose format (CSV, XML)
4. Download file

## Email Notifications

### Notification Types

1. **Admin Notifications**
   - New submission alerts
   - Deletion requests
   - System errors

2. **Customer Notifications**
   - Submission confirmation
   - Approval notification
   - Rejection notification
   - Status updates

### Managing Templates

Email templates are located in:
- Marketing > Communications > Email Templates
- Filter by "dealer" to find module templates

To customize:
1. Load default template
2. Click "Load Template"
3. Modify content
4. Save as new template
5. Update configuration to use new template

### Template Variables

Available in all dealer locator emails:
```
{{var location.getName()}}
{{var location.getFullAddress()}}
{{var location.getStatus()}}
{{var customer.name}}
{{var store.frontend_name}}
{{var approval_url}}
{{var rejection_reason}}
```

### Email Configuration

Configure in Stores > Configuration > Zhik > Dealer Locator:
- Enable/disable notifications
- Set sender identity
- Choose templates
- Add CC recipients

## Reports and Analytics

### Built-in Metrics

Monitor module usage via:

1. **Admin Dashboard Widget** (if enabled)
   - Pending locations count
   - Recent submissions
   - Approval rate

2. **Grid Statistics**
   - Total locations by status
   - Submissions by date
   - Customer activity

### Custom Reports

Create custom reports using:
1. Direct database queries
2. Magento BI (if available)
3. Export data for external analysis

Key metrics to track:
- Submission volume over time
- Approval/rejection rates
- Geographic distribution
- Customer engagement
- Tag usage statistics

### Performance Monitoring

Monitor module performance:
- Page load times
- Geocoding API usage
- Database query performance
- Cache hit rates

## Troubleshooting

### Common Issues

#### Locations Not Appearing on Map
1. Check status (must be Approved)
2. Verify coordinates are set
3. Clear cache
4. Check browser console for JS errors

#### Geocoding Failures
1. Verify API key is valid
2. Check API quota limits
3. Review address format
4. Manual coordinate entry

#### Email Not Sending
1. Test Magento email configuration
2. Verify templates are assigned
3. Check email logs
4. Confirm customer email addresses

#### Performance Issues
1. Enable caching
2. Optimize database indexes
3. Reduce map markers with clustering
4. Check API rate limits

### Debug Mode

Enable logging for troubleshooting:
```php
// In app/etc/env.php
'system' => [
    'default' => [
        'dev' => [
            'debug' => [
                'debug_logging' => 1,
            ],
        ],
    ],
],
```

Check logs in:
- `var/log/system.log`
- `var/log/exception.log`
- `var/log/debug.log`

## Best Practices

### Daily Operations

1. **Morning Review**
   - Check pending submissions
   - Review overnight submissions
   - Address any errors

2. **Regular Maintenance**
   - Weekly tag cleanup
   - Monthly duplicate check
   - Quarterly data validation

### Data Quality

Maintain high-quality data:
- Standardize naming conventions
- Consistent address formats
- Regular duplicate removal
- Update outdated information

### Security

Protect location data:
- Regular admin permission audits
- Monitor suspicious submissions
- Implement rate limiting
- Regular backups

### Customer Experience

Improve submission process:
- Clear submission guidelines
- Fast approval turnaround
- Helpful rejection messages
- Regular communication

### Scalability

Plan for growth:
- Implement marker clustering early
- Optimize database queries
- Monitor API usage
- Plan for multi-region deployment

---

For technical details, see the [Developer Guide](DEVELOPER-GUIDE.md). For configuration options, see the [Configuration Guide](CONFIGURATION.md).