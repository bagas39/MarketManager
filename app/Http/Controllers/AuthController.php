<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // LOGIN
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Format email tidak valid!',
            'password.required' => 'Password wajib diisi!'
        ]);

        // 2. Facade Auth untuk mengecek email & password
        if (Auth::attempt($credentials)) {
            // 3. Regenerate session
            $request->session()->regenerate();


            $user = Auth::user();

            // Redirect sesuai role
            if ($user->role === 'Gudang') return redirect()->intended('/manajemen_stok');
            if ($user->role === 'Owner') return redirect()->intended('/laporan_keuangan');
            if ($user->role === 'Supervisor') return redirect()->intended('/transaksi_penjualan');

            return redirect()->intended('/');
        }

        // 4. Feedback Error
        return back()->with('error', 'Email atau Password salah!');
    }

    // REGISTER

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        // 1. Validasi 
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:Kasir,Gudang,Supervisor,Owner' 
        ], [
            'name.required' => 'Nama lengkap wajib diisi!',
            'email.required' => 'Email wajib diisi!',
            'email.unique' => 'Email sudah terdaftar, gunakan yang lain!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 6 karakter!',
            'password.confirmed' => 'Konfirmasi password tidak cocok!'
        ]);

        // 2. Simpan ke Database & Hash Password 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 3. Feedback Sukses
        return redirect('/login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // LOGOUT

    public function logout(Request $request)
    {
        Auth::logout();

        //Regenerate 
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda berhasil logout.');
    }
}