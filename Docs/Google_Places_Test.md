# Google Places Autocomplete Test Guide

## Setup Instructions

### 1. Configure Google Maps API Key

1. Go to **Stores > Configuration > General > Dealer Locator > Google Maps Configuration**
2. Enter your Google Maps API Key
3. Save Configuration

### 2. Enable Required APIs in Google Cloud Console

Make sure these APIs are enabled for your API key:
- **Maps JavaScript API**
- **Places API**
- **Geocoding API** (optional, for better results)

### 3. Set API Key Restrictions (Recommended)

In Google Cloud Console:
1. Go to **APIs & Services > Credentials**
2. Click on your API key
3. Under **Application restrictions**, select **HTTP referrers**
4. Add your domains:
   - `http://localhost/*`
   - `https://yourdomain.com/*`

## Testing the Autocomplete

### Admin Location Form

1. Navigate to **Dealer Locator > Manage Locations**
2. Click **Add New Location** or edit an existing one
3. In the **Address Information** section:
   - Click on the **Street Address** field
   - Start typing an address (e.g., "1600 Amphitheatre Parkway")
   - You should see Google Places suggestions dropdown
   - Select an address from the dropdown

### Expected Behavior

When you select an address from the dropdown:

1. **Street Address** field fills with the street number and name
2. **City** field auto-fills with the city name
3. **State** field auto-fills with the state/province
4. **Postal Code** field auto-fills with the ZIP/postal code
5. **Country** field auto-fills with the country code (e.g., "US")
6. **Latitude** and **Longitude** fields (if visible) auto-fill with coordinates

### Troubleshooting

#### Autocomplete Not Showing

1. **Check Browser Console** (F12):
   ```javascript
   // Look for errors like:
   // "Google Maps JavaScript API error: InvalidKeyMapError"
   // "You have included the Google Maps JavaScript API multiple times"
   ```

2. **Verify API Key is Loading**:
   - Open Browser Developer Tools
   - Go to Network tab
   - Look for request to: `/admin/dealerlocator/config/apikey`
   - Should return: `{"api_key":"your-key-here"}`

3. **Check Google Maps Script Loading**:
   - In Network tab, look for:
   - `https://maps.googleapis.com/maps/api/js?key=YOUR_KEY&libraries=places`
   - Should load with status 200

#### Common Issues

1. **"This API key is not authorized"**
   - Enable Places API in Google Cloud Console
   - Check API key restrictions

2. **"You have exceeded your request quota"**
   - Check Google Cloud Console for quota limits
   - Enable billing if using free tier limits

3. **Autocomplete shows but fields don't fill**
   - Check browser console for JavaScript errors
   - Verify field names match in the form

### Testing Different Address Formats

Try these test addresses:

1. **US Address**: "1600 Amphitheatre Parkway, Mountain View, CA"
2. **UK Address**: "10 Downing Street, London"
3. **Australian Address**: "Sydney Opera House"
4. **Canadian Address**: "CN Tower, Toronto"

### Verifying the Implementation

1. **Check JavaScript Component**:
   ```bash
   # File should exist at:
   src/app/code/Zhik/DealerLocator/view/adminhtml/web/js/form/element/address-autocomplete.js
   ```

2. **Check Template**:
   ```bash
   # File should exist at:
   src/app/code/Zhik/DealerLocator/view/adminhtml/web/template/form/element/address-autocomplete.html
   ```

3. **Check Static Content Deployment**:
   ```bash
   # After deployment, files should be in:
   pub/static/adminhtml/Magento/backend/en_US/Zhik_DealerLocator/js/form/element/address-autocomplete.js
   pub/static/adminhtml/Magento/backend/en_US/Zhik_DealerLocator/template/form/element/address-autocomplete.html
   ```

## API Usage Monitoring

Monitor your API usage in Google Cloud Console:
1. Go to **APIs & Services > Metrics**
2. Select **Places API**
3. View requests, errors, and latency

## Security Notes

1. The API key is stored encrypted in Magento configuration
2. The key is only exposed to authenticated admin users
3. Always use HTTPS in production
4. Set proper API key restrictions in Google Cloud Console