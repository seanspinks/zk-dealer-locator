# UI/UX Documentation for Magento Dealer Locator Module

## Design System Specifications

### Brand Guidelines
- **Primary Color**: #1979C3 (Magento Blue)
- **Secondary Color**: #F26322 (Magento Orange)
- **Success Color**: #5CB85C
- **Warning Color**: #F0AD4E
- **Error Color**: #D9534F
- **Text Colors**: 
  - Primary: #333333
  - Secondary: #666666
  - Disabled: #999999

### Typography
- **Font Family**: Open Sans, Arial, sans-serif
- **Headings**:
  - H1: 32px, font-weight: 300
  - H2: 24px, font-weight: 400
  - H3: 18px, font-weight: 600
- **Body Text**: 14px, line-height: 1.428571429
- **Small Text**: 12px

### Spacing System
- Base unit: 10px
- Spacing scale: 10px, 20px, 30px, 40px, 50px
- Container padding: 20px
- Grid gutter: 30px

## UI Component Guidelines

### Form Components

#### Input Fields
```css
.input-text {
    height: 36px;
    padding: 0 10px;
    border: 1px solid #C2C2C2;
    border-radius: 3px;
    font-size: 14px;
}

.input-text:focus {
    border-color: #1979C3;
    box-shadow: 0 0 0 1px #1979C3;
}
```

#### Google Maps Autocomplete
- Integrate seamlessly with Magento form styling
- Show dropdown suggestions below input field
- Highlight matched text in suggestions
- Include location icon for each suggestion

#### Tag Selection
- Use checkbox list for multiple selection
- Group tags by category if applicable
- Show tag color indicator
- Maximum 3 columns on desktop, 1 on mobile

### Button Styles
```css
.action.primary {
    background: #1979C3;
    color: #FFFFFF;
    padding: 10px 20px;
    border-radius: 3px;
    font-weight: 600;
}

.action.secondary {
    background: #FFFFFF;
    color: #1979C3;
    border: 1px solid #1979C3;
}

.action.delete {
    background: #D9534F;
    color: #FFFFFF;
}
```

### Status Indicators
- **Pending**: Orange badge (#F0AD4E)
- **Approved**: Green badge (#5CB85C)
- **Rejected**: Red badge (#D9534F)

## User Experience Flow Diagrams

### Customer Location Submission Flow
```
1. Customer Login
   └─> My Account Dashboard
       └─> My Locations (menu item)
           └─> Location List Page
               ├─> Add New Location Button
               │   └─> Location Form
               │       ├─> Google Maps Autocomplete
               │       ├─> Form Fields
               │       ├─> Tag Selection
               │       └─> Submit Button
               │           └─> Success Message
               │               └─> Return to List
               └─> Edit/Delete Actions
                   └─> Confirmation Dialogs
```

### Admin Review Flow
```
1. Admin Login
   └─> Dealer Locator Menu
       ├─> Manage Locations
       │   └─> Location Grid
       │       ├─> Filters & Search
       │       ├─> Bulk Actions
       │       └─> Row Actions
       │           ├─> Edit
       │           ├─> Approve
       │           └─> Reject
       └─> Manage Tags
           └─> Tag Grid
               └─> Add/Edit Tags
```

## Responsive Design Requirements

### Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Mobile Adaptations
- Single column layout
- Collapsible filters
- Touch-friendly buttons (min 44px)
- Simplified navigation
- Stacked form fields

### Tablet Adaptations
- Two-column forms
- Condensed grid view
- Side-by-side layout for related content

## Accessibility Standards

### WCAG 2.1 AA Compliance
- All interactive elements keyboard accessible
- Proper ARIA labels for dynamic content
- Color contrast ratio minimum 4.5:1
- Focus indicators on all interactive elements
- Screen reader compatibility

### Form Accessibility
```html
<div class="field required">
    <label for="location-name" class="label">
        <span>Location Name</span>
    </label>
    <div class="control">
        <input type="text" 
               id="location-name" 
               name="name" 
               class="input-text required-entry"
               aria-required="true"
               aria-describedby="location-name-error">
        <div id="location-name-error" class="field-error" role="alert"></div>
    </div>
</div>
```

## Component Library Organization

### Customer Account Components

#### Location List Component
- Tabbed interface (All/Approved/Pending/Rejected)
- Grid/List view toggle
- Pagination controls
- Quick action buttons
- Status filters

#### Location Form Component
- Progressive disclosure for optional fields
- Real-time validation
- Address autocomplete integration
- Tag selector widget
- Image upload with preview

### Admin Interface Components

#### Location Grid Component
- Sortable columns
- Advanced filters panel
- Bulk action toolbar
- Inline editing capability
- Export options

#### Approval Modal Component
- Location details preview
- Quick approve/reject buttons
- Rejection reason field
- Email preview

## User Journey Maps

### New Customer Journey
```
1. Discovery
   - Learns about dealer program
   - Decides to register location

2. Registration
   - Creates customer account
   - Navigates to location section

3. First Submission
   - Explores form interface
   - Uses address autocomplete
   - Selects relevant tags
   - Submits for approval

4. Waiting Period
   - Checks status in account
   - Receives email updates

5. Post-Approval
   - Views approved location
   - Makes updates as needed
```

### Returning Customer Journey
```
1. Login
   - Quick access to locations

2. Management
   - Views all locations
   - Updates information
   - Adds new locations

3. Monitoring
   - Tracks approval status
   - Responds to rejections
```

## Wireframe References

### Customer Location List
```
┌─────────────────────────────────────┐
│ My Locations          [Add Location] │
├─────────────────────────────────────┤
│ All (5) | Approved (3) | Pending (2)│
├─────────────────────────────────────┤
│ ┌─────────────────────────────────┐ │
│ │ Location Name        Status   ▼ │ │
│ ├─────────────────────────────────┤ │
│ │ Store NYC           ✓ Approved  │ │
│ │ [Edit] [Delete]                 │ │
│ ├─────────────────────────────────┤ │
│ │ Store LA            ⏳ Pending   │ │
│ │ [Edit] [Delete]                 │ │
│ └─────────────────────────────────┘ │
│ < 1 2 3 ... 10 >                    │
└─────────────────────────────────────┘
```

### Location Form
```
┌─────────────────────────────────────┐
│ Add New Location                    │
├─────────────────────────────────────┤
│ Location Name *                     │
│ [____________________________]      │
│                                     │
│ Address *                           │
│ [____________________________] 🔍   │
│ └─ Google Maps suggestions          │
│                                     │
│ City *            State *    Zip *  │
│ [__________]     [____]    [_____] │
│                                     │
│ Phone *           Email *           │
│ [__________]     [______________]   │
│                                     │
│ Select Tags                         │
│ □ Retailer  □ Distributor          │
│ □ Service   □ Showroom             │
│                                     │
│ [Cancel]           [Save Location]  │
└─────────────────────────────────────┘
```

## Design Tool Integration

### Sketch/Figma Components
- Maintain component library matching Magento admin theme
- Create reusable symbols for:
  - Form elements
  - Status badges
  - Action buttons
  - Grid components
  - Modal dialogs

### Design Tokens
```json
{
  "color": {
    "primary": "#1979C3",
    "secondary": "#F26322",
    "success": "#5CB85C",
    "warning": "#F0AD4E",
    "error": "#D9534F"
  },
  "spacing": {
    "xs": "5px",
    "sm": "10px",
    "md": "20px",
    "lg": "30px",
    "xl": "40px"
  },
  "typography": {
    "fontFamily": "Open Sans, Arial, sans-serif",
    "fontSize": {
      "sm": "12px",
      "md": "14px",
      "lg": "18px",
      "xl": "24px"
    }
  }
}
```

## Integration with Implementation

### Frontend Development Guidelines
1. Use Magento UI component framework
2. Leverage existing Magento styles
3. Follow BEM naming convention
4. Implement progressive enhancement
5. Ensure cross-browser compatibility

### Component Mapping
- Customer forms → Magento UI Form Components
- Admin grids → Magento UI Listing Components
- Modals → Magento UI Modal Component
- Notifications → Magento Messages Component

This UI/UX documentation ensures consistency with Magento's design patterns while providing an optimal user experience for both customers and administrators managing dealer locations.