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
| **@yaireo** | `@yaireo/` | Tagify plugin for tags input |
| **Air Datepicker** | `air-datepicker/` | Lightweight customizable cross-browser datepicker |
| **CountUp.js** | `countup.js/` | Animates a numerical value |
| **Dual Listbox** | `dual-listbox/` | Responsive dual listbox |
| **ECharts** | `echarts/` | Powerful, interactive charting and data visualization library |
| **gmaps** | `gmaps/` | Google Maps API helper |
| **Grid.js** | `gridjs/` | Advanced table plugin |
| **Jsvectormap** | `jsvectormap/` | Interactive vector maps |
| **List.js** | `list.js/` | Search, sort, filters for HTML lists |
| **Plyr** | `plyr/` | Simple, lightweight media player |
| **Remixicon** | `remixicon/` | Open source icon set |
| **Shepherd.js** | `shepherd.js/` | Guide/App tour plugin |
| **Star Rating** | `star-rating.js/` | Zero-dependency star rating |
| **wNumb** | `wNumb/` | Number & money formatting |

## Usage Guideline
To include a library in a Blade view, use the `asset()` helper:
```html
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
```
