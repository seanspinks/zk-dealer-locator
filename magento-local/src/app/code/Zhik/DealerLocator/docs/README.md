# Zhik Dealer Locator Module

A comprehensive dealer/store locator module for Magento 2 that provides a complete solution for managing physical locations with customer submissions, admin approval workflows, and interactive map displays.

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Requirements](#requirements)
- [Support](#support)

## Overview

The Zhik Dealer Locator module enables businesses to manage their physical locations through an intuitive interface. Customers can submit new locations, view existing locations on an interactive map, and manage their submissions through their account dashboard. Administrators have full control over approvals, edits, and location management.

### Module Information
- **Module Name**: Zhik_DealerLocator
- **Version**: 1.0.0
- **Compatibility**: Magento 2.4.x
- **License**: Proprietary

## Key Features

### Customer Features
- 🗺️ **Interactive Map Display** - View all approved locations on Google Maps
- 📍 **Location Search** - Search by location name, address, or proximity
- 📝 **Location Submission** - Submit new dealer locations for approval
- 👤 **Account Management** - Manage submitted locations through customer account
- 🔍 **Advanced Filtering** - Filter by tags, distance, and location attributes
- 📧 **Email Notifications** - Receive updates on submission status

### Admin Features
- ✅ **Approval Workflow** - Review and approve/reject location submissions
- 🏷️ **Tag Management** - Organize locations with customizable tags
- 📊 **Bulk Operations** - Mass approve, reject, or delete locations
- 📝 **Location Editing** - Full CRUD operations on all locations
- 📈 **Versioning System** - Track location history and changes
- 🔒 **ACL Integration** - Role-based permissions for admin users

### Technical Features
- 🚀 **REST API** - Full API coverage for all operations
- 🏗️ **Modular Architecture** - Clean, extensible codebase
- 🔌 **Plugin Support** - Extension points for customization
- 🌍 **Multi-store Support** - Store-specific location management
- 📱 **Responsive Design** - Mobile-friendly interface
- ⚡ **Performance Optimized** - Efficient database queries and caching

## Quick Start

### Installation
```bash
# Navigate to your Magento root directory
cd /path/to/magento

# Copy module to app/code
cp -r /path/to/Zhik/DealerLocator app/code/Zhik/

# Enable the module
bin/magento module:enable Zhik_DealerLocator

# Run setup upgrade
bin/magento setup:upgrade

# Deploy static content (if in production mode)
bin/magento setup:static-content:deploy

# Clear cache
bin/magento cache:flush
```

### Basic Configuration
1. Navigate to **Stores > Configuration > Zhik > Dealer Locator**
2. Enter your Google Maps API Key
3. Configure visibility options and notifications
4. Save configuration

### First Location
1. Customer submits location via "Add Location" in their account
2. Admin reviews in **Marketing > Dealer Locator > Manage Locations**
3. Approved locations appear on the map immediately

## Documentation

Detailed documentation is available in the following files:

- 📚 **[INSTALLATION.md](INSTALLATION.md)** - Detailed installation and setup instructions
- 🔧 **[CONFIGURATION.md](CONFIGURATION.md)** - All configuration options explained
- 👨‍💼 **[ADMIN-GUIDE.md](ADMIN-GUIDE.md)** - Complete admin user guide
- 👩‍💻 **[DEVELOPER-GUIDE.md](DEVELOPER-GUIDE.md)** - Developer documentation and API reference
- 🔌 **[API.md](API.md)** - REST API endpoint documentation
- 📝 **[CHANGELOG.md](CHANGELOG.md)** - Version history and updates

## Requirements

### System Requirements
- Magento 2.4.x (CE or EE)
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer

### External Services
- Google Maps API Key (with Maps JavaScript API and Geocoding API enabled)
- SMTP server for email notifications (optional)

### Magento Dependencies
- Magento_Customer
- Magento_Backend
- Magento_Ui
- Magento_Email
- Magento_Store

## Module Structure

```
DealerLocator/
├── Api/                  # Service contracts and interfaces
├── Block/               # Block classes for rendering
├── Controller/          # Frontend and admin controllers
├── Helper/              # Helper classes
├── Model/               # Business logic and data models
├── Setup/               # Installation and upgrade scripts
├── Ui/                  # UI component configurations
├── etc/                 # Module configuration files
├── view/                # Templates, layouts, and web assets
│   ├── adminhtml/      # Admin interface files
│   └── frontend/       # Customer interface files
└── Test/               # Unit tests
```

## Support

For issues, questions, or contributions:
- Check existing documentation
- Review code comments and docblocks
- Contact module maintainer

---

© 2024 Zhik. All rights reserved.