# Dealer Locator REST API Examples

## Base URL
```
http://localhost/rest/V1/dealerlocator
```

## Authentication

### Customer Token (for customer endpoints)
```bash
curl -X POST "http://localhost/rest/V1/integration/customer/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"customer@example.com","password":"password123"}'
```

### Admin Token (for admin endpoints)  
```bash
curl -X POST "http://localhost/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

## Public Endpoints (No Authentication Required)

### 1. Get All Approved Locations
```bash
curl -X GET "http://localhost/rest/V1/dealerlocator/locations" \
  -H "Content-Type: application/json"
```

### 2. Get Location by ID
```bash
curl -X GET "http://localhost/rest/V1/dealerlocator/locations/1" \
  -H "Content-Type: application/json"
```

### 3. Search Locations
```bash
# Search by query
curl -X GET "http://localhost/rest/V1/dealerlocator/locations/search?query=bike" \
  -H "Content-Type: application/json"

# Search with filters
curl -X GET "http://localhost/rest/V1/dealerlocator/locations/search?city=New%20York&tagIds[]=1&tagIds[]=2" \
  -H "Content-Type: application/json"
```

### 4. Search Nearby Locations
```bash
# Find locations within 10km of coordinates
curl -X GET "http://localhost/rest/V1/dealerlocator/locations/nearby?latitude=40.7128&longitude=-74.0060&radius=10" \
  -H "Content-Type: application/json"
```

### 5. Get All Tags
```bash
curl -X GET "http://localhost/rest/V1/dealerlocator/tags" \
  -H "Content-Type: application/json"
```

## Customer Endpoints (Requires Customer Token)

### 1. Get My Locations
```bash
curl -X GET "http://localhost/rest/V1/dealerlocator/mine/locations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_CUSTOMER_TOKEN"
```

### 2. Submit New Location
```bash
curl -X POST "http://localhost/rest/V1/dealerlocator/mine/locations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_CUSTOMER_TOKEN" \
  -d '{
    "location": {
      "name": "My Bike Shop",
      "address": "123 Main St",
      "city": "New York",
      "state": "NY",
      "postal_code": "10001",
      "country": "US",
      "phone": "555-1234",
      "email": "shop@example.com",
      "website": "https://example.com",
      "hours": "Mon-Fri 9am-5pm",
      "description": "Full service bike shop",
      "latitude": 40.7128,
      "longitude": -74.0060,
      "tag_ids": [1, 2]
    }
  }'
```

### 3. Update My Location
```bash
curl -X PUT "http://localhost/rest/V1/dealerlocator/mine/locations/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_CUSTOMER_TOKEN" \
  -d '{
    "location": {
      "location_id": 1,
      "name": "My Updated Bike Shop",
      "address": "456 Main St",
      "city": "New York",
      "state": "NY",
      "postal_code": "10001",
      "country": "US",
      "phone": "555-5678",
      "email": "newshop@example.com"
    }
  }'
```

### 4. Delete My Location
```bash
curl -X DELETE "http://localhost/rest/V1/dealerlocator/mine/locations/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_CUSTOMER_TOKEN"
```

## Admin Endpoints (Requires Admin Token)

### 1. Create Location (Admin)
```bash
curl -X POST "http://localhost/rest/V1/dealerlocator/locations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "location": {
      "customer_id": 1,
      "name": "Admin Created Shop",
      "address": "789 Admin St",
      "city": "Boston",
      "state": "MA",
      "postal_code": "02101",
      "country": "US",
      "phone": "555-9999",
      "email": "admin@shop.com",
      "status": "approved",
      "is_active": 1
    }
  }'
```

### 2. Update Location (Admin)
```bash
curl -X PUT "http://localhost/rest/V1/dealerlocator/locations/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "location": {
      "location_id": 1,
      "status": "approved",
      "is_active": 1
    }
  }'
```

### 3. Approve Location
```bash
curl -X POST "http://localhost/rest/V1/dealerlocator/locations/1/approve" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

### 4. Reject Location
```bash
curl -X POST "http://localhost/rest/V1/dealerlocator/locations/1/reject" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "reason": "Incomplete information provided"
  }'
```

### 5. Delete Location (Admin)
```bash
curl -X DELETE "http://localhost/rest/V1/dealerlocator/locations/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

### 6. Create Tag
```bash
curl -X POST "http://localhost/rest/V1/dealerlocator/tags" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "tag": {
      "name": "Premium Dealer",
      "description": "Premium certified dealers",
      "is_active": 1
    }
  }'
```

### 7. Update Tag
```bash
curl -X PUT "http://localhost/rest/V1/dealerlocator/tags/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "tag": {
      "tag_id": 1,
      "name": "Updated Tag Name",
      "is_active": 1
    }
  }'
```

### 8. Delete Tag
```bash
curl -X DELETE "http://localhost/rest/V1/dealerlocator/tags/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

## Response Examples

### Location Response
```json
{
  "location_id": 1,
  "customer_id": 1,
  "name": "My Bike Shop",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "postal_code": "10001",
  "country": "US",
  "phone": "555-1234",
  "email": "shop@example.com",
  "website": "https://example.com",
  "hours": "Mon-Fri 9am-5pm",
  "description": "Full service bike shop",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "status": "approved",
  "is_active": 1,
  "created_at": "2024-01-01 12:00:00",
  "updated_at": "2024-01-01 12:00:00",
  "extension_attributes": {
    "tags": [
      {
        "tag_id": 1,
        "name": "Authorized Dealer",
        "description": "Official authorized dealer"
      }
    ],
    "distance": 2.5
  }
}
```

### Search Results Response
```json
{
  "items": [
    {
      "location_id": 1,
      "name": "My Bike Shop",
      // ... full location data
    }
  ],
  "search_criteria": {
    "filter_groups": [],
    "page_size": 20,
    "current_page": 1
  },
  "total_count": 15
}
```

### Error Response
```json
{
  "message": "The location that was requested doesn't exist. Verify the location and try again.",
  "trace": "..."
}
```

## Rate Limiting

The API implements standard Magento rate limiting:
- Guest users: 20 requests per minute
- Authenticated customers: 50 requests per minute  
- Admin users: Unlimited

## Best Practices

1. Always use HTTPS in production
2. Store tokens securely and never expose them in client-side code
3. Implement proper error handling for all API calls
4. Use pagination for large result sets
5. Cache responses where appropriate
6. Include only necessary fields in requests to minimize payload size