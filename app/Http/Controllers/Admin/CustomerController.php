<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Rules\UniqueEmail;
use App\Rules\UniqueUsername;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Admin::whereNotNull('user_id');
        $query->with('customer');
        $query->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        $query->when(isset($request->email) && $request->email !== '', function ($q) use ($request) {
            return $q->where('email', 'LIKE', '%' . $request->email . '%');
        });

        $query->when(isset($request->search_key) && $request->search_key !== '', function ($q) use ($request) {

            return $q->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', '%' . $request->search_key . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $request->search_key . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $request->search_key . '%')
                    ->orWhere('username', 'LIKE', '%' . $request->search_key . '%');
            });
        });

        $query->when(isset($request->first_name) && $request->first_name !== '', function ($q) use ($request) {
            return $q->where('first_name', 'LIKE', '%' . $request->first_name . '%');
        });

        return CustomerResource::collection(executeQuery($query));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', new UniqueEmail],
            'username' => ['required', 'string', 'max:255', new UniqueUsername],
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'username' => $request->username,
                'status' => $request->status,
                'type' => $request->type,
            ]);

            if ($customer) {
                $admin = Admin::create([
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'password' => $customer->password,
                    'user_id' => $customer->id,
                    'status' => $customer->status
                ]);
                if ($admin) {
                    foreach ($request->permissions as $permission) {
                        if ($permission['status']) {
                            $permit = Permission::find($permission['id']);
                            if ($permit) {
                                UserPermission::create([
                                    'module' => $permit->module,
                                    'user_id' => $admin->id,
                                    'permission_id' => $permit->id,
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Customer created successfully!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $admin = Admin::where('id', $id)->first();
        $admin->load('permissions', 'customer');
        return new CustomerResource($admin);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', new UniqueEmail($id)],
            'username' => ['required', 'string', 'max:255', new UniqueUsername($id)],
            'password' => 'nullable|string|min:6',
        ]);
        $admin = Admin::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($admin) {
                $admin->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => $request->password ? bcrypt($request->password) : $admin->password,
                    'username' => $request->username,
                    'user_id' => $admin->user_id,
                    'status' => $request->status,
                ]);
                
                $customer = Customer::findOrFail($admin->user_id);
                $customer->update([
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'password' => $request->password ? bcrypt($request->password) : $admin->password,
                    'status' => $admin->status,
                    'type' => $request->type,
                ]);

                UserPermission::where('user_id', $admin->id)->delete();

                foreach ($request->permissions as $permission) {
                    if ($permission['status']) {
                        $permit = Permission::find($permission['id']);
                        if ($permit) {
                            UserPermission::create([
                                'module' => $permit->module,
                                'user_id' => $admin->id,
                                'permission_id' => $permit->id,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Customer updated successfully!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $customer = Customer::findOrFail($admin->user_id);
        $admin->delete();
        $customer->delete();
        
    }
    public function updateActivationStatus(Request $request, $id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $customer->status = $request->status === 0 ? 1 : 0;
            $customer->save();
        }
        $admin = Admin::find($id);
        if($admin){
            $admin->status = $request->status === 0 ? 1 : 0;
            $admin->save();
        }
        
        return response()->json(['success' => true, 'message' => 'Customer updated successfully!'], 200);
    }
}
