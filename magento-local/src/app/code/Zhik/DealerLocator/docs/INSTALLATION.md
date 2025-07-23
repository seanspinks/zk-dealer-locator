# Installation Guide - Zhik Dealer Locator

This guide provides detailed instructions for installing and setting up the Zhik Dealer Locator module.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation Methods](#installation-methods)
3. [Post-Installation Setup](#post-installation-setup)
4. [Google Maps Configuration](#google-maps-configuration)
5. [Verification](#verification)
6. [Troubleshooting](#troubleshooting)
7. [Uninstallation](#uninstallation)

## Prerequisites

### System Requirements
- **Magento Version**: 2.4.x (Community or Enterprise)
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.x
- **Composer**: Latest version

### Required PHP Extensions
- ext-json
- ext-curl
- ext-gd or ext-imagick (for image processing)

### Google Maps Requirements
- Google Cloud Platform account
- Billing enabled on your GCP account
- API Key with the following APIs enabled:
  - Maps JavaScript API
  - Geocoding API
  - Places API (optional, for enhanced search)

## Installation Methods

### Method 1: Manual Installation

1. **Download the Module**
   ```bash
   # Download or clone the module to a temporary location
   git clone [repository-url] /tmp/zhik-dealerlocator
   ```

2. **Copy to Magento**
   ```bash
   # Create directory structure if it doesn't exist
   mkdir -p app/code/Zhik
   
   # Copy module files
   cp -r /tmp/zhik-dealerlocator/DealerLocator app/code/Zhik/
   ```

3. **Set Permissions**
   ```bash
   # Set proper ownership (adjust user:group as needed)
   chown -R www-data:www-data app/code/Zhik/DealerLocator
   
   # Set proper permissions
   find app/code/Zhik/DealerLocator -type d -exec chmod 755 {} \;
   find app/code/Zhik/DealerLocator -type f -exec chmod 644 {} \;
   ```

### Method 2: Composer Installation (if available)

```bash
# Add repository (if private)
composer config repositories.zhik-dealerlocator vcs [repository-url]

# Require the module
composer require zhik/module-dealerlocator

# Update dependencies
composer update
```

### Method 3: Archive Installation

1. **Extract Archive**
   ```bash
   # Extract to Magento root
   tar -xzf zhik-dealerlocator.tar.gz -C app/code/
   ```

## Post-Installation Setup

### 1. Enable the Module

```bash
# Check module status
bin/magento module:status

# Enable the module
bin/magento module:enable Zhik_DealerLocator

# You should see:
# The following modules have been enabled:
# - Zhik_DealerLocator
```

### 2. Run Magento Setup

```bash
# Run database setup
bin/magento setup:upgrade

# Compile dependency injection (production mode)
bin/magento setup:di:compile

# Deploy static content (production mode)
bin/magento setup:static-content:deploy -f

# Reindex if necessary
bin/magento indexer:reindex

# Clear all caches
bin/magento cache:clean
bin/magento cache:flush
```

### 3. Verify Installation

```bash
# Check if module is active
bin/magento module:status | grep Zhik_DealerLocator

# Check database tables were created
mysql -u[username] -p[password] [database_name] -e "SHOW TABLES LIKE '%dealer_location%';"
```

Expected tables:
- `dealer_location`
- `dealer_location_tag`
- `dealer_location_tag_relation`

## Google Maps Configuration

### 1. Obtain Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable billing for the project
4. Navigate to "APIs & Services" > "Library"
5. Enable the following APIs:
   - Maps JavaScript API
   - Geocoding API
   - Places API (optional)
6. Go to "APIs & Services" > "Credentials"
7. Click "Create Credentials" > "API Key"
8. Restrict the API key:
   - Application restrictions: HTTP referrers
   - Add your domain(s): `https://yourdomain.com/*`
   - API restrictions: Select the APIs you enabled

### 2. Configure in Magento Admin

1. Log in to Magento Admin Panel
2. Navigate to **Stores > Configuration > Zhik > Dealer Locator**
3. Expand "Google Maps Configuration"
4. Enter your API Key
5. Configure additional options:
   - **Default Latitude**: Starting map center latitude
   - **Default Longitude**: Starting map center longitude
   - **Default Zoom**: Initial zoom level (1-20)
   - **Map Style**: Optional custom map styling JSON

### 3. Configure Module Settings

#### General Settings
- **Enable Module**: Yes/No
- **Show Search Box**: Enable location search
- **Enable Clustering**: Group nearby markers
- **Cluster Minimum**: Minimum markers to create cluster

#### Field Visibility
Configure which fields are visible/required:
- Phone Number
- Website URL
- Email
- Hours of Operation
- Description

#### Customer Settings
- **Require Login**: Require customer login to submit
- **Auto-Approve**: Automatically approve submissions (not recommended)
- **Submission Limit**: Max locations per customer

#### Email Notifications
- **Enable Notifications**: Send email updates
- **Admin Email**: Where to send admin notifications
- **Email Template Selection**: Choose templates for each notification type

## Verification

### 1. Frontend Verification

1. **Check Map Display**
   - Navigate to the dealer locator page
   - Verify map loads without errors
   - Check browser console for JavaScript errors

2. **Test Search Functionality**
   - Try searching for locations
   - Test proximity search
   - Verify tag filtering works

3. **Customer Submission**
   - Log in as a customer
   - Navigate to My Account > My Locations
   - Try submitting a new location
   - Verify form validation works

### 2. Admin Verification

1. **Access Admin Grid**
   - Go to Marketing > Dealer Locator > Manage Locations
   - Verify grid loads with proper columns
   - Test sorting and filtering

2. **Test Approval Workflow**
   - Find a pending location
   - Test approve/reject actions
   - Verify email notifications are sent

3. **Tag Management**
   - Go to Marketing > Dealer Locator > Manage Tags
   - Create, edit, and delete tags
   - Assign tags to locations

### 3. API Verification

Test REST API endpoints:
```bash
# Get all locations
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?searchCriteria[page_size]=10"

# Search nearby locations
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search/nearby?latitude=40.7128&longitude=-74.0060&radius=50"
```

## Troubleshooting

### Common Issues

#### 1. Module Not Showing in Admin

**Problem**: Module menu items don't appear
**Solutions**:
- Clear cache: `bin/magento cache:clean config`
- Check ACL permissions for admin user
- Verify module is enabled: `bin/magento module:status`
- Recompile: `bin/magento setup:di:compile`

#### 2. Map Not Loading

**Problem**: Map shows gray box or error
**Solutions**:
- Check browser console for errors
- Verify API key is correct and has billing enabled
- Check API key restrictions match your domain
- Ensure required APIs are enabled in Google Cloud Console

#### 3. Database Errors

**Problem**: SQL errors during installation
**Solutions**:
- Check MySQL version compatibility
- Verify database user has CREATE/ALTER permissions
- Check for conflicting table names
- Review `var/log/system.log` for detailed errors

#### 4. Permission Issues

**Problem**: Cannot save locations or access features
**Solutions**:
```bash
# Fix file permissions
find app/code/Zhik/DealerLocator -type d -exec chmod 755 {} \;
find app/code/Zhik/DealerLocator -type f -exec chmod 644 {} \;

# Fix ownership
chown -R www-data:www-data app/code/Zhik/DealerLocator

# Clear generated files
rm -rf generated/code/Zhik
rm -rf var/cache/*
```

#### 5. Email Notifications Not Sending

**Problem**: Emails not being delivered
**Solutions**:
- Verify Magento email configuration
- Check email templates are assigned in configuration
- Review mail logs
- Test with Magento's email test functionality

### Debug Mode

Enable debug logging for troubleshooting:
```php
// In any model or controller
$this->_logger->debug('Dealer Locator Debug', ['data' => $debugData]);
```

Check logs in:
- `var/log/system.log`
- `var/log/exception.log`
- `var/log/debug.log`

## Uninstallation

### Complete Removal

1. **Disable the Module**
   ```bash
   bin/magento module:disable Zhik_DealerLocator
   ```

2. **Remove Module Files**
   ```bash
   rm -rf app/code/Zhik/DealerLocator
   ```

3. **Remove Database Tables** (Optional)
   ```sql
   DROP TABLE IF EXISTS `dealer_location_tag_relation`;
   DROP TABLE IF EXISTS `dealer_location_tag`;
   DROP TABLE IF EXISTS `dealer_location`;
   ```

4. **Clean Up**
   ```bash
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

5. **Remove Configuration** (Optional)
   ```sql
   DELETE FROM `core_config_data` WHERE `path` LIKE 'zhik_dealerlocator/%';
   ```

### Temporary Disable

To temporarily disable without removing:
```bash
bin/magento module:disable Zhik_DealerLocator
bin/magento cache:flush
```

Re-enable later with:
```bash
bin/magento module:enable Zhik_DealerLocator
bin/magento setup:upgrade
```

---

For additional support or questions, please refer to the main [README.md](README.md) or contact the module maintainer.