# Dealer Location Admin Testing Guide

## Overview
This guide helps verify that the dealer location form submission and reject button functionality are working correctly after recent fixes.

## Prerequisites
- Access to Magento admin panel at http://localhost/admin
- Admin credentials
- Browser developer console open (F12)

## Test Cases

### 1. Create New Location with Pending Status
1. Navigate to **Marketing > Dealer Locations**
2. Click **Add New Location**
3. Fill in required fields:
   - Location Name: "Test Location 1"
   - Status: **Pending**
   - Street Address: "123 Test Street"
   - City: "Test City"
   - Postal Code: "12345"
   - Country: "United States"
   - Phone: "555-1234"
   - Email: "test@example.com"
4. Click **Save**
5. **Expected Result**: 
   - Success message "You saved the location"
   - Redirected to locations grid
   - No errors in console

### 2. Create New Location with Approved Status
1. Click **Add New Location**
2. Fill in required fields (same as above but different name)
3. Set Status to **Approved**
4. Click **Save**
5. **Expected Result**:
   - Success message "You saved the location"
   - No "Location is already approved" error
   - Location saved with approved status

### 3. Create New Location with Rejected Status
1. Click **Add New Location**
2. Fill in required fields
3. Set Status to **Rejected**
4. Fill in Rejection Reason: "Test rejection"
5. Click **Save**
6. **Expected Result**:
   - Success message "You saved the location"
   - Location saved with rejected status and reason

### 4. Test Reject Button on Pending Location
1. Edit a location with **Pending** status
2. Verify **Reject** button appears (should be visible only for pending locations)
3. Click **Reject** button
4. **Expected Result**:
   - Modal dialog opens asking for rejection reason
   - Console shows: "DealerLocator Reject Handler: Opening modal..."
5. Enter rejection reason and click **Reject** in modal
6. **Expected Result**:
   - Page reloads
   - Location status changed to Rejected
   - Rejection reason saved

### 5. Test Save and Continue Edit
1. Edit any location
2. Make changes
3. Click **Save and Continue Edit**
4. **Expected Result**:
   - Success message appears
   - Stay on edit page (not redirected to grid)
   - Changes are saved

## Console Debug Messages
When testing, you should see these debug messages in browser console:

### Form Initialization:
```
DealerLocator Form: Initializing...
DealerLocator Form: Component initialized successfully
```

### Form Submission:
```
DealerLocator Form: Save action triggered
DealerLocator Form: Validating form...
DealerLocator Form: Form is valid, submitting...
DealerLocator Form: Submitting form with data: {...}
```

### Reject Button:
```
DealerLocator Reject Handler: Initializing...
DealerLocator Reject Handler: Initialized successfully
DealerLocator Reject Handler: Opening modal...
DealerLocator Reject Handler: Rejecting location ID: [id]
DealerLocator Reject Handler: Response: {...}
```

## Server-Side Logs
Check `var/log/system.log` for server-side debug messages:
```
DealerLocator Save: Request method: POST
DealerLocator Save: Post data: {...}
DealerLocator Save: Form key valid: YES
DealerLocator Save: Location ID: [id/null]
DealerLocator Save: Created new location / Loaded existing location
```

## Troubleshooting

### Form doesn't submit (nothing happens)
1. Check browser console for JavaScript errors
2. Verify form component loaded: Look for "DealerLocator Form: Component initialized successfully"
3. Check network tab for failed requests
4. Ensure static content is deployed: `bin/magento setup:static-content:deploy -f`

### Reject button doesn't appear
- Verify location status is "Pending" (button only shows for pending locations)
- Check console for "DealerLocator Reject Handler: Initialized successfully"

### "Invalid form key" error
- Refresh the page and try again
- Clear browser cache
- Check session isn't expired

### Modal doesn't open when clicking Reject
1. Check console for errors
2. Verify jQuery UI is loaded
3. Check for JavaScript conflicts

## Success Criteria
✅ All test cases pass without errors
✅ Console shows expected debug messages
✅ No JavaScript errors in browser console
✅ Server logs show successful operations
✅ UI behaves as expected (redirects, messages, etc.)

## Notes
- The reject button intentionally only appears for locations with "Pending" status
- Debug logging is enabled in both JavaScript and PHP for troubleshooting
- Form uses Magento's UI component validation before submission