<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Helpers\PermissionHelper;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\ModulMenu;
use Dotenv\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Rule;
use SessionHandler;

class AuthorizationController extends Controller
{
    public function showLogin()
    {
        return view('authorization.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            // Check if user is active
            if (Auth::user()->status === 'Inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
            }

            $userId = Auth::user()->id; // employee.id (PK)

            $employment2 = Employment::where('employee_id', $userId)->firstOrFail(); // employee_id is now FK to employee.id

            $uplinesTopDown = $employment2->uplineEmployeesTopDown([1, 2, 3, 4]);

            $dam = array();
            foreach ($uplinesTopDown as $upline) {
                $dam[] = array(
                    "id" =>  $upline->id,
                    "employee_id" =>  $upline->employee_id,
                    "level" => $upline->upline_job_level_id,
                    "fname" => $upline->first_name,
                    "lname" => $upline->last_name
                );
            }
            session()->put('uplines_top_down', $dam);

            session()->put('department_codes', $employment2->getDepartmentCodes());
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Email atau password salah.');
    }


    // ====== ROLE PAGE ======
    public function roles()
    {
        $roles = Role::orderBy('id', 'desc')->get();
        return view('authorization.index', compact('roles'));
    }

    public function roleStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')->where(fn($query) => $query->where('guard_name', 'web')),
                ],
            ]);

            Role::create([
                'name' => trim($validated['name']),
                'guard_name' => 'web',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Role Store Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function roleUpdate(Request $request, $id)
    {
        try {
            $role = Role::find($id);
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')
                        ->where(fn($query) => $query->where('guard_name', $role->guard_name))
                        ->ignore($role->id),
                ],
            ]);

            $role->update([
                'name' => trim($validated['name']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Role Update Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function roleDelete($id)
    {
        Role::find($id)->delete();
        return response()->json(['success' => true]);
    }


    // ====== PERMISSIONS ======
    // ====== PERMISSIONS ======
    public function permissions()
    {
        $permissions = Permission::with('modul')->orderBy('id', 'desc')->get(); // Update eager load
        $moduls = ModulMenu::all(); // Ini sudah benar!
        $routePermissionKeys = PermissionHelper::routePermissionKeys();

        return view('authorization.permissions', compact('permissions', 'moduls', 'routePermissionKeys'));
    }

    public function permissionStore(Request $request)
    {
        try {
            Log::info('Permission Store Request:', $request->all());

            // Validasi dengan nama tabel yang benar: 'modul_menu' (bukan 'modul_menus')
            $routePermissionKeys = PermissionHelper::routePermissionKeys();

            $validator = FacadesValidator::make($request->all(), [
                'modul_menu' => 'required|exists:modul_menu,id', // ← PERBAIKI DI SINI
                'name' => [
                    'required',
                    'string',
                    Rule::in($routePermissionKeys),
                    Rule::unique('permissions', 'name'),
                ],
                'modul_menu_name' => 'required|string'
            ], [
                'name.in' => 'Route/Key Name harus dipilih dari permission middleware aktif di routes/web.php.'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create permission
            $permission = Permission::create([
                'modul_menu'         => $request->modul_menu,
                'name'             => $request->name,
                'modul_menu_name'  => $request->modul_menu_name,
                'guard_name'       => 'web'
            ]);

            Log::info('Permission created successfully:', ['id' => $permission->id]);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'permission' => $permission
            ]);
        } catch (Exception $e) {
            Log::error('Permission Store Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function permissionUpdate(Request $request, $id)
    {
        try {
            Log::info('Permission Update Request:', ['id' => $id, 'data' => $request->all()]);

            $permission = Permission::find($id);
            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission not found'
                ], 404);
            }

            $routePermissionKeys = PermissionHelper::routePermissionKeys();
            $allowedPermissionKeys = array_values(array_unique(array_merge(
                $routePermissionKeys,
                [$permission->name]
            )));

            $validator = FacadesValidator::make($request->all(), [
                'modul_menu' => 'required|exists:modul_menu,id',
                'name' => [
                    'required',
                    'string',
                    Rule::in($allowedPermissionKeys),
                    Rule::unique('permissions', 'name')->ignore($permission->id),
                ],
                'modul_menu_name' => 'required|string'
            ], [
                'name.in' => 'Route/Key Name harus dipilih dari permission middleware aktif di routes/web.php.'
            ]);

            if ($validator->fails()) {
                Log::error('Update Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permission->update([
                'modul_menu'       => $request->modul_menu,
                'name'             => $request->name,
                'modul_menu_name'  => $request->modul_menu_name,
            ]);

            Log::info('Permission updated successfully:', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Permission Update Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function permissionDelete($id)
    {
        $permission = Permission::find($id);

        if ($permission) {
            // Cek jika permission sedang digunakan
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission. It is assigned to roles.'
                ], 422);
            }

            $permission->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }


    // ====== ROLE PERMISSIONS ======
    public function rolePermissions($id)
    {
        $role = Role::find($id);
        $permissions = Permission::with('modul')->get();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
            'selected' => $role->permissions->pluck('name')
        ]);
    }

    public function rolePermissionsUpdate(Request $request, $id)
    {
        $role = Role::find($id);
        $role->syncPermissions($request->permissions);
        return response()->json(['success' => true]);
    }


    // ASSIGN ROLE → USER
    public function assignRole(Request $request)
    {
        $user = Employee::find($request->user_id);
        $user->syncRoles([$request->role]);
        return response()->json(['success' => true]);
    }

    public function removeUserRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:employee,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = Employee::find($request->user_id);
        $role = Role::find($request->role_id);

        if ($user && $role) {
            $user->removeRole($role->name);
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus dari role'
        ]);
    }
}
