<?php

namespace App\Http\Controllers\Admin;

use App\Enumeration\PermissionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->with('permissions')->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$admin->status) {
            throw ValidationException::withMessages([
                'email' => 'The provided account is not active.',
            ]);
        }

        $admin->token = $admin->createToken('web')->plainTextToken;

        DB::table('personal_access_tokens')->where('tokenable_id', $admin->id)
            ->where('tokenable_type', 'App\Models\Admin')
            ->whereNull('lfm_token')
            ->update([
                'lfm_token' => bcrypt($admin->token)
            ]);

        return new AuthResource($admin);
    }
    
    public function user(Request $request)
    {
        return $request->user();
    }
    
    public function logout() {
        Auth::guard('admin')->user()->currentAccessToken()->delete();
    }
    
    public function getAuthUserPermission()
    {
        return UserPermission::where('user_id',Auth::guard('admin')->user()->id)->get();
    }
    public function getCustomerPermissions()
    {
        return Permission::where('type', PermissionType::$Customer)->get();
    }
}
