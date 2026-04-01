<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private function initMasterUsers()
    {
        if (!Session::has('master_users')) {
            Session::put('master_users', [
                ['id' => 1, 'username' => 'owner', 'nama' => 'Owner', 'role' => 'Owner'],
                ['id' => 2, 'username' => 'kasir', 'nama' => 'Kasir', 'role' => 'Kasir'],
                ['id' => 3, 'username' => 'gudang', 'nama' => 'Gudang', 'role' => 'Gudang'],
                ['id' => 4, 'username' => 'spv', 'nama' => 'SPV', 'role' => 'Supervisor'],
            ]);
        }
    }

    public function index()
    {
        if (Session::has('user_role')) {
            return redirect('/'); 
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $this->initMasterUsers();

        $username = $request->username;
        $password = $request->password;

        $users = Session::get('master_users', []);
        
        $user = collect($users)->firstWhere('username', $username);

        if ($user && $password === '12345') {

            Session::put('user_name', $user['nama']);
            Session::put('user_role', $user['role']);

            $role = $user['role'];
            if ($role === 'Gudang') return redirect('/manajemen_stok');
            if ($role === 'Owner') return redirect('/laporan_keuangan');
            if ($role === 'Supervisor') return redirect('/transaksi_penjualan');
            
            return redirect('/'); 
        }

        return back()->with('error', 'Username tidak ditemukan atau Password salah. (Hint: pakai password "12345")');
    }

    public function logout()
    {
        Session::forget(['user_name', 'user_role']);
        
        return redirect('/login');
    }
}