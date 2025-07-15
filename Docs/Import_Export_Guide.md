# Dealer Locator Import/Export Guide

## Overview

The Dealer Locator module supports importing and exporting location data through Magento's standard import/export functionality.

## Accessing Import/Export

### From Admin Menu
1. Navigate to **Dealer Locator** in the admin menu
2. Click **Import Locations** to import new locations
3. Click **Export Locations** to export existing locations

### From System Menu
1. Navigate to **System > Data Transfer > Import** for importing
2. Navigate to **System > Data Transfer > Export** for exporting
3. Select "Dealer Locations" as the entity type

## Export Locations

### Steps to Export
1. Go to **System > Data Transfer > Export**
2. Select **Entity Type**: "Dealer Locations"
3. Select **Export File Format**: CSV (recommended) or XML
4. Configure any export filters if needed
5. Click **Continue** to generate the export file

### Exported Fields
- `location_id` - Unique location identifier
- `customer_id` - Customer ID who owns the location
- `customer_email` - Email of the customer
- `name` - Location name
- `address` - Street address
- `city` - City
- `state` - State/Province
- `postal_code` - ZIP/Postal code
- `country` - Country code (e.g., US, CA)
- `phone` - Phone number
- `email` - Location email
- `website` - Website URL
- `hours` - Business hours
- `description` - Location description
- `latitude` - Latitude coordinate
- `longitude` - Longitude coordinate
- `status` - Status (pending/approved/rejected)
- `is_active` - Active status (0 or 1)
- `tags` - Comma-separated tag names
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `approved_at` - Approval timestamp
- `approved_by` - Username of admin who approved
- `rejection_reason` - Reason for rejection if applicable

## Import Locations

### Steps to Import
1. Go to **System > Data Transfer > Import**
2. Select **Entity Type**: "Dealer Locations"
3. Select **Import Behavior**:
   - **Add/Update** - Add new locations and update existing ones
   - **Replace** - Replace existing locations with same ID
   - **Delete** - Delete locations by ID
4. Select your CSV file
5. Click **Check Data** to validate
6. Click **Import** to process the file

### Required Fields for Import
- `name` - Location name (required)
- `address` - Street address (required)
- `city` - City (required)
- `postal_code` - ZIP/Postal code (required)
- `country` - Country code (required)
- `phone` - Phone number (required)
- `email` - Location email (required)
- `customer_id` OR `customer_email` - One is required to identify the customer

### Optional Fields
- `location_id` - Only include for updates/deletes
- `state` - State/Province
- `website` - Website URL
- `hours` - Business hours
- `description` - Location description
- `latitude` - Latitude (-90 to 90)
- `longitude` - Longitude (-180 to 180)
- `status` - pending/approved/rejected (default: pending)
- `is_active` - 0 or 1 (default: 1)
- `tags` - Comma-separated tag names
- `approved_by` - Admin username (for pre-approved imports)

### Import File Format

#### CSV Format
```csv
location_id,customer_email,name,address,city,state,postal_code,country,phone,email,website,hours,description,latitude,longitude,status,is_active,tags,approved_by
,customer@example.com,"Bike World","123 Main Street","New York","NY","10001","US","555-1234","info@bikeworld.com","https://bikeworld.com","Mon-Fri 9am-6pm","Full service bike shop",40.7128,-74.0060,pending,1,"Authorized Dealer,Service Center",
```

#### Important Notes
- Use UTF-8 encoding for the CSV file
- Wrap values containing commas in double quotes
- Leave `location_id` empty for new locations
- Tags must match existing tag names (case-insensitive)
- Customer must exist in the system (by ID or email)
- Admin username must match an existing admin user

## Import Behaviors

### Add/Update
- Creates new locations if `location_id` is empty or doesn't exist
- Updates existing locations if `location_id` matches
- Preserves existing data for fields not included in import

### Replace
- Completely replaces existing location data
- All fields not in import file will be cleared
- Creates new locations if ID doesn't exist

### Delete
- Deletes locations matching the provided `location_id`
- Only `location_id` field is required for delete
- Other fields are ignored

## Validation Rules

### Customer Validation
- Customer must exist in the system
- Can identify by `customer_id` or `customer_email`
- Email takes precedence if both are provided

### Coordinate Validation
- Latitude: -90 to 90
- Longitude: -180 to 180
- Both are optional but recommended

### Status Validation
- Valid values: pending, approved, rejected
- Default: pending

### Tag Validation
- Tags must exist in the system
- Matching is case-insensitive
- Invalid tags are skipped (not imported)

## Sample Files

Sample import files are provided in:
`app/code/Zhik/DealerLocator/Samples/`

- `locations_import_sample.csv` - Example import file with all fields

## Troubleshooting

### Common Import Errors

1. **"Customer ID or Email Required"**
   - Ensure each row has either `customer_id` or `customer_email`

2. **"Invalid Customer Email"**
   - Customer with this email doesn't exist
   - Create the customer first or use a valid email

3. **"Invalid Latitude/Longitude"**
   - Check coordinate ranges
   - Ensure numeric format (no degree symbols)

4. **"Name is Required"**
   - Every location must have a name

5. **"Invalid Status"**
   - Use only: pending, approved, rejected

### Performance Tips

1. **Large Imports**
   - Split files larger than 5000 rows
   - Import in batches to avoid timeouts

2. **Optimization**
   - Remove unnecessary columns
   - Import during low-traffic periods

3. **Validation**
   - Always use "Check Data" before importing
   - Fix all errors before proceeding

## Best Practices

1. **Backup First**
   - Always export existing data before major imports
   - Test imports on staging environment

2. **Data Preparation**
   - Validate customer emails exist
   - Ensure tags are created before import
   - Use consistent formatting

3. **Incremental Updates**
   - Use Add/Update behavior for regular updates
   - Include only changed fields
   - Maintain location_id for updates

4. **Error Handling**
   - Review import results carefully
   - Check system.log for detailed errors
   - Re-import failed rows after fixes