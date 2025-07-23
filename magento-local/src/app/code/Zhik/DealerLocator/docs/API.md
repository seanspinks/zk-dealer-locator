# API Documentation - Zhik Dealer Locator

Complete REST API reference for the Zhik Dealer Locator module.

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Public Endpoints](#public-endpoints)
4. [Customer Endpoints](#customer-endpoints)
5. [Admin Endpoints](#admin-endpoints)
6. [Search Endpoints](#search-endpoints)
7. [Data Models](#data-models)
8. [Error Handling](#error-handling)
9. [Examples](#examples)
10. [Rate Limiting](#rate-limiting)

## Overview

The Dealer Locator module provides a comprehensive REST API for managing dealer locations. All endpoints follow Magento 2 REST API standards.

### Base URLs
- **Public API**: `https://yourdomain.com/rest/V1/dealerlocator/`
- **Customer API**: `https://yourdomain.com/rest/V1/dealerlocator/mine/`
- **Admin API**: `https://yourdomain.com/rest/V1/dealerlocator/`

### Content Types
- **Request**: `application/json`
- **Response**: `application/json`

### HTTP Methods
- `GET` - Retrieve resources
- `POST` - Create new resources
- `PUT` - Update existing resources
- `DELETE` - Delete resources

## Authentication

### Public Access
Public endpoints require no authentication:
- Search locations
- Get location details
- Search nearby locations

### Customer Authentication
Customer endpoints require a customer token:

```bash
# Get customer token
curl -X POST "https://yourdomain.com/rest/V1/integration/customer/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"customer@example.com","password":"password123"}'

# Response
"eyJraWQiOiIxIiwiYWxnIjoiSFMy..."
```

Use the token in subsequent requests:
```bash
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/mine/locations" \
  -H "Authorization: Bearer eyJraWQiOiIxIiwiYWxnIjoiSFMy..."
```

### Admin Authentication
Admin endpoints require an admin token:

```bash
# Get admin token
curl -X POST "https://yourdomain.com/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

## Public Endpoints

### Search Locations

Search for dealer locations with various filters.

**Endpoint**: `GET /rest/V1/dealerlocator/search`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| searchCriteria[filter_groups][0][filters][0][field] | string | No | Field to filter by |
| searchCriteria[filter_groups][0][filters][0][value] | string | No | Value to filter |
| searchCriteria[filter_groups][0][filters][0][condition_type] | string | No | Condition (eq, neq, like, gt, lt, gteq, lteq, in, nin) |
| searchCriteria[sortOrders][0][field] | string | No | Field to sort by |
| searchCriteria[sortOrders][0][direction] | string | No | Sort direction (ASC, DESC) |
| searchCriteria[pageSize] | int | No | Items per page (default: 20) |
| searchCriteria[currentPage] | int | No | Page number (default: 1) |

**Response**:
```json
{
    "items": [
        {
            "location_id": 1,
            "name": "Downtown Store",
            "street": "123 Main St",
            "city": "New York",
            "state": "NY",
            "postal_code": "10001",
            "country": "US",
            "latitude": "40.7580",
            "longitude": "-73.9855",
            "phone": "555-1234",
            "email": "downtown@example.com",
            "website": "https://example.com",
            "hours": "Mon-Fri 9-6, Sat 10-4",
            "description": "Our flagship location",
            "status": "approved",
            "created_at": "2024-01-15 10:30:00",
            "tags": [
                {
                    "tag_id": 1,
                    "name": "Sales",
                    "code": "sales",
                    "color": "#0000FF"
                }
            ]
        }
    ],
    "search_criteria": {
        "filter_groups": [],
        "sort_orders": [],
        "page_size": 20,
        "current_page": 1
    },
    "total_count": 42
}
```

**Examples**:

```bash
# Get all approved locations
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=status&\
searchCriteria[filter_groups][0][filters][0][value]=approved&\
searchCriteria[filter_groups][0][filters][0][condition_type]=eq"

# Search by city
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=city&\
searchCriteria[filter_groups][0][filters][0][value]=New York&\
searchCriteria[filter_groups][0][filters][0][condition_type]=eq"

# Search by name (partial match)
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=name&\
searchCriteria[filter_groups][0][filters][0][value]=%Store%&\
searchCriteria[filter_groups][0][filters][0][condition_type]=like"

# Multiple filters (status = approved AND country = US)
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=status&\
searchCriteria[filter_groups][0][filters][0][value]=approved&\
searchCriteria[filter_groups][1][filters][0][field]=country&\
searchCriteria[filter_groups][1][filters][0][value]=US"

# Sort by name ascending
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[sortOrders][0][field]=name&\
searchCriteria[sortOrders][0][direction]=ASC"

# Pagination
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[pageSize]=10&\
searchCriteria[currentPage]=2"
```

### Search Nearby Locations

Find locations within a radius of given coordinates.

**Endpoint**: `GET /rest/V1/dealerlocator/search/nearby`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| latitude | float | Yes | Center point latitude |
| longitude | float | Yes | Center point longitude |
| radius | float | Yes | Search radius in kilometers |
| tagIds | int[] | No | Filter by tag IDs |
| limit | int | No | Maximum results (default: 50) |

**Response**:
```json
{
    "items": [
        {
            "location_id": 1,
            "name": "Downtown Store",
            "street": "123 Main St",
            "city": "New York",
            "state": "NY",
            "postal_code": "10001",
            "country": "US",
            "latitude": "40.7580",
            "longitude": "-73.9855",
            "distance": 2.5,
            "phone": "555-1234",
            "email": "downtown@example.com",
            "website": "https://example.com",
            "tags": []
        }
    ],
    "search_criteria": {
        "filter_groups": []
    },
    "total_count": 5
}
```

**Example**:
```bash
# Find locations within 50km of coordinates
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search/nearby?\
latitude=40.7128&\
longitude=-74.0060&\
radius=50"

# Filter by tags
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search/nearby?\
latitude=40.7128&\
longitude=-74.0060&\
radius=50&\
tagIds[0]=1&\
tagIds[1]=3"
```

### Get Location Details

Get detailed information about a specific location.

**Endpoint**: `GET /rest/V1/dealerlocator/location/:locationId`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| locationId | int | Yes | Location ID |

**Response**:
```json
{
    "location_id": 1,
    "name": "Downtown Store",
    "street": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postal_code": "10001",
    "country": "US",
    "latitude": "40.7580",
    "longitude": "-73.9855",
    "phone": "555-1234",
    "email": "downtown@example.com",
    "website": "https://example.com",
    "hours": "Mon-Fri 9-6, Sat 10-4",
    "description": "Our flagship location",
    "image": "https://example.com/media/dealer/location/store1.jpg",
    "status": "approved",
    "customer_id": 123,
    "store_id": 1,
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00",
    "tags": [
        {
            "tag_id": 1,
            "name": "Sales",
            "code": "sales",
            "color": "#0000FF"
        }
    ]
}
```

**Example**:
```bash
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/location/1"
```

### Get All Tags

Retrieve all available location tags.

**Endpoint**: `GET /rest/V1/dealerlocator/tags`

**Response**:
```json
{
    "items": [
        {
            "tag_id": 1,
            "name": "Sales",
            "code": "sales",
            "color": "#0000FF",
            "icon": "fa-shopping-cart",
            "sort_order": 10,
            "is_active": true
        },
        {
            "tag_id": 2,
            "name": "Service",
            "code": "service",
            "color": "#00FF00",
            "icon": "fa-wrench",
            "sort_order": 20,
            "is_active": true
        }
    ],
    "total_count": 5
}
```

**Example**:
```bash
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/tags"
```

## Customer Endpoints

These endpoints require customer authentication.

### Get My Locations

Get all locations submitted by the authenticated customer.

**Endpoint**: `GET /rest/V1/dealerlocator/mine/locations`

**Headers**:
```
Authorization: Bearer {customer_token}
```

**Response**:
```json
{
    "items": [
        {
            "location_id": 1,
            "name": "My Store",
            "street": "456 Oak Ave",
            "city": "Boston",
            "state": "MA",
            "postal_code": "02101",
            "country": "US",
            "status": "pending",
            "created_at": "2024-01-20 14:20:00"
        }
    ],
    "total_count": 3
}
```

**Example**:
```bash
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/mine/locations" \
  -H "Authorization: Bearer {customer_token}"
```

### Submit New Location

Submit a new location for approval.

**Endpoint**: `POST /rest/V1/dealerlocator/mine/locations`

**Headers**:
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "location": {
        "name": "New Store Location",
        "street": "789 Pine St",
        "city": "Chicago",
        "state": "IL",
        "postal_code": "60601",
        "country": "US",
        "phone": "555-9876",
        "email": "chicago@example.com",
        "website": "https://chicago.example.com",
        "hours": "Mon-Sat 10-8, Sun 12-6",
        "description": "Our newest location in downtown Chicago"
    }
}
```

**Response**:
```json
{
    "location_id": 45,
    "name": "New Store Location",
    "street": "789 Pine St",
    "city": "Chicago",
    "state": "IL",
    "postal_code": "60601",
    "country": "US",
    "latitude": "41.8781",
    "longitude": "-87.6298",
    "status": "pending",
    "customer_id": 123,
    "created_at": "2024-01-25 09:15:00"
}
```

**Example**:
```bash
curl -X POST "https://yourdomain.com/rest/V1/dealerlocator/mine/locations" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "name": "New Store",
        "street": "789 Pine St",
        "city": "Chicago",
        "state": "IL",
        "postal_code": "60601",
        "country": "US"
    }
}'
```

### Update My Location

Update a pending or rejected location.

**Endpoint**: `PUT /rest/V1/dealerlocator/mine/location/:locationId`

**Headers**:
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "location": {
        "name": "Updated Store Name",
        "phone": "555-1111",
        "hours": "Mon-Fri 9-7, Sat-Sun 10-5"
    }
}
```

**Response**:
```json
{
    "location_id": 45,
    "name": "Updated Store Name",
    "status": "pending",
    "updated_at": "2024-01-25 10:30:00"
}
```

**Example**:
```bash
curl -X PUT "https://yourdomain.com/rest/V1/dealerlocator/mine/location/45" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "name": "Updated Store Name",
        "phone": "555-1111"
    }
}'
```

### Request Location Deletion

Request deletion of an approved location.

**Endpoint**: `DELETE /rest/V1/dealerlocator/mine/location/:locationId`

**Headers**:
```
Authorization: Bearer {customer_token}
```

**Response**:
```json
{
    "message": "Location has been marked for deletion. An administrator will review this request."
}
```

**Example**:
```bash
curl -X DELETE "https://yourdomain.com/rest/V1/dealerlocator/mine/location/45" \
  -H "Authorization: Bearer {customer_token}"
```

## Admin Endpoints

These endpoints require admin authentication and appropriate ACL permissions.

### Create Location

Create a new location (admin only).

**Endpoint**: `POST /rest/V1/dealerlocator/location`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "location": {
        "name": "Admin Created Store",
        "street": "321 Admin Ave",
        "city": "Dallas",
        "state": "TX",
        "postal_code": "75201",
        "country": "US",
        "phone": "555-0000",
        "email": "admin@example.com",
        "status": "approved",
        "tag_ids": [1, 2]
    }
}
```

**Response**: Same as location object

**Example**:
```bash
curl -X POST "https://yourdomain.com/rest/V1/dealerlocator/location" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "name": "Admin Store",
        "street": "321 Admin Ave",
        "city": "Dallas",
        "state": "TX",
        "postal_code": "75201",
        "country": "US",
        "status": "approved"
    }
}'
```

### Update Location

Update any location (admin only).

**Endpoint**: `PUT /rest/V1/dealerlocator/location/:locationId`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "location": {
        "status": "approved",
        "tag_ids": [1, 3, 5]
    }
}
```

**Example**:
```bash
curl -X PUT "https://yourdomain.com/rest/V1/dealerlocator/location/45" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "status": "approved"
    }
}'
```

### Delete Location

Permanently delete a location (admin only).

**Endpoint**: `DELETE /rest/V1/dealerlocator/location/:locationId`

**Headers**:
```
Authorization: Bearer {admin_token}
```

**Response**:
```json
{
    "message": "Location has been deleted successfully."
}
```

**Example**:
```bash
curl -X DELETE "https://yourdomain.com/rest/V1/dealerlocator/location/45" \
  -H "Authorization: Bearer {admin_token}"
```

### Bulk Update Status

Update status for multiple locations.

**Endpoint**: `POST /rest/V1/dealerlocator/locations/massStatus`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "locationIds": [1, 2, 3, 4, 5],
    "status": "approved"
}
```

**Response**:
```json
{
    "affected_locations": 5,
    "message": "5 location(s) have been updated."
}
```

### Approve Deletion Requests

Approve pending deletion requests.

**Endpoint**: `POST /rest/V1/dealerlocator/locations/massApproveDeletion`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "locationIds": [10, 11, 12]
}
```

**Response**:
```json
{
    "deleted_count": 3,
    "message": "3 location(s) have been deleted."
}
```

## Search Endpoints

### Advanced Search

Complex search with multiple criteria.

**Endpoint**: `POST /rest/V1/dealerlocator/search/advanced`

**Request Body**:
```json
{
    "query": "store",
    "filters": {
        "country": ["US", "CA"],
        "state": ["NY", "CA", "TX"],
        "tags": [1, 2],
        "status": "approved"
    },
    "sort": {
        "field": "name",
        "direction": "ASC"
    },
    "pagination": {
        "page": 1,
        "limit": 20
    }
}
```

**Response**: Same as search endpoint

### Search Suggestions

Get search suggestions based on partial input.

**Endpoint**: `GET /rest/V1/dealerlocator/search/suggestions`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| query | string | Yes | Partial search term |
| limit | int | No | Maximum suggestions (default: 10) |

**Response**:
```json
{
    "suggestions": [
        {
            "value": "Downtown Store",
            "data": {
                "location_id": 1,
                "city": "New York",
                "state": "NY"
            }
        }
    ]
}
```

## Data Models

### Location Object

```json
{
    "location_id": "integer",
    "name": "string (required)",
    "street": "string (required)",
    "city": "string (required)",
    "state": "string",
    "postal_code": "string",
    "country": "string (required, ISO 3166-1 alpha-2)",
    "latitude": "decimal(10,8)",
    "longitude": "decimal(11,8)",
    "phone": "string (max 50)",
    "email": "string (valid email)",
    "website": "string (valid URL)",
    "hours": "string",
    "description": "string",
    "image": "string (URL)",
    "status": "string (pending|approved|rejected|pending_deletion)",
    "customer_id": "integer",
    "parent_id": "integer",
    "is_latest": "boolean",
    "store_id": "integer",
    "created_by": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "tags": "array of tag objects",
    "extension_attributes": "object"
}
```

### Tag Object

```json
{
    "tag_id": "integer",
    "name": "string (required)",
    "code": "string (required, unique)",
    "color": "string (hex color)",
    "icon": "string (icon class)",
    "sort_order": "integer",
    "is_active": "boolean"
}
```

### Search Results Object

```json
{
    "items": "array of location objects",
    "search_criteria": {
        "filter_groups": "array",
        "sort_orders": "array",
        "page_size": "integer",
        "current_page": "integer"
    },
    "total_count": "integer"
}
```

### Error Response

```json
{
    "message": "string",
    "errors": [
        {
            "message": "string",
            "parameters": []
        }
    ],
    "code": "integer",
    "parameters": [],
    "trace": "string (debug mode only)"
}
```

## Error Handling

### HTTP Status Codes

| Code | Description | Common Causes |
|------|-------------|---------------|
| 200 | Success | Request completed successfully |
| 201 | Created | New resource created successfully |
| 400 | Bad Request | Invalid parameters, validation errors |
| 401 | Unauthorized | Missing or invalid authentication token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | Duplicate entry, concurrent modification |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server error, check logs |

### Common Error Messages

```json
{
    "message": "Location with id \"999\" does not exist.",
    "code": 404
}

{
    "message": "You are not authorized to delete this location.",
    "code": 403
}

{
    "message": "\"name\" is a required field.",
    "code": 400
}

{
    "message": "The consumer isn't authorized to access %resources.",
    "parameters": {
        "resources": "Zhik_DealerLocator::locations_save"
    },
    "code": 401
}
```

### Validation Errors

```json
{
    "message": "Validation Failed",
    "errors": [
        {
            "message": "\"email\" is not a valid email address.",
            "parameters": ["email"]
        },
        {
            "message": "\"website\" is not a valid URL.",
            "parameters": ["website"]
        }
    ]
}
```

## Examples

### Complete Search Example

```bash
#!/bin/bash

# Configuration
API_URL="https://yourdomain.com/rest/V1/dealerlocator"
TOKEN="your_token_here"

# Search with multiple filters and pagination
curl -X GET "${API_URL}/search?\
searchCriteria[filter_groups][0][filters][0][field]=status&\
searchCriteria[filter_groups][0][filters][0][value]=approved&\
searchCriteria[filter_groups][0][filters][0][condition_type]=eq&\
searchCriteria[filter_groups][1][filters][0][field]=country&\
searchCriteria[filter_groups][1][filters][0][value]=US&\
searchCriteria[filter_groups][1][filters][0][condition_type]=eq&\
searchCriteria[filter_groups][2][filters][0][field]=state&\
searchCriteria[filter_groups][2][filters][0][value]=NY,CA,TX&\
searchCriteria[filter_groups][2][filters][0][condition_type]=in&\
searchCriteria[sortOrders][0][field]=name&\
searchCriteria[sortOrders][0][direction]=ASC&\
searchCriteria[pageSize]=20&\
searchCriteria[currentPage]=1" \
-H "Authorization: Bearer ${TOKEN}" | jq '.'
```

### Customer Workflow Example

```bash
#!/bin/bash

# 1. Get customer token
TOKEN=$(curl -X POST "https://yourdomain.com/rest/V1/integration/customer/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"customer@example.com","password":"password123"}' \
  | tr -d '"')

# 2. Submit new location
LOCATION_ID=$(curl -X POST "https://yourdomain.com/rest/V1/dealerlocator/mine/locations" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "name": "My New Store",
        "street": "123 Test St",
        "city": "Boston",
        "state": "MA",
        "postal_code": "02101",
        "country": "US"
    }
}' | jq -r '.location_id')

echo "Created location ID: ${LOCATION_ID}"

# 3. Check status
curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/mine/locations" \
  -H "Authorization: Bearer ${TOKEN}" | jq '.'

# 4. Update if needed
curl -X PUT "https://yourdomain.com/rest/V1/dealerlocator/mine/location/${LOCATION_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "location": {
        "phone": "555-1234",
        "email": "mystore@example.com"
    }
}' | jq '.'
```

### Admin Workflow Example

```bash
#!/bin/bash

# 1. Get admin token
ADMIN_TOKEN=$(curl -X POST "https://yourdomain.com/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  | tr -d '"')

# 2. Get pending locations
PENDING=$(curl -X GET "https://yourdomain.com/rest/V1/dealerlocator/search?\
searchCriteria[filter_groups][0][filters][0][field]=status&\
searchCriteria[filter_groups][0][filters][0][value]=pending" \
  -H "Authorization: Bearer ${ADMIN_TOKEN}")

# 3. Extract location IDs
LOCATION_IDS=$(echo $PENDING | jq -r '.items[].location_id' | tr '\n' ',')

# 4. Bulk approve
curl -X POST "https://yourdomain.com/rest/V1/dealerlocator/locations/massStatus" \
  -H "Authorization: Bearer ${ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{
    \"locationIds\": [${LOCATION_IDS%,}],
    \"status\": \"approved\"
}" | jq '.'
```

### JavaScript Example

```javascript
// Search nearby locations
async function searchNearby(lat, lng, radius) {
    const response = await fetch(`/rest/V1/dealerlocator/search/nearby?` + 
        `latitude=${lat}&longitude=${lng}&radius=${radius}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    return data.items;
}

// Customer location submission
async function submitLocation(token, locationData) {
    const response = await fetch('/rest/V1/dealerlocator/mine/locations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            location: locationData
        })
    });
    
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Submission failed');
    }
    
    return await response.json();
}

// Usage
try {
    const locations = await searchNearby(40.7128, -74.0060, 50);
    console.log(`Found ${locations.length} locations`);
    
    const newLocation = await submitLocation(customerToken, {
        name: "New Store",
        street: "456 Oak Ave",
        city: "Boston",
        state: "MA",
        postal_code: "02101",
        country: "US"
    });
    console.log(`Created location ID: ${newLocation.location_id}`);
} catch (error) {
    console.error('Error:', error);
}
```

## Rate Limiting

### Default Limits
- **Public endpoints**: 100 requests per minute
- **Customer endpoints**: 60 requests per minute per customer
- **Admin endpoints**: 200 requests per minute

### Headers
Rate limit information is included in response headers:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### Rate Limit Exceeded Response
```json
{
    "message": "Rate limit exceeded. Please try again later.",
    "code": 429,
    "retry_after": 60
}
```

### Best Practices
1. Implement exponential backoff for retries
2. Cache responses when possible
3. Use bulk endpoints for multiple operations
4. Monitor rate limit headers
5. Implement request queuing for high-volume operations

---

For more technical details, see the [Developer Guide](DEVELOPER-GUIDE.md).