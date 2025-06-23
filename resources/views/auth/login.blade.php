{{-- Jurislocator Custom Login Design --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jurislocator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #002B5B;
            --secondary-color: #C19A6B;
            --accent-color: #0056b3;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --dark-color: #001B3B;
            --light-color: #ffffff;
            --bg-color: #f8f5f1;
            --btn: #394a59;
        }
        body {
            background: var(--bg-color);
            min-height: 100vh;
            font-family: 'Roboto-Regular', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(0,43,91,0.03) 0%, rgba(193,154,107,0.07) 100%);
        }
        .login-card {
            background: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: var(--primary-color);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: var(--secondary-color);
            opacity: 0.1;
            border-radius: 50%;
        }
        .logo-section {
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        .logo-section img {
            width: 120px;
            height: auto;
            margin-bottom: 20px;
            object-fit: contain;
        }
        .login-right {
            padding: 60px 40px;
        }
        .form-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        .form-subtitle {
            color: #666;
            margin-bottom: 40px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-control {
            border: 1px solid #e1e8ed;
            border-radius: 4px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--bg-color);
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(193, 154, 107, 0.15);
            background-color: var(--light-color);
        }
        .btn-login {
            background: var(--secondary-color);
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
            color: white;
        }
        .btn-login:hover {
            background: var(--dark-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 43, 91, 0.2);
        }
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
            border-left: 4px solid var(--accent-color);
        }
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        .signup-link {
            text-align: center;
            margin-top: 30px;
        }
        .signup-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            color: var(--primary-color);
        }
        @media (max-width: 768px) {
            .login-left {
                padding: 40px 20px;
            }
            .login-right {
                padding: 40px 20px;
            }
            .form-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="login-left h-100">
                        <div class="logo-section">
                            <img src="{{ asset('user_assets/img/logo-01.png') }}" alt="Jurislocator Logo">
                            <h3 class="mb-0">Jurislocator</h3>
                        </div>
                        <div>
                            <h4 class="mb-3">Welcome Back!</h4>
                            <p class="mb-0">Access your account to manage legal services and connect with professionals.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="login-right">
                        <h2 class="form-title">Sign In</h2>
                        <p class="form-subtitle">Enter your credentials to access your account</p>
                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="username">
                                <label for="email"><i class="fas fa-user me-2"></i>Email</label>
                            </div>
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        <div class="signup-link">
                            <p>Don't have an account? <a href="{{ route('register') }}">Create Account</a></p>
                            @if (Route::has('password.request'))
                                <p class="mt-2"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
