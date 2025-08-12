# Dealer Location Reject Action Fix - Complete Overhaul

## Problem Analysis
The reject action wasn't working because it was implemented differently from the approve action:
- Approve action used standard Magento grid action pattern
- Reject action tried to use custom JavaScript with onclick handler
- This approach wasn't compatible with Magento's UI grid component

## Solution: Match Approve Action Pattern

### 1. Updated LocationActions.php
Changed reject action configuration from:
```php
$item[$name]['reject'] = [
    'href' => '#',
    'label' => __('Reject'),
    'onclick' => sprintf(
        "require(['Zhik_DealerLocator/js/grid/actions'], function(actions) { actions.rejectLocation('%s'); }); return false;",
        $this->urlBuilder->getUrl(static::URL_PATH_REJECT, ['location_id' => $item['location_id']])
    )
];
```

To match approve pattern:
```php
$item[$name]['reject'] = [
    'href' => $this->urlBuilder->getUrl(
        static::URL_PATH_REJECT,
        ['location_id' => $item['location_id']]
    ),
    'label' => __('Reject'),
    'callback' => 'Zhik_DealerLocator/js/grid/actions/reject',
    'confirm' => [
        'title' => __('Reject Location'),
        'message' => __('Are you sure you want to reject this location?')
    ],
    'post' => true
];
```

### 2. Created New Callback Handler
Created `/js/grid/actions/reject.js` following Magento's callback pattern:
- Uses Magento's prompt modal for rejection reason
- Integrates with grid's action handler
- Adds reason to POST data

### 3. Updated Reject Controller
Enhanced to handle both AJAX and regular requests like Approve controller:
- Added proper return types (Json|Redirect)
- Handles isAjax() check for proper response format
- Returns appropriate messages for both request types

### 4. Added HttpPostActionInterface
- Added to Delete controller
- Already present in Reject controller
- Ensures proper CSRF protection

### 5. Cleaned Up
- Removed old `/js/grid/actions.js` file
- Updated requirejs-config.js to remove old reference
- Added new callback module reference

## Key Differences Fixed
| Feature | Old (Broken) | New (Working) |
|---------|--------------|---------------|
| href | '#' | Proper URL |
| Handler | Custom onclick | Standard callback |
| POST | Not specified | 'post' => true |
| Pattern | Custom JS | Magento standard |

## Result
The reject action now works exactly like the approve action but with a prompt for rejection reason. Both grid and edit form reject buttons should work properly.