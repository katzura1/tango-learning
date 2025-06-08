<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Usercontroller extends Controller
{
    public function index()
    {
        // Tampilkan halaman, misal inertia('Users/Index')
        return inertia('users/Index');
    }

    public function list()
    {
        // Untuk fetch data user via AJAX
        $users = User::select('id', 'name', 'email', 'role')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'role'     => 'required',
            'password' => 'required|min:6',
        ]);
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name'  => 'required',
            'email' => "required|email|unique:users,email,$id",
            'role'  => 'required',
        ]);
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->back();
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
