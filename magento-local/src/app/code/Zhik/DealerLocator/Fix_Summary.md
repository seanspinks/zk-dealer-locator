# Dealer Location Type Error Fix Summary

## Issue
When accessing the location edit page in admin, a TypeError was occurring:
```
TypeError: Zhik\DealerLocator\Model\LocationRepository\Interceptor::getById(): Argument #1 ($locationId) must be of type int, string given
```

## Root Cause
The `GenericButton::getLocationId()` method was unnecessarily complex - it was:
1. Getting the location_id parameter from request
2. Loading the location entity via repository
3. Returning the location's ID from the entity

This caused issues because:
- The method could return a string value from the entity
- It added unnecessary database queries
- It made the code more complex than needed

## Solution
Simplified the `GenericButton::getLocationId()` method to:
```php
public function getLocationId()
{
    $locationId = $this->context->getRequest()->getParam('location_id');
    return $locationId ? (int)$locationId : null;
}
```

Also fixed `RejectButton::getButtonData()` to explicitly cast the location ID:
```php
$location = $this->locationRepository->getById((int)$locationId);
```

## Files Modified
1. `/Block/Adminhtml/Location/Edit/GenericButton.php` - Simplified getLocationId() method
2. `/Block/Adminhtml/Location/Edit/RejectButton.php` - Added explicit type casting

## Verification
After checking all other usages of `getById()` in the module:
- All admin controllers already cast location_id to int when retrieving from request
- All customer controllers already cast location_id to int when retrieving from request
- Repository methods properly expect int type per interface definition

## Result
The type error is now fixed. The location edit page should load without errors, and all button providers will work correctly with proper integer type casting.