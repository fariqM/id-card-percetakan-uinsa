<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            background: #4facfe;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .alert {
            background: #ffe0e0;
            color: #b30000;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login ID Card UINSA</h2>

        {{-- GLOBAL ERROR (optional) --}}
        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="Enter your username">
                @error('username')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>

</body>

</html>
