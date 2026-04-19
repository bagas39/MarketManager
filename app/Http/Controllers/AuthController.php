<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\JsonDataService;

class AuthController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
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
        $username = $request->username;
        $password = $request->password;

        $users = $this->db->getUsers();
        
        $user = collect($users)->firstWhere('username', $username);

        if ($user && isset($user['password']) && $user['password'] === $password) {
            
            Session::put('user_name', $user['nama']);
            Session::put('user_role', $user['role']);

            $role = $user['role'];
            if ($role === 'Gudang') return redirect('/manajemen_stok');
            if ($role === 'Owner') return redirect('/laporan_keuangan');
            if ($role === 'Supervisor') return redirect('/transaksi_penjualan');
            
            return redirect('/'); 
        }

        return back()->with('error', 'Username tidak ditemukan atau Password salah.');
    }

    public function logout()
    {
        Session::forget(['user_name', 'user_role']);
        return redirect('/login');
    }
}