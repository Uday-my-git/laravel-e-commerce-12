<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function listing(Request $request)
    {
        $users = User::orderBy('id', 'asc');

        if (filled($request->search)) {
            $search = trim($request->search);

            if (is_numeric($search)) {
                // this search behalf on id params
                $users = $users->where('id', (int)$search)->limit(1);
                $users->first();
            } else {
                $users = $users->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            } 
        }
        
        $users = $users->paginate(10);

        return view('admin.users.listing', compact('users'));
    }

    public function viewUserData($id)
    {
        $role = '';
        $userData = User::find($id);

        if (!$userData) {
            return response()->json(['status' => false, 'msg' => 'User not found or data not found !!']);
        }

        if ($userData->role == 2) {
            $role = '<span class="badge bg-primary px-3 py-2 fs-6">Admin</span>';
        } else {
            $role = '<span class="badge bg-success px-3 py-2 fs-6">User</span>';
        }

        if ($userData->status == 1) {
            $userStatus = '<span class="badge bg-success px-3 py-2 fs-6">Active</span>';
        } else {
            $userStatus = '<span class="badge bg-danger px-3 py-2 fs-6">Deactive</span>';
        }
       
        return response()->json([
            'status' => true, 
            'userData' => $userData,
            'role' => $role,
            'userStatus' => $userStatus
        ]);
    }

    public function changeStatus(Request $request)
    {
        $user = User::find($request->user_id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['success'=>'Status change successfully.']);
    }

    public function create()
    {
        return view('admin.users.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required',
            'email'  => 'required|email|unique:users',
            'phone' => 'required|min:10|max:12',
            'role'   => 'required',
        ]);

        $users = new User();
        $users->name = $request->name;
        $users->email = $request->email;
        $users->phone = $request->phone;
        $users->role = $request->role;
        $users->status = $request->status;
        $users->password = $request->password;
        $users->save();

        // $request->session()->flash('success', 'User inserted successfully...');
        return redirect()->route('users.listing')->with('success', 'User inserted successfully...');
    }

    public function edit($id)
    {
        $users['users'] = User::find($id);

        if (!$users['users'])  return redirect()->route('users.listing')->with('error', 'Invlaid entry or id error getting??');

        return view('admin.users.edit', $users);
    }

    public function update(Request $request, $id)
    {
        $users = User::find($id);

        if (!$users)  return redirect()->route('users.listing')->with('error', 'Invlaid entry or updateing data error...');

        $request->validate([
            'name'   => 'required',
            'email'  => 'required|unique:users,email,'.$id.',id',
            'phone' => 'required|min:10|max:12',
            'role'   => 'required',
        ]);

        $users->name = $request->name;
        $users->email = $request->email;
        $users->phone = $request->phone;
        $users->role = $request->role;
        $users->status = $request->status;
        $users->password = $request->password;
        $users->save();

        // $request->session()->flash('success', 'User updated successfully...');
        return redirect()->route('users.listing')->with('success', 'User updated successfully...');
    }

    public function destroy($id)
    {
        $users = User::find($id);

        if (!$users)  return redirect()->route('users.listing')->with('error', 'Invlaid entry or error for deleting data??');

        $users->delete();

        return redirect()->route('users.listing')->with('success', 'User Deleted Successfully...');
    }



}
