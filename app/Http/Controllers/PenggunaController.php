<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class PenggunaController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        return view('manajemen_pengguna');
    }

    public function listUsers()
    {
        $users = collect($this->db->getUsers())->map(function ($user) {
            unset($user['password']); 
            return $user;
        });
        
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $users = $this->db->getUsers();

            $exists = collect($users)->contains('username', $request->username);
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Username sudah digunakan!'], 400);
            }

            $newId = collect($users)->max('id') + 1;
            
            $users[] = [
                'id' => $newId,
                'username' => $request->username,
                'password' => $request->password,
                'nama' => $request->nama,
                'role' => $request->role
            ];

            $this->db->saveUsers($users);

            return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $users = $this->db->getUsers();
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
            
            if ($request->filled('password')) {
                $users[$index]['password'] = $request->password;
            }

            $this->db->saveUsers($users);

            return response()->json(['success' => true, 'message' => 'User berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $users = $this->db->getUsers();
            $index = collect($users)->search(fn($u) => $u['id'] == $id);

            if ($index === false) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan!'], 404);
            }

            array_splice($users, $index, 1);
            $this->db->saveUsers($users);

            return response()->json(['success' => true, 'message' => 'User berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}