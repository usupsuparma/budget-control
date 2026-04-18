# Choices.js Implementation Standard

This document defines the mandatory standard for handling dropdowns/select elements using Choices.js to ensure UI consistency and a modern user experience.

## Mandatory Rules

1. **Default Library**: All `<select>` elements (except for very simple ones like page length) MUST use `Choices.js`.
2. **Single Instance per Element**: Every select element MUST have its own dedicated instance. Never share an instance across multiple DOM elements.
3. **Local Assets**: Always use local assets via `asset('assets/libs/choices.js/...')`. Do NOT use CDNs unless explicitly allowed for temporary debugging.
4. **Cleanup**: When using Choices.js within dynamic modals or Livewire, ensure instances are properly destroyed (`instance.destroy()`) when the component is unmounted to prevent memory leaks.

## Implementation Guide

### 1. Basic Initialization

```javascript
// Good: Individual instance
const element = document.querySelector('#my-select');
const choices = new Choices(element, {
    searchEnabled: true,
    itemSelectText: '',
    allowHTML: true,
    removeItemButton: true, // If needed
});

// Store instance if you need to manipulate it later (e.g., in a global object)
window.selectInstances = window.selectInstances || {};
window.selectInstances['my-select'] = choices;
```

### 2. Handling AJAX / Dynamic Data
When updating options dynamically, use the `setChoices` or `choices.clearStore()` methods provided by the library.

```javascript
const instance = window.selectInstances['my-select'];
instance.clearStore();
instance.setChoices(newData, 'value', 'label', true);
```

### 3. Styling Consistency
Ensure the CSS for Choices.js is included in the `@section('css')` of the blade file:
```html
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
```

## Integration with Data-Driven UI
As per our project standard, do not store business data in DOM attributes. Use the Choices.js instance to retrieve the selected value and sync it with your JavaScript data objects.
