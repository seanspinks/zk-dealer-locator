# Dealer Location Reject Functionality Testing Checklist

## Prerequisites
- Access admin panel at http://localhost/admin
- Have at least one location with "Pending" status

## Test Case 1: Reject from Location Edit Page
1. Navigate to Marketing > Dealer Locations
2. Click Edit on a location with "Pending" status
3. Click the "Reject" button (should appear next to Save buttons)
4. **Expected**: Modal dialog opens
5. Enter rejection reason
6. Click "Reject" in modal
7. **Expected**: Page reloads, location status is "Rejected"

## Test Case 2: Reject from Grid View
1. Navigate to Marketing > Dealer Locations
2. Find a location with "Pending" status
3. In the Actions column, click "Reject"
4. **Expected**: Prompt dialog opens
5. Enter rejection reason
6. Click OK/Confirm
7. **Expected**: Grid refreshes, location status is "Rejected"

## Console Debugging
Open browser console (F12) to see debug messages:
- When clicking Reject button: "DealerLocator Reject Handler: Opening modal..."
- When submitting: "DealerLocator Reject Handler: Rejecting location ID: [id]"
- After success: "DealerLocator Reject Handler: Response: {success: true...}"

## Common Issues Resolved
✅ 404 error when rejecting - Fixed with HttpPostActionInterface
✅ Grid reject not working - Fixed with proper location_id parameter
✅ Location ID not found - Fixed with fallback selectors

## Notes
- Reject button only appears for locations with "Pending" status
- Both workflows should now work correctly
- All changes have been deployed to Docker