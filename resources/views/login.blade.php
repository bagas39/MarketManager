<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Swalayan Segar</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">SWALAYAN SEGAR</h1>
            <p class="text-gray-500 text-sm mt-1">Silakan masuk untuk melanjutkan</p>
        </div>

        <form method="POST" action="{{ url('/login') }}" class="space-y-5">
            @csrf 

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none transition">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none transition">
            </div>
            
            @if(session('error'))
                <div class="text-red-500 text-sm text-center bg-red-50 p-2 rounded-md border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <button type="submit" class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 transition duration-200">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-xs text-gray-400 text-center border-t pt-4">
            Test Mock Login:<br>
            User: <b>owner, kasir, gudang, spv</b> | Pass: <b>12345</b>
        </div>
    </div>

</body>
</html>