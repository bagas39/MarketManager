<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PenggunaController extends Controller
{
    private function getMasterUsers()
    {
        if (!Session::has('master_users')) {
            Session::put('master_users', [
                ['id' => 1, 'username' => 'owner', 'nama' => 'Owner', 'role' => 'Owner'],
                ['id' => 2, 'username' => 'kasir', 'nama' => 'Kasir', 'role' => 'Kasir'],
                ['id' => 3, 'username' => 'gudang', 'nama' => 'Gudang', 'role' => 'Gudang'],
                ['id' => 4, 'username' => 'spv', 'nama' => 'SPV', 'role' => 'Supervisor'],
            ]);
        }
        return Session::get('master_users');
    }

    public function index()
    {
        return view('manajemen_pengguna');
    }

    public function listUsers()
    {
        $users = $this->getMasterUsers();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $users = $this->getMasterUsers();

            $exists = collect($users)->contains('username', $request->username);
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Username sudah digunakan!'], 400);
            }

            $newId = collect($users)->max('id') + 1;
            
            $newUser = [
                'id' => $newId,
                'username' => $request->username,
                'nama' => $request->nama,
                'role' => $request->role
            ];

            $users[] = $newUser;
            Session::put('master_users', $users);

            return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $users = $this->getMasterUsers();
            $index = collect($users)->search(fn($u) => $u['id'] == $id);

            if ($index === false) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan!'], 404);
            }

            $usernameExists = collect($users)->contains(function ($u) use ($id, $request) {
                return $u['username'] === $request->username && $u['id'] != $id;
            });

            if ($usernameExists) {
                return response()->json(['success' => false, 'message' => 'Username sudah dipakai user lain!'], 400);
            }

            $users[$index]['username'] = $request->username;
            $users[$index]['nama'] = $request->nama;
            $users[$index]['role'] = $request->role;

            Session::put('master_users', $users);

            return response()->json(['success' => true, 'message' => 'User berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $users = $this->getMasterUsers();
            $index = collect($users)->search(fn($u) => $u['id'] == $id);

            if ($index === false) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan!'], 404);
            }

            array_splice($users, $index, 1);
            Session::put('master_users', $users);

            return response()->json(['success' => true, 'message' => 'User berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}