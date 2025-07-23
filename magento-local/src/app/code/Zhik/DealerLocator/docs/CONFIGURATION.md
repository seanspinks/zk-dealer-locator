# Configuration Guide - Zhik Dealer Locator

This guide covers all configuration options available in the Zhik Dealer Locator module.

## Table of Contents

1. [Accessing Configuration](#accessing-configuration)
2. [General Settings](#general-settings)
3. [Google Maps Configuration](#google-maps-configuration)
4. [Field Configuration](#field-configuration)
5. [Customer Settings](#customer-settings)
6. [Email Notifications](#email-notifications)
7. [Advanced Settings](#advanced-settings)
8. [Store View Configuration](#store-view-configuration)
9. [Configuration via Code](#configuration-via-code)

## Accessing Configuration

### Admin Panel Access
1. Log in to Magento Admin Panel
2. Navigate to **Stores > Configuration**
3. In the left panel, find **Zhik > Dealer Locator**

### Configuration Scope
- **Default Config**: Applies to all store views
- **Website Level**: Override for specific websites
- **Store View Level**: Override for specific store views

Use the "Store View" selector at the top to change scope.

## General Settings

### Enable Module
- **Path**: `zhik_dealerlocator/general/enabled`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Master switch to enable/disable the entire module

### Frontend URL Key
- **Path**: `zhik_dealerlocator/general/url_key`
- **Type**: Text
- **Default**: `dealer-locator`
- **Description**: URL path for the dealer locator page (e.g., `yoursite.com/dealer-locator`)

### Page Title
- **Path**: `zhik_dealerlocator/general/page_title`
- **Type**: Text
- **Default**: `Find a Dealer`
- **Description**: Title displayed on the dealer locator page

### Meta Description
- **Path**: `zhik_dealerlocator/general/meta_description`
- **Type**: Textarea
- **Default**: `Find authorized dealers and locations near you`
- **Description**: SEO meta description for the dealer locator page

## Google Maps Configuration

### API Key (Required)
- **Path**: `zhik_dealerlocator/google_maps/api_key`
- **Type**: Obscure (encrypted)
- **Description**: Your Google Maps API key with proper APIs enabled

### Map Center - Latitude
- **Path**: `zhik_dealerlocator/google_maps/default_latitude`
- **Type**: Text
- **Default**: `40.7128`
- **Description**: Default map center latitude (e.g., New York City)

### Map Center - Longitude
- **Path**: `zhik_dealerlocator/google_maps/default_longitude`
- **Type**: Text
- **Default**: `-74.0060`
- **Description**: Default map center longitude

### Default Zoom Level
- **Path**: `zhik_dealerlocator/google_maps/default_zoom`
- **Type**: Select
- **Options**: 1-20
- **Default**: 10
- **Description**: Initial map zoom level (1=World, 20=Building)

### Map Height
- **Path**: `zhik_dealerlocator/google_maps/map_height`
- **Type**: Text
- **Default**: `600px`
- **Description**: Height of the map container (px, %, vh)

### Enable Search Box
- **Path**: `zhik_dealerlocator/google_maps/show_search`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Show search box on the map

### Enable User Location
- **Path**: `zhik_dealerlocator/google_maps/show_user_location`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Show "Use My Location" button

### Map Style (Advanced)
- **Path**: `zhik_dealerlocator/google_maps/map_style`
- **Type**: Textarea
- **Description**: Custom map styling JSON (use [Snazzy Maps](https://snazzymaps.com/) for examples)

Example:
```json
[
  {
    "featureType": "water",
    "elementType": "geometry",
    "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}]
  }
]
```

### Marker Clustering
- **Path**: `zhik_dealerlocator/google_maps/enable_clustering`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Group nearby markers into clusters

### Cluster Minimum Size
- **Path**: `zhik_dealerlocator/google_maps/cluster_minimum`
- **Type**: Text
- **Default**: 3
- **Description**: Minimum markers required to form a cluster

### Info Window Template
- **Path**: `zhik_dealerlocator/google_maps/info_window_template`
- **Type**: Select
- **Options**: Default, Compact, Detailed
- **Default**: Default
- **Description**: Template for marker info windows

## Field Configuration

### Required Fields

#### Name Field
- **Required**: Always (cannot be disabled)

#### Address Fields
- **Street Required**: `zhik_dealerlocator/fields/street_required` (Default: Yes)
- **City Required**: `zhik_dealerlocator/fields/city_required` (Default: Yes)
- **State Required**: `zhik_dealerlocator/fields/state_required` (Default: Yes)
- **Postal Code Required**: `zhik_dealerlocator/fields/postal_required` (Default: Yes)
- **Country Required**: `zhik_dealerlocator/fields/country_required` (Default: Yes)

### Optional Fields

#### Phone Number
- **Show Field**: `zhik_dealerlocator/fields/show_phone`
- **Required**: `zhik_dealerlocator/fields/phone_required`
- **Default**: Show: Yes, Required: No

#### Email
- **Show Field**: `zhik_dealerlocator/fields/show_email`
- **Required**: `zhik_dealerlocator/fields/email_required`
- **Default**: Show: Yes, Required: No

#### Website URL
- **Show Field**: `zhik_dealerlocator/fields/show_website`
- **Required**: `zhik_dealerlocator/fields/website_required`
- **Default**: Show: Yes, Required: No

#### Hours of Operation
- **Show Field**: `zhik_dealerlocator/fields/show_hours`
- **Required**: `zhik_dealerlocator/fields/hours_required`
- **Default**: Show: Yes, Required: No

#### Description
- **Show Field**: `zhik_dealerlocator/fields/show_description`
- **Required**: `zhik_dealerlocator/fields/description_required`
- **Default**: Show: Yes, Required: No
- **Max Length**: `zhik_dealerlocator/fields/description_max_length` (Default: 500)

#### Image Upload
- **Enable Upload**: `zhik_dealerlocator/fields/enable_image_upload`
- **Required**: `zhik_dealerlocator/fields/image_required`
- **Max File Size**: `zhik_dealerlocator/fields/image_max_size` (Default: 2MB)
- **Allowed Types**: `zhik_dealerlocator/fields/image_types` (Default: jpg,jpeg,png,gif)

## Customer Settings

### Require Customer Login
- **Path**: `zhik_dealerlocator/customer/require_login`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Require customers to log in before submitting locations

### Auto-Approve Submissions
- **Path**: `zhik_dealerlocator/customer/auto_approve`
- **Type**: Yes/No
- **Default**: No
- **Description**: Automatically approve customer submissions (bypasses admin review)

### Submission Limit
- **Path**: `zhik_dealerlocator/customer/submission_limit`
- **Type**: Text
- **Default**: 0 (unlimited)
- **Description**: Maximum locations per customer (0 = unlimited)

### Allow Editing
- **Path**: `zhik_dealerlocator/customer/allow_edit`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Allow customers to edit their pending/rejected locations

### Allow Deletion
- **Path**: `zhik_dealerlocator/customer/allow_delete`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Allow customers to request deletion of approved locations

### Show Submission Guidelines
- **Path**: `zhik_dealerlocator/customer/show_guidelines`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Display submission guidelines on the form

### Guidelines Text
- **Path**: `zhik_dealerlocator/customer/guidelines_text`
- **Type**: Textarea
- **Default**: Provides standard guidelines
- **Description**: Custom guidelines for location submission

## Email Notifications

### Enable Email Notifications
- **Path**: `zhik_dealerlocator/email/enabled`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Master switch for all email notifications

### Admin Notification Email
- **Path**: `zhik_dealerlocator/email/admin_email`
- **Type**: Email
- **Default**: General contact email
- **Description**: Where to send admin notifications

### Email Sender
- **Path**: `zhik_dealerlocator/email/identity`
- **Type**: Select
- **Options**: General, Sales, Support, Custom 1, Custom 2
- **Default**: General
- **Description**: Email sender identity

### Email Templates

#### New Submission - Admin
- **Path**: `zhik_dealerlocator/email/admin_notification_template`
- **Default**: `zhik_dealerlocator_admin_notification`
- **Description**: Template for notifying admin of new submissions

#### Submission Approved - Customer
- **Path**: `zhik_dealerlocator/email/approved_template`
- **Default**: `zhik_dealerlocator_location_approved`
- **Description**: Template for approved location notifications

#### Submission Rejected - Customer
- **Path**: `zhik_dealerlocator/email/rejected_template`
- **Default**: `zhik_dealerlocator_location_rejected`
- **Description**: Template for rejected location notifications

#### Status Update - Customer
- **Path**: `zhik_dealerlocator/email/updated_template`
- **Default**: `zhik_dealerlocator_location_updated`
- **Description**: Template for general status updates

### Email Options

#### Send Copy to Submitter
- **Path**: `zhik_dealerlocator/email/send_copy_to_submitter`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Send confirmation email to location submitter

#### CC Email Addresses
- **Path**: `zhik_dealerlocator/email/copy_to`
- **Type**: Text
- **Description**: Additional emails to CC (comma-separated)

## Advanced Settings

### Geocoding

#### Enable Auto-Geocoding
- **Path**: `zhik_dealerlocator/advanced/auto_geocode`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Automatically geocode addresses to coordinates

#### Geocoding Service
- **Path**: `zhik_dealerlocator/advanced/geocoding_service`
- **Type**: Select
- **Options**: Google Maps, OpenStreetMap
- **Default**: Google Maps

### Performance

#### Enable Caching
- **Path**: `zhik_dealerlocator/advanced/enable_cache`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Cache location data for better performance

#### Cache Lifetime
- **Path**: `zhik_dealerlocator/advanced/cache_lifetime`
- **Type**: Text
- **Default**: 3600 (1 hour)
- **Description**: Cache lifetime in seconds

#### Ajax Page Size
- **Path**: `zhik_dealerlocator/advanced/ajax_page_size`
- **Type**: Text
- **Default**: 50
- **Description**: Number of locations to load per AJAX request

### Security

#### Enable reCAPTCHA
- **Path**: `zhik_dealerlocator/advanced/enable_recaptcha`
- **Type**: Yes/No
- **Default**: Yes (if reCAPTCHA module installed)
- **Description**: Add reCAPTCHA to submission form

#### Allowed Countries
- **Path**: `zhik_dealerlocator/advanced/allowed_countries`
- **Type**: Multiselect
- **Default**: All Countries
- **Description**: Restrict submissions to specific countries

#### IP Address Logging
- **Path**: `zhik_dealerlocator/advanced/log_ip_address`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Log submitter IP addresses for security

### SEO Settings

#### URL Suffix
- **Path**: `zhik_dealerlocator/seo/url_suffix`
- **Type**: Text
- **Default**: `.html`
- **Description**: URL suffix for location pages (if individual pages enabled)

#### Enable Sitemap
- **Path**: `zhik_dealerlocator/seo/enable_sitemap`
- **Type**: Yes/No
- **Default**: Yes
- **Description**: Include locations in XML sitemap

#### Robots Meta Tag
- **Path**: `zhik_dealerlocator/seo/robots`
- **Type**: Select
- **Options**: INDEX,FOLLOW | NOINDEX,FOLLOW | INDEX,NOFOLLOW | NOINDEX,NOFOLLOW
- **Default**: INDEX,FOLLOW

## Store View Configuration

### Multi-Store Setup

Different configuration per store view:
1. Select store view from scope selector
2. Uncheck "Use Default" for fields to override
3. Enter store-specific values

Common store-specific settings:
- Google Maps API Key (different domains)
- Default map center (regional defaults)
- Allowed countries
- Email templates (language-specific)
- Field labels and guidelines

### Language Configuration

For multi-language stores:
- Email templates per store view
- Field labels via translation files
- Guidelines text per store view
- Map language via Google Maps parameter

## Configuration via Code

### Programmatic Configuration

Set configuration values programmatically:

```php
// In a setup script or observer
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigSetup
{
    private $configWriter;
    
    public function __construct(WriterInterface $configWriter)
    {
        $this->configWriter = $configWriter;
    }
    
    public function setConfig()
    {
        // Set default scope
        $this->configWriter->save(
            'zhik_dealerlocator/general/enabled',
            '1'
        );
        
        // Set for specific store
        $this->configWriter->save(
            'zhik_dealerlocator/google_maps/api_key',
            'your-api-key',
            'stores',
            1 // store ID
        );
    }
}
```

### Configuration in config.php

For deployment consistency:

```php
// app/etc/config.php
return [
    'modules' => [
        'Zhik_DealerLocator' => 1,
    ],
    'system' => [
        'default' => [
            'zhik_dealerlocator' => [
                'general' => [
                    'enabled' => '1',
                    'url_key' => 'find-dealer'
                ],
                'google_maps' => [
                    'default_zoom' => '12'
                ]
            ]
        ]
    ]
];
```

### Environment Variables

For sensitive data:

```bash
# In env.php or environment variables
CONFIG__DEFAULT__ZHIK_DEALERLOCATOR__GOOGLE_MAPS__API_KEY="your-api-key"
```

---

For more information, see the [Admin Guide](ADMIN-GUIDE.md) for using these configurations.