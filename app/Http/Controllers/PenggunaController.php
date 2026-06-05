<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index() { return view('manajemen_pengguna'); }

    public function listUsers()
    {
        // BEST PRACTICE: Ambil kolom yang dibutuhkan saja
        $users = User::select('id', 'email', 'name', 'role')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'username' => $u->email, 
                'nama' => $u->name,      
                'role' => $u->role
            ];
        });
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|email|unique:users,email',
                'nama'     => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'role'     => 'required|in:Kasir,Gudang,Supervisor,Owner'
            ], [
                'username.unique' => 'Username/Email sudah digunakan!'
            ]);

            User::create([
                'name' => $validated['nama'],
                'email' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role']
            ]);

            return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'username' => 'required|email|unique:users,email,'.$id,
                'nama'     => 'required|string|max:255',
                'password' => 'nullable|string|min:6',
                'role'     => 'required|in:Kasir,Gudang,Supervisor,Owner'
            ], [
                'username.unique' => 'Username/Email sudah dipakai user lain!'
            ]);

            $user->name = $validated['nama'];
            $user->email = $validated['username'];
            $user->role = $validated['role'];
            
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            
            $user->save();

            return response()->json(['success' => true, 'message' => 'User berhasil diperbarui!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            User::destroy($id);
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus user.'], 500);
        }
    }
}