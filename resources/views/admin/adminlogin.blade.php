<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-image: url('/imgs/wmsu_pic.jpg');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <h2 class="mb-6 text-3xl font-bold text-center text-gray-800">ADMIN</h2>
        
        @if ($errors->any())
            <div class="mb-4 text-red-700">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                <div class="relative flex items-center">
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required 
                        class="w-full p-2 pl-10 border border-red-700 rounded-md focus:outline-none focus:ring focus:ring-red-300">
                    <span class="absolute left-3 text-red-700">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
            </div>
            
            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <div class="relative flex items-center">
                    <input type="password" id="password" name="password" required 
                        class="w-full p-2 pl-10 border border-red-700 rounded-md focus:outline-none focus:ring focus:ring-red-300">
                    <span class="absolute left-3 text-red-700">
                        <i class="fas fa-lock"></i>
                    </span>
                    <button type="button" onclick="togglePassword()" 
                        class="absolute right-3 text-red-700">
                        <i id="toggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full p-2 text-white bg-red-700 rounded-md hover:bg-red-800">Login</button>
        </form>
    </div>
    
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.getElementById("toggleIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }

        window.onload = function() {
            document.getElementById("username").value = "";
            document.getElementById("password").value = "";
        }
    </script>
</body>
</html>