# HR System Styling Guide
## Jetlouge Travels - Professional Design System

### Overview
This guide outlines the unified styling system implemented across all HR modules to ensure consistency, professionalism, and excellent user experience.

## Design System Architecture

### 1. CSS Files Structure
- **`hr-unified-style.css`** - Core design system with variables, base styles, and layout components
- **`hr-component-styles.css`** - Enhanced component-specific styles for advanced UI elements

### 2. Brand Colors
```css
--jetlouge-primary: #1e3a8a    /* Deep Blue - Primary actions, headers */
--jetlouge-secondary: #3b82f6  /* Bright Blue - Secondary elements */
--jetlouge-accent: #fbbf24     /* Golden Yellow - Highlights, borders */
--jetlouge-light: #dbeafe     /* Light Blue - Backgrounds, subtle areas */
--jetlouge-success: #10b981   /* Green - Success states */
--jetlouge-warning: #f59e0b   /* Orange - Warning states */
--jetlouge-danger: #ef4444    /* Red - Error states */
--jetlouge-info: #06b6d4      /* Cyan - Information states */
```

### 3. Typography Scale
```css
--font-size-xs: 0.75rem       /* 12px - Small labels */
--font-size-sm: 0.875rem      /* 14px - Body text small */
--font-size-base: 1rem        /* 16px - Base body text */
--font-size-lg: 1.125rem      /* 18px - Large text */
--font-size-xl: 1.25rem       /* 20px - Subheadings */
--font-size-2xl: 1.5rem       /* 24px - Section headers */
--font-size-3xl: 1.875rem     /* 30px - Page titles */
```

### 4. Spacing System
```css
--spacing-xs: 0.25rem         /* 4px */
--spacing-sm: 0.5rem          /* 8px */
--spacing-md: 1rem            /* 16px */
--spacing-lg: 1.5rem          /* 24px */
--spacing-xl: 2rem            /* 32px */
--spacing-xxl: 3rem           /* 48px */
```

## Component Guidelines

### Statistics Cards
```html
<div class="card stat-card border-0 shadow-sm">
  <div class="card-body">
    <div class="d-flex align-items-center">
      <div class="stat-icon bg-jetlouge-primary text-white me-3">
        <i class="fas fa-icon-name"></i>
      </div>
      <div>
        <h3 class="fw-bold mb-0 text-jetlouge-primary">123</h3>
        <p class="text-muted mb-0 small">Metric Name</p>
      </div>
    </div>
  </div>
</div>
```

### Enhanced Buttons
```html
<button class="btn btn-primary btn-enhanced">
  <i class="fas fa-icon me-2"></i>Action Text
</button>
```

### Enhanced Tables
```html
<table class="table table-enhanced">
  <thead>
    <tr>
      <th>Column Header</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data Cell</td>
    </tr>
  </tbody>
</table>
```

### Enhanced Forms
```html
<div class="form-enhanced">
  <div class="mb-3">
    <label for="input-id" class="form-label">Field Label</label>
    <input type="text" class="form-control" id="input-id">
  </div>
</div>
```

### Enhanced Modals
```html
<div class="modal fade modal-enhanced" id="modal-id">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal Title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Modal content
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary">Cancel</button>
        <button type="button" class="btn btn-primary btn-enhanced">Save</button>
      </div>
    </div>
  </div>
</div>
```

### Enhanced Badges
```html
<span class="badge badge-enhanced bg-success">Status</span>
<span class="badge badge-enhanced bg-warning">Pending</span>
<span class="badge badge-enhanced bg-danger">Error</span>
```

## Layout Structure

### Page Header Pattern
```html
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Page Title</h2>
        <p class="text-muted mb-0">Page description</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Current Page</li>
      </ol>
    </nav>
  </div>
</div>
```

### Statistics Row Pattern
```html
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <!-- Stat card here -->
  </div>
  <!-- Repeat for other stats -->
</div>
```

### Action Cards Pattern
```html
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card action-card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-icon me-2"></i>Section Title
        </h5>
      </div>
      <div class="card-body">
        <!-- Action buttons -->
      </div>
    </div>
  </div>
</div>
```

## Animation Guidelines

### Hover Effects
- **Cards**: `translateY(-4px) scale(1.02)` with shadow enhancement
- **Buttons**: `translateY(-2px)` with shadow increase
- **Icons**: `scale(1.1) rotate(5deg)` for playful interaction

### Transitions
- **Fast**: `0.15s ease-in-out` - For small interactions
- **Base**: `0.3s ease-in-out` - For most animations
- **Slow**: `0.5s ease-in-out` - For complex state changes

### Loading States
```html
<div class="loading-enhanced">
  <div class="spinner"></div>
  Loading...
</div>
```

## Responsive Design

### Breakpoints
- **Mobile**: `max-width: 767px`
- **Tablet**: `768px - 1023px`
- **Desktop**: `min-width: 1024px`

### Mobile Adaptations
- Reduce font sizes by 10-15%
- Stack cards vertically
- Simplify navigation
- Hide decorative elements
- Increase touch targets to minimum 44px

## Accessibility Features

### Color Contrast
- All text meets WCAG 2.1 AA standards
- Interactive elements have sufficient contrast ratios

### Focus States
- All interactive elements have visible focus indicators
- Keyboard navigation is fully supported

### Screen Reader Support
- Proper ARIA labels and roles
- Semantic HTML structure
- Alt text for all images

## Best Practices

### CSS Class Naming
- Use descriptive, semantic class names
- Follow BEM methodology where appropriate
- Prefix custom classes with `hr-` or component name

### Performance
- Minimize CSS specificity conflicts
- Use CSS custom properties for theming
- Optimize for critical rendering path

### Maintenance
- Keep styles modular and reusable
- Document any custom modifications
- Test across all supported browsers

## Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Implementation Checklist

### For New Pages
- [ ] Include unified CSS files in layout
- [ ] Use page header pattern
- [ ] Apply consistent spacing
- [ ] Use enhanced components
- [ ] Test responsive behavior
- [ ] Verify accessibility compliance

### For Existing Pages
- [ ] Replace inline styles with utility classes
- [ ] Update component markup to use enhanced versions
- [ ] Ensure color consistency
- [ ] Test all interactive states
- [ ] Validate responsive design

## Common Patterns

### Success Messages
```html
<div class="alert alert-success notification-enhanced">
  <i class="fas fa-check-circle me-2"></i>
  Operation completed successfully!
</div>
```

### Error Messages
```html
<div class="alert alert-danger notification-enhanced">
  <i class="fas fa-exclamation-triangle me-2"></i>
  An error occurred. Please try again.
</div>
```

### Clock Display
```html
<div class="clock-display">
  <div class="clock-time" id="live-clock">--:--:--</div>
  <div class="clock-date" id="live-date">--</div>
</div>
```

This styling guide ensures consistency across all HR modules while maintaining the professional appearance and excellent user experience that reflects Jetlouge Travels' brand standards.
