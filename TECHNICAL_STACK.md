# Technical Stack & Libraries

This document lists the libraries and assets available in this project to prevent redundant inclusions of CDN links or duplicated assets.

## Core Framework
- **Laravel 12.x** (PHP 8.2+)
- **Livewire 3**
- **Spatie Laravel Permission**
- **Yajra DataTables**

## Frontend Libraries (Available in `public/assets/libs/`)

Always check `public/assets/libs/` before adding any CDN links.

| Library | Directory | Description |
|---------|-----------|-------------|
| **jQuery** | `jquery/` | Version 3.7.1 |
| **Bootstrap** | `bootstrap/` | Version 5.x |
| **Choices.js** | `choices.js/` | Searchable select/multiselect |
| **SweetAlert2** | `sweetalert2/` | Beautiful, responsive alerts |
| **DataTables** | `cdn.datatables.net/` | Advanced interaction controls for HTML tables |
| **ApexCharts** | `apexcharts/` | Interactive charts |
| **Chart.js** | `chart.js/` | Simple yet flexible JS charting |
| **Swiper** | `swiper/` | Modern mobile touch slider |
| **FullCalendar** | `fullcalendar/` | JavaScript calendar |
| **Dropzone** | `dropzone/` | Drag and drop file uploads |
| **Quill** | `quill/` | Rich text editor |
| **Leaflet** | `leaflet/` | Mobile-friendly interactive maps |
| **Simplebar** | `simplebar/` | Custom scrollbars |
| **Nouislider** | `nouislider/` | Lightweight range slider |
| **Cleave.js** | `cleave.js/` | Format input content when typing |
| **Dragula** | `dragula/` | Drag and drop so simple it hurts |
| **SortableJS** | `sortablejs/` | Reorderable lists |
| **PrismJS** | `prismjs/` | Syntax highlighter |

## Usage Guideline
To include a library in a Blade view, use the `asset()` helper:
```html
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
```
