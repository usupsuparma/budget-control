# 📋 DOKUMENTASI MASTER DATA SETTINGS

> **URL**: `http://127.0.0.1:8000/master`  
> **Permission Required**: `setting.master.view`  
> **Last Updated**: 2026-01-12

---

## 📑 Daftar Isi

1. [Overview](#1-overview)
2. [Employee Management](#2-employee-management)
3. [Job Position Management](#3-job-position-management)
4. [Job Level Management](#4-job-level-management)
5. [Director Management](#5-director-management)
6. [Division Management](#6-division-management)
7. [Department Management](#7-department-management)
8. [Section Management](#8-section-management)
9. [Technical Documentation](#9-technical-documentation)

---

## 1. OVERVIEW

### 1.1 Tentang Fitur
Master Data Settings adalah pusat manajemen data master untuk sistem Budget Control. Fitur ini menyediakan antarmuka terpusat untuk mengelola:
- **Employee**: Data karyawan dan kredensial login
- **Job Position**: Posisi jabatan dalam organisasi
- **Job Level**: Level jabatan (Director, Division, Department, Section)
- **Director**: Data direktur perusahaan
- **Division**: Divisi dalam organisasi
- **Department**: Departemen per divisi
- **Section**: Seksi per departemen

### 1.2 Akses Halaman
```
URL: http://127.0.0.1:8000/master
Route Name: master
Method: GET
Permission: setting.master.view
```

### 1.3 Tampilan Interface
Halaman menggunakan **Tabbed Layout** dengan:
- **Left Sidebar**: Tab navigasi vertikal (7 menu)
- **Right Content**: Konten dinamis per tab
- **Card Layout**: Pembungkus utama dengan shadow dan border

### 1.4 Layout Structure
```
┌─────────────────────────────────────────────────────┐
│  Settings / Master Data                              │
├───────────┬─────────────────────────────────────────┤
│ Employee  │  [+] Create Employee                     │
│ Job Pos.  │                                          │
│ Job Level │  ┌─────────────────────────────────┐   │
│ Director  │  │ DataTable (Server-side)         │   │
│ Division  │  │ - ID, Name, Email, Role, etc.   │   │
│ Dept.     │  │ - Edit & Delete Buttons         │   │
│ Section   │  └─────────────────────────────────┘   │
└───────────┴─────────────────────────────────────────┘
```

---

## 2. EMPLOYEE MANAGEMENT

### 2.1 Fitur Overview
Kelola data karyawan termasuk akun login, role, job position, dan uppline (atasan langsung).

### 2.2 Kolom Tabel
| Column | Description | Type |
|--------|-------------|------|
| **ID** | Employee ID | Integer |
| **Names & Email** | Full name + Email | String |
| **Job Position** | Jabatan + Job Level | Relation |
| **Role** | User role (badge) | Relation |
| **Status** | Active/Inactive (badge) | Enum |
| **Action** | Detail, Edit, Delete | Buttons |

### 2.3 Create Employee

#### Form Fields
```
┌─────────────────────────────────────────┐
│  First Name*        │  Last Name*       │
│  Employee ID*       │  Job Position*    │
│  Email*             │  Password*        │
│  Uppline            │  Role*            │
│  Status*                                │
└─────────────────────────────────────────┘
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `first_name` | Text | ✅ | Nama depan karyawan |
| `last_name` | Text | ✅ | Nama belakang karyawan |
| `employee_id` | Text | ✅ | ID unik karyawan (NIP) |
| `job_position_id` | Select | ✅ | Jabatan karyawan |
| `email` | Email | ✅ | Email untuk login |
| `password` | Password | ✅ | Password (di-bcrypt) |
| `uppline_id` | Select | ❌ | Atasan langsung (untuk approval chain) |
| `role_id` | Select | ✅ | Role (Administrator, User, etc.) |
| `status` | Select | ✅ | Active/Inactive |

#### Business Logic
1. **Employee Record**: Data utama karyawan disimpan ke tabel `employee`
2. **Employment Record**: Data employment otomatis dibuat di tabel `employment` (sync dengan job position)
3. **Password**: Di-hash menggunakan `bcrypt()`
4. **Uppline Chain**: Untuk approval workflow dinamis

#### API Endpoint
```
POST /employee/store
Content-Type: application/json

Request Body:
{
  "first_name": "John",
  "last_name": "Doe",
  "employee_id": "EMP001",
  "email": "john.doe@company.com",
  "password": "password123",
  "job_position_id": 1,
  "role_id": 2,
  "uppline_id": 5,
  "status": "Active"
}

Response:
{
  "success": true,
  "message": "Employee & Employment created successfully"
}
```

### 2.4 Edit Employee

#### Process Flow
1. Klik tombol **Edit** (icon pensil)
2. Modal muncul dengan data pre-filled
3. Update field yang diperlukan
4. Klik **Update**
5. Data tersimpan dan tabel refresh

#### API Endpoint
```
GET /employee/{id}/edit     → Fetch data
PUT /employee/{id}/update   → Update data
```

### 2.5 Delete Employee

#### Soft Delete
- Menggunakan `SoftDeletes` trait
- Record tidak dihapus permanen dari database
- Status berubah menjadi deleted
- Dapat di-restore jika diperlukan

#### API Endpoint
```
DELETE /employee/{id}

Response:
{
  "success": true,
  "message": "Employee deleted successfully"
}
```

### 2.6 View Detail Employee
Tombol dengan icon user untuk melihat detail lengkap karyawan (employment info, history, dll).

---

## 3. JOB POSITION MANAGEMENT

### 3.1 Fitur Overview
Kelola jabatan dalam organisasi yang terhubung dengan job level dan struktur organisasi.

### 3.2 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Job Position ID |
| **Job Position** | Nama jabatan |
| **Organization** | Level organisasi (Director/Division/etc.) |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 3.3 Create Job Position

#### Form Fields
```
┌──────────────────────────────────────┐
│  Job Position Name*                  │
│  Level*                              │
│  [Dynamic Organization Dropdown]*    │
└──────────────────────────────────────┘
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `job_position_name` | Text | ✅ | Nama jabatan (e.g., "Finance Manager") |
| `job_level_id` | Select | ✅ | Level jabatan |
| `structure_id` | Select (Dynamic) | ✅ | ID organisasi sesuai level |

#### Dynamic Organization Dropdown
Dropdown organization muncul secara dinamis berdasarkan **Job Level** yang dipilih:

```javascript
// Cascading Logic
Job Level: Director  → Organization: [List Directors]
Job Level: Division  → Organization: [List Divisions]
Job Level: Department → Organization: [List Departments]
Job Level: Section   → Organization: [List Sections]
```

#### Business Logic
1. Pilih **Job Level** terlebih dahulu
2. AJAX call ke `/jobPosition/organization/{level_id}`
3. Populate dropdown organization sesuai level
4. Simpan dengan `structure_id` yang dipilih

#### API Endpoint
```
POST /jobPosition/store

Request:
{
  "job_position_name": "Finance Manager",
  "job_level_id": 3,
  "structure_id": 5
}

Response:
{
  "success": true,
  "message": "Job Position created successfully"
}
```

### 3.4 Edit Job Position
1. Klik tombol **Edit**
2. Modal muncul dengan data existing
3. Update field yang diperlukan
4. Sistem otomatis load organization sesuai level
5. Klik **Update**

#### API Endpoint
```
GET /jobPosition/{id}/edit     → Fetch data
PUT /jobPosition/{id}/update   → Update data
```

### 3.5 Delete Job Position
```
DELETE /jobPosition/{id}
```

---

## 4. JOB LEVEL MANAGEMENT

### 4.1 Fitur Overview
Kelola level jabatan dalam hierarki organisasi.

### 4.2 Job Levels
Default job levels dalam sistem:
1. **Director** - Level tertinggi (Board of Directors)
2. **Division** - Level divisi
3. **Department** - Level departemen
4. **Section** - Level seksi

### 4.3 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Job Level ID |
| **Job Level Name** | Nama level |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 4.4 Create Job Level

#### Form Fields
```
┌──────────────────────────┐
│  Job Level Name*         │
│  Status*                 │
└──────────────────────────┘
```

| Field | Type | Required |
|-------|------|----------|
| `job_level_name` | Text | ✅ |
| `status` | Select | ✅ |

#### API Endpoint
```
POST /jobLevel/store

Request:
{
  "job_level_name": "Division",
  "status": "Active"
}
```

### 4.5 Edit & Delete
```
GET /jobLevel/{id}/edit
PUT /jobLevel/{id}/update
DELETE /jobLevel/{id}
```

---

## 5. DIRECTOR MANAGEMENT

### 5.1 Fitur Overview
Kelola data direktur perusahaan (Board of Directors).

### 5.2 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Director ID |
| **Director** | Nama direktur |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 5.3 Create Director

#### Form Fields
```
┌──────────────────────────┐
│  Director Name*          │
└──────────────────────────┘
```

| Field | Type | Required |
|-------|------|----------|
| `director_name` | Text | ✅ |

#### API Endpoint
```
POST /director/store

Request:
{
  "director_name": "John Smith"
}

Response:
{
  "success": true,
  "message": "Director created successfully"
}
```

### 5.4 Edit Director
```
GET /director/{id}/edit
PUT /director/{id}/update

Request:
{
  "director_name": "John Smith Jr.",
  "status": "Active"
}
```

### 5.5 Delete Director
```
DELETE /director/{id}
```

---

## 6. DIVISION MANAGEMENT

### 6.1 Fitur Overview
Kelola divisi dalam organisasi. Divisi merupakan sub dari Director.

### 6.2 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Division ID |
| **Division** | Nama divisi |
| **Director** | Direktur pembawah (relation) |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 6.3 Create Division

#### Form Fields
```
┌──────────────────────────┐
│  Division Name*          │
│  Director*               │
└──────────────────────────┘
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `division_name` | Text | ✅ | Nama divisi |
| `director_id` | Select | ✅ | Direktur pembawah |

#### Business Logic
- Setiap division **WAJIB** memiliki director
- Digunakan untuk KPI Division
- Digunakan untuk Budget Planning per division

#### API Endpoint
```
POST /division/store

Request:
{
  "division_name": "Finance & Accounting",
  "director_id": 1
}
```

### 6.4 Edit Division
```
GET /division/{id}/edit
PUT /division/{id}/update

Request:
{
  "division_name": "Finance & Accounting Dept",
  "director_id": 2,
  "status": "Active"
}
```

### 6.5 Delete Division
```
DELETE /division/{id}
```

---

## 7. DEPARTMENT MANAGEMENT

### 7.1 Fitur Overview
Kelola departemen dalam organisasi. Department merupakan sub dari Division.

### 7.2 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Department ID |
| **Department** | Nama departemen |
| **Division** | Divisi induk (relation) |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 7.3 Create Department

#### Form Fields
```
┌──────────────────────────┐
│  Department Name*        │
│  Division*               │
└──────────────────────────┘
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `department_name` | Text | ✅ | Nama departemen |
| `division_id` | Select | ✅ | Divisi induk |

#### Business Logic
- Setiap department **WAJIB** memiliki division
- Digunakan untuk KPI Department
- Digunakan untuk Budget Planning per department

#### API Endpoint
```
POST /department/store

Request:
{
  "department_name": "Accounting",
  "division_id": 1
}
```

### 7.4 Edit Department
```
GET /department/{id}/edit
PUT /department/{id}/update
```

### 7.5 Delete Department
```
DELETE /department/{id}
```

---

## 8. SECTION MANAGEMENT

### 8.1 Fitur Overview
Kelola seksi dalam organisasi. Section merupakan sub dari Department.

### 8.2 Kolom Tabel
| Column | Description |
|--------|-------------|
| **ID** | Section ID |
| **Section** | Nama seksi |
| **Department** | Departemen induk (relation) |
| **Status** | Active/Inactive |
| **Action** | Edit, Delete |

### 8.3 Create Section

#### Form Fields
```
┌──────────────────────────┐
│  Section Name*           │
│  Department*             │
└──────────────────────────┘
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `section_name` | Text | ✅ | Nama seksi |
| `department_id` | Select | ✅ | Departemen induk |

#### Business Logic
- Setiap section **WAJIB** memiliki department
- Digunakan untuk KPI Section
- Level terbawah dalam hierarki organisasi

#### API Endpoint
```
POST /section/store

Request:
{
  "section_name": "Tax & Compliance",
  "department_id": 1
}
```

### 8.4 Edit Section
```
GET /section/{id}/edit
PUT /section/{id}/update
```

### 8.5 Delete Section
```
DELETE /section/{id}
```

---

## 9. TECHNICAL DOCUMENTATION

### 9.1 Architecture Overview

```
┌─────────────────────────────────────────────────┐
│  Browser (User Interface)                       │
├─────────────────────────────────────────────────┤
│  Blade Template: Settings.blade.php             │
│  - Tab Navigation                               │
│  - Include Partial Views (employee.blade.php)   │
├─────────────────────────────────────────────────┤
│  JavaScript (jQuery + DataTables)               │
│  - AJAX Requests                                │
│  - Form Validation                              │
│  - Modal Handling                               │
├─────────────────────────────────────────────────┤
│  Routes (web.php)                               │
│  - Permission Middleware                        │
├─────────────────────────────────────────────────┤
│  Controllers                                    │
│  - MasterController (index)                     │
│  - EmployeeController, JobPositionController    │
│  - DirectorController, DivisionController       │
│  - DepartmentController, SectionController      │
├─────────────────────────────────────────────────┤
│  Models (Eloquent ORM)                          │
│  - Employee, JobPosition, JobLevel              │
│  - Director, Division, Department, Section      │
├─────────────────────────────────────────────────┤
│  Database (MySQL/MariaDB)                       │
└─────────────────────────────────────────────────┘
```

### 9.2 File Structure

```
app/
├── Http/Controllers/
│   ├── MasterController.php          # Main entry point
│   ├── EmployeeController.php        # Employee CRUD
│   ├── JobPositionController.php     # Job Position CRUD
│   ├── JobLevelController.php        # Job Level CRUD
│   ├── DirectorController.php        # Director CRUD
│   ├── DivisionController.php        # Division CRUD
│   ├── DepartmentController.php      # Department CRUD
│   └── SectionController.php         # Section CRUD
│
├── Models/
│   ├── Employee.php                  # Employee model + relations
│   ├── Employment.php                # Employment record
│   ├── JobPosition.php               # Job Position model
│   ├── JobLevel.php                  # Job Level model
│   ├── Director.php                  # Director model
│   ├── Division.php                  # Division model
│   ├── Department.php                # Department model
│   └── Section.php                   # Section model
│
resources/views/pages/settings/
├── Settings.blade.php                # Main layout (tabbed)
├── employee.blade.php                # Employee tab
├── JobPosition.blade.php             # Job Position tab
├── JobLevel.blade.php                # Job Level tab
├── director.blade.php                # Director tab
├── division.blade.php                # Division tab
├── department.blade.php              # Department tab
└── section.blade.php                 # Section tab

routes/
└── web.php                           # All routes definition
```

### 9.3 MasterController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Director;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Section;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class MasterController extends Controller
{
    /**
     * Display the Master Data Settings page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $title = 'Master Data';

        // Load all active master data
        $employees = Employee::where('status', 'Active')->get();
        $roles = Role::get();
        $jobPositions = JobPosition::where('status', 'Active')->get();
        $jobLevel = JobLevel::where('status', 'Active')->get();
        $director = Director::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();
        $division = Division::where('status', 'Active')
            ->with('director')
            ->orderBy('name', 'asc')
            ->get();
        $department = Department::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();
        $section = Section::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.settings.Settings', compact(
            'title',
            'employees',
            'roles',
            'jobPositions',
            'jobLevel',
            'director',
            'division',
            'department',
            'section'
        ));
    }
}
```

**Key Points:**
- **Single Entry Point**: `MasterController::index()` load semua data master sekaligus
- **Active Filter**: Hanya load data dengan status Active
- **Eager Loading**: Division dengan relation director
- **Compact**: Pass semua data ke view sekaligus

### 9.4 EmployeeController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    /**
     * Get employee data for DataTables
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData()
    {
        $query = Employee::with(['role', 'jobPosition'])
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'role_id',
                'job_position_id',
                'status'
            ]);

        return DataTables::of($query)
            ->addColumn('full_name', function ($row) {
                return e($row->first_name . ' ' . $row->last_name) .
                    '<br>' .
                    '<small class="text-muted">
                        <i class="bi bi-envelope me-1"></i>' .
                        e($row->email) .
                    '</small>';
            })
            ->addColumn('job_info', function ($row) {
                $jp = $row->jobPosition;
                return e($jp->job_position_name ?? '-') .
                    '<br>' .
                    '<small class="text-muted">' .
                        e($jp->job_level_name ?? '-') .
                    '</small>';
            })
            ->addColumn('roles', function ($row) {
                return '<span class="badge border border-primary text-primary">' .
                    $row->role->name .
                    '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-secondary icon-btn-sm open-detail" 
                            data-id="' . $row->id . '">
                        <i class="ri-user-line"></i>
                    </button>
                    <button class="btn btn-light-primary icon-btn-sm employee-edit-btn" 
                            data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-light-danger icon-btn-sm employee-delete-btn" 
                            data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['full_name', 'job_info', 'roles', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a new employee
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Get related data
            $jobPosition = JobPosition::findOrFail($request->job_position_id);
            $role = Role::findOrFail($request->role_id);

            // 1. Create Employee
            $employee = Employee::create([
                'employee_id'      => $request->employee_id,
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'email'            => $request->email,
                'password'         => bcrypt($request->password),
                'job_position_id'  => $jobPosition->id,
                'role_id'          => $role->id,
                'status'           => 'Active',
            ]);

            // 2. Create Employment record
            Employment::create([
                'employee_id'        => $request->employee_id,
                'organization_id'    => $jobPosition->organization->id ?? null,
                'organization_name'  => $jobPosition->organization->organization_name ?? null,
                'job_level_id'       => $jobPosition->job_level_id ?? null,
                'job_level_name'     => $jobPosition->job_level_name ?? null,
                'job_position_id'    => $jobPosition->id,
                'job_position_name'  => $jobPosition->job_position_name,
                'uppline_id'         => $request->uppline_id ?? null,
                'uppline_id_name'    => $request->uppline_name ?? null,
                'employment_status'  => 'Aktif',
                'role_id'            => $role->id,
                'role_name'          => $role->name,
                'status'             => 'Active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee & Employment created successfully'
        ]);
    }

    /**
     * Get employee data for editing
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $emp = Employee::with('employment')->findOrFail($id);
        return response()->json($emp);
    }

    /**
     * Update employee data
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $employee = Employee::findOrFail($id);
            
            // Update employee data
            $employee->update([
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'email'            => $request->email,
                'job_position_id'  => $request->job_position_id,
                'role_id'          => $request->role_id,
                'status'           => $request->status,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $employee->password = bcrypt($request->password);
                $employee->save();
            }

            // Update employment record
            $employment = Employment::where('employee_id', $employee->employee_id)->first();
            if ($employment) {
                $jobPosition = JobPosition::find($request->job_position_id);
                $employment->update([
                    'job_position_id'   => $jobPosition->id,
                    'job_position_name' => $jobPosition->job_position_name,
                    'uppline_id'        => $request->uppline_id,
                    'status'            => $request->status,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully'
        ]);
    }

    /**
     * Delete employee (soft delete)
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }
}
```

**Key Features:**
- **Server-Side DataTables**: Efficient untuk data besar
- **Transaction Safety**: Menggunakan `DB::transaction()` untuk data integrity
- **Dual Record Creation**: Employee + Employment simultaneously
- **Password Hashing**: Otomatis bcrypt password
- **Soft Delete**: Data tidak dihapus permanen
- **Eager Loading**: Load relations untuk performance

### 9.5 JobPositionController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Director;
use App\Models\Division;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Section;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JobPositionController extends Controller
{
    /**
     * Get job position data for DataTables
     */
    public function getData()
    {
        $query = JobPosition::select([
            'id',
            'job_position_name',
            'job_level_id',
            'job_level_name',
            'structure_id',
            'structure_name',
            'status'
        ]);

        return DataTables::of($query)
            ->addColumn('organization', fn($row) => $row->job_level_name ?? '-')
            ->addColumn('structure', fn($row) => $row->structure_name ?? '-')
            ->addColumn('status_badge', function ($row) {
                return $row->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm jobPosition-edit-btn" 
                            data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-light-danger icon-btn-sm jobPosition-delete-btn" 
                            data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Get organization dropdown based on job level
     * 
     * @param  int  $level_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizationByLevel($level_id)
    {
        $level = JobLevel::find($level_id);

        if (!$level) {
            return response()->json([]);
        }

        // Dynamic model selection based on level name
        switch (strtolower($level->job_level_name)) {
            case 'director':
                $data = Director::select('id', 'name')->get();
                break;
            case 'division':
                $data = Division::select('id', 'name')->get();
                break;
            case 'department':
                $data = Department::select('id', 'name')->get();
                break;
            case 'section':
                $data = Section::select('id', 'name')->get();
                break;
            default:
                $data = [];
        }

        return response()->json(['items' => $data]);
    }

    /**
     * Store new job position
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_position_name' => 'required|string|max:255',
            'job_level_id'      => 'required',
            'structure_id'      => 'required',
        ]);

        JobPosition::create([
            'job_position_name' => $validated['job_position_name'],
            'job_level_id'      => $validated['job_level_id'],
            'structure_id'      => $validated['structure_id'],
            'status'            => 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job Position created successfully'
        ]);
    }

    /**
     * Edit job position
     */
    public function edit($id)
    {
        $data = JobPosition::findOrFail($id);
        return response()->json($data);
    }

    /**
     * Update job position
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'job_position_name' => 'required|string|max:255',
            'status'            => 'required|in:Active,Inactive',
        ]);

        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job Position updated successfully'
        ]);
    }

    /**
     * Delete job position
     */
    public function destroy($id)
    {
        JobPosition::findOrFail($id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Job Position deleted successfully'
        ]);
    }
}
```

**Key Features:**
- **Dynamic Dropdown**: Organization dropdown berubah berdasarkan job level
- **Cascading Selection**: Level → Organization
- **Polymorphic-like Behavior**: Satu field `structure_id` untuk berbagai model

### 9.6 Routes Configuration

```php
// routes/web.php

Route::middleware('auth')->group(function () {
    
    // Main Master Data Page
    Route::middleware('permission:setting.master.view')
        ->get('/master', [MasterController::class, 'index'])
        ->name('master');

    // Employee Routes
    Route::prefix('employee')
        ->middleware('permission:employee.view')
        ->group(function () {
            Route::get('/data', [EmployeeController::class, 'getData'])
                ->name('employee.data');
            Route::post('/store', [EmployeeController::class, 'store'])
                ->name('employee.store');
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])
                ->name('employee.edit');
            Route::put('/{id}/update', [EmployeeController::class, 'update'])
                ->name('employee.update');
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])
                ->name('employee.destroy');
        });

    // Job Position Routes
    Route::prefix('jobPosition')
        ->middleware('permission:jobposition.view')
        ->group(function () {
            Route::get('/data', [JobPositionController::class, 'getData'])
                ->name('jobPosition.data');
            Route::get('/organization/{level_id}', 
                [JobPositionController::class, 'getOrganizationByLevel'])
                ->name('jobPosition.organization');
            Route::post('/store', [JobPositionController::class, 'store'])
                ->name('jobPosition.store');
            Route::get('/{id}/edit', [JobPositionController::class, 'edit'])
                ->name('jobPosition.edit');
            Route::put('/{id}/update', [JobPositionController::class, 'update'])
                ->name('jobPosition.update');
            Route::delete('/{id}', [JobPositionController::class, 'destroy'])
                ->name('jobPosition.destroy');
        });

    // Job Level Routes
    Route::prefix('jobLevel')
        ->middleware('permission:joblevel.view')
        ->group(function () {
            Route::get('/data', [JobLevelController::class, 'getData'])
                ->name('jobLevel.data');
            Route::post('/store', [JobLevelController::class, 'store'])
                ->name('jobLevel.store');
            Route::get('/{id}/edit', [JobLevelController::class, 'edit'])
                ->name('jobLevel.edit');
            Route::put('/{id}/update', [JobLevelController::class, 'update'])
                ->name('jobLevel.update');
            Route::delete('/{id}', [JobLevelController::class, 'destroy'])
                ->name('jobLevel.destroy');
        });

    // Director Routes
    Route::prefix('director')
        ->middleware('permission:director.view')
        ->group(function () {
            Route::get('/data', [DirectorController::class, 'getData'])
                ->name('director.data');
            Route::post('/store', [DirectorController::class, 'store'])
                ->name('director.store');
            Route::get('/{id}/edit', [DirectorController::class, 'edit'])
                ->name('director.edit');
            Route::put('/{id}/update', [DirectorController::class, 'update'])
                ->name('director.update');
            Route::delete('/{id}', [DirectorController::class, 'destroy'])
                ->name('director.destroy');
        });

    // Division Routes
    Route::prefix('division')
        ->middleware('permission:division.view')
        ->group(function () {
            Route::get('/data', [DivisionController::class, 'getData'])
                ->name('division.data');
            Route::post('/store', [DivisionController::class, 'store'])
                ->name('division.store');
            Route::get('/{id}/edit', [DivisionController::class, 'edit'])
                ->name('division.edit');
            Route::put('/{id}/update', [DivisionController::class, 'update'])
                ->name('division.update');
            Route::delete('/{id}', [DivisionController::class, 'destroy'])
                ->name('division.destroy');
        });

    // Department Routes
    Route::prefix('department')
        ->middleware('permission:department.view')
        ->group(function () {
            Route::get('/data', [DepartmentController::class, 'getData'])
                ->name('department.data');
            Route::post('/store', [DepartmentController::class, 'store'])
                ->name('department.store');
            Route::get('/{id}/edit', [DepartmentController::class, 'edit'])
                ->name('department.edit');
            Route::put('/{id}/update', [DepartmentController::class, 'update'])
                ->name('department.update');
            Route::delete('/{id}', [DepartmentController::class, 'destroy'])
                ->name('department.destroy');
        });

    // Section Routes
    Route::prefix('section')
        ->middleware('permission:section.view')
        ->group(function () {
            Route::get('/data', [SectionController::class, 'getData'])
                ->name('section.data');
            Route::post('/store', [SectionController::class, 'store'])
                ->name('section.store');
            Route::get('/{id}/edit', [SectionController::class, 'edit'])
                ->name('section.edit');
            Route::put('/{id}/update', [SectionController::class, 'update'])
                ->name('section.update');
            Route::delete('/{id}', [SectionController::class, 'destroy'])
                ->name('section.destroy');
        });
});
```

**Route Conventions:**
- **Prefix Pattern**: `/prefix/action`
- **Name Pattern**: `prefix.action`
- **Permission Middleware**: Every route group protected
- **RESTful**: GET (data/edit), POST (store), PUT (update), DELETE (destroy)

### 9.7 Database Schema

#### Employee Table
```sql
CREATE TABLE employee (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(50) UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    job_position_id INT UNSIGNED,
    role_id INT UNSIGNED,
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (job_position_id) REFERENCES job_positions(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### Employment Table
```sql
CREATE TABLE employment (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(50),
    organization_id INT UNSIGNED NULL,
    organization_name VARCHAR(255) NULL,
    job_level_id INT UNSIGNED NULL,
    job_level_name VARCHAR(100) NULL,
    job_position_id INT UNSIGNED,
    job_position_name VARCHAR(255),
    uppline_id INT UNSIGNED NULL,
    uppline_id_name VARCHAR(255) NULL,
    employment_status VARCHAR(50),
    role_id INT UNSIGNED,
    role_name VARCHAR(100),
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    FOREIGN KEY (job_position_id) REFERENCES job_positions(id),
    FOREIGN KEY (uppline_id) REFERENCES employee(id)
);
```

#### Job Positions Table
```sql
CREATE TABLE job_positions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    job_position_name VARCHAR(255),
    job_level_id INT UNSIGNED,
    job_level_name VARCHAR(100),
    structure_id INT UNSIGNED,
    structure_name VARCHAR(255),
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (job_level_id) REFERENCES job_levels(id)
);
```

#### Job Levels Table
```sql
CREATE TABLE job_levels (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    job_level_name VARCHAR(100),
    status ENUM('Active', 'Inactive'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Directors Table
```sql
CREATE TABLE directors (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Divisions Table
```sql
CREATE TABLE divisions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    director_id INT UNSIGNED,
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (director_id) REFERENCES directors(id)
);
```

#### Departments Table
```sql
CREATE TABLE departments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    division_id INT UNSIGNED,
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (division_id) REFERENCES divisions(id)
);
```

#### Sections Table
```sql
CREATE TABLE sections (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    department_id INT UNSIGNED,
    status ENUM('Active', 'Inactive'),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

### 9.8 Model Relationships

#### Employee Model
```php
class Employee extends Authenticatable
{
    use HasRoles, Notifiable, SoftDeletes;

    // Relationships
    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }

    public function employment()
    {
        return $this->hasOne(Employment::class, 'employee_id', 'id');
    }

    // Uppline relationship (self-referencing)
    public function uppline()
    {
        return $this->belongsTo(Employee::class, 'uppline_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'uppline_id');
    }
}
```

#### JobPosition Model
```php
class JobPosition extends Model
{
    use SoftDeletes;

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'job_position_id');
    }

    // Polymorphic-like relationship untuk structure
    public function structure()
    {
        // Based on job_level_name, return Director/Division/Department/Section
    }
}
```

#### Division Model
```php
class Division extends Model
{
    use SoftDeletes;

    public function director()
    {
        return $this->belongsTo(Director::class, 'director_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'division_id');
    }

    public function kpiDivisions()
    {
        return $this->hasMany(KPIDivision::class, 'division_id');
    }
}
```

#### Department Model
```php
class Department extends Model
{
    use SoftDeletes;

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'department_id');
    }

    public function kpiDepartments()
    {
        return $this->hasMany(KPIDepartment::class, 'department_id');
    }
}
```

#### Section Model
```php
class Section extends Model
{
    use SoftDeletes;

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function kpiSections()
    {
        return $this->hasMany(KPISection::class, 'section_id');
    }
}
```

### 9.9 JavaScript Implementation

#### DataTables Initialization
```javascript
// Employee DataTable
$('#employeeTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '/employee/data',
        type: 'GET'
    },
    columns: [
        { data: 'id', name: 'id' },
        { data: 'full_name', name: 'full_name' },
        { data: 'job_info', name: 'job_info' },
        { data: 'roles', name: 'roles' },
        { data: 'status_badge', name: 'status_badge' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
    ],
    order: [[0, 'desc']]
});
```

#### AJAX Form Submission
```javascript
// Create Employee
$('#btnCreateEmployee').on('click', function(e) {
    e.preventDefault();
    
    let formData = $('#employeeCreateForm').serialize();
    
    $.ajax({
        url: '/employee/store',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#createEmployee').modal('hide');
                $('#employeeTable').DataTable().ajax.reload();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to create employee'
            });
        }
    });
});
```

#### Dynamic Cascading Dropdown
```javascript
// Job Level → Organization Dropdown
$('select[name="job_level_id"]').on('change', function() {
    let levelId = $(this).val();
    
    if (levelId) {
        $.ajax({
            url: '/jobPosition/organization/' + levelId,
            type: 'GET',
            success: function(response) {
                let options = '<option value="" disabled selected>-- Select Organization --</option>';
                
                response.items.forEach(function(item) {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                
                $('#dynamicOrganization').html(`
                    <div class="col-12">
                        <label class="form-label">Organization</label>
                        <select name="structure_id" class="form-select" required>
                            ${options}
                        </select>
                    </div>
                `);
            }
        });
    }
});
```

### 9.10 Permission System

#### Required Permissions
```php
// Permission seeder
$permissions = [
    'setting.master.view',
    'employee.view',
    'employee.create',
    'employee.edit',
    'employee.delete',
    'jobposition.view',
    'jobposition.create',
    'jobposition.edit',
    'jobposition.delete',
    'joblevel.view',
    'joblevel.create',
    'joblevel.edit',
    'joblevel.delete',
    'director.view',
    'director.create',
    'director.edit',
    'director.delete',
    'division.view',
    'division.create',
    'division.edit',
    'division.delete',
    'department.view',
    'department.create',
    'department.edit',
    'department.delete',
    'section.view',
    'section.create',
    'section.edit',
    'section.delete',
];
```

#### Checking Permissions
```php
// In controller
if (!auth()->user()->can('employee.create')) {
    abort(403, 'Unauthorized');
}

// In blade
@can('employee.create')
    <button>Create Employee</button>
@endcan
```

### 9.11 Security Best Practices

1. **CSRF Protection**: Semua form POST/PUT/DELETE include CSRF token
2. **Input Validation**: Validasi di controller sebelum store/update
3. **XSS Prevention**: Escape output dengan `e()` helper atau `{{ }}`
4. **SQL Injection**: Menggunakan Eloquent ORM (prepared statements)
5. **Password Hashing**: Otomatis bcrypt semua password
6. **Soft Deletes**: Data tidak dihapus permanen
7. **Permission Check**: Setiap route dilindungi middleware permission

### 9.12 Performance Optimization

1. **Server-Side DataTables**: Efficient untuk ribuan records
2. **Eager Loading**: `with()` untuk load relations
3. **Index Database**: Primary key, foreign key, unique constraints
4. **Select Specific Columns**: Tidak load semua columns
5. **Pagination**: DataTables otomatis pagination
6. **Caching**: Role & Permission di-cache oleh Spatie

---

## 10. TROUBLESHOOTING

### 10.1 Common Issues

#### Issue: DataTable tidak load data
**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check permission
# Pastikan user punya permission yang sesuai
```

#### Issue: Modal tidak muncul
**Solution:**
```javascript
// Pastikan Bootstrap JS sudah loaded
// Check console untuk error JavaScript
// Pastikan CSRF token sudah ada di meta tag
```

#### Issue: Form submit tidak berhasil
**Solution:**
```php
// Check validation rules
// Check permission
// Check network tab di browser developer tools
// Check Laravel logs: storage/logs/laravel.log
```

### 10.2 Debugging Tips

1. **Check Laravel Logs**: `storage/logs/laravel.log`
2. **Browser Console**: F12 → Console tab
3. **Network Tab**: Monitor AJAX requests
4. **Database Queries**: Enable query log
5. **Permission**: `php artisan permission:cache-reset`

---

## 11. BEST PRACTICES

### 11.1 Development
- ✅ Selalu validasi input di controller
- ✅ Gunakan DB transaction untuk operasi multi-step
- ✅ Implement soft deletes untuk data penting
- ✅ Eager load relations untuk performa
- ✅ Gunakan route names, jangan hardcode URL

### 11.2 Security
- ✅ Validasi permission di setiap action
- ✅ Escape semua output user
- ✅ Hash password dengan bcrypt
- ✅ Gunakan CSRF protection
- ✅ Validasi di backend, jangan percaya client

### 11.3 UI/UX
- ✅ Feedback ke user (success/error message)
- ✅ Confirmation sebelum delete
- ✅ Loading indicator untuk AJAX
- ✅ Disable button saat submit (prevent double click)
- ✅ Responsive design

---

## 12. CHANGELOG

| Date | Version | Changes |
|------|---------|---------|
| 2026-01-12 | 1.0.0 | Initial documentation |

---

## 13. AUTHOR & MAINTENANCE

**Maintained by**: Development Team  
**Project**: Budget Control  
**Framework**: Laravel 12.x  
**Last Review**: 2026-01-12

---

**END OF DOCUMENTATION**
