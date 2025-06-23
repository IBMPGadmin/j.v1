{{-- Jurislocator Custom Register Design --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Jurislocator</title>
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
        }
        body {
            background: var(--bg-color);
            min-height: 100vh;
            font-family: 'Roboto-Regular', Tahoma, Geneva, Verdana, sans-serif;
        }
        .signup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(0,43,91,0.03) 0%, rgba(193,154,107,0.07) 100%);
        }
        .signup-card {
            background: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        .signup-left {
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
        .signup-left::after {
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
        .signup-right {
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
        .btn-signup {
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
        .btn-signup:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(193, 154, 107, 0.3);
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
        .login-link {
            text-align: center;
            margin-top: 30px;
        }
        .login-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            color: var(--primary-color);
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #666;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .signup-left {
                padding: 40px 20px;
            }
            .signup-right {
                padding: 40px 20px;
            }
            .form-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="signup-left h-100">
                        <div class="logo-section">
                            <img src="{{ asset('user_assets/img/logo-01.png') }}" alt="Jurislocator Logo">
                            <h3 class="mb-0">Jurislocator</h3>
                        </div>
                        <div>
                            <h4 class="mb-3">Join Our Community!</h4>
                            <p class="mb-0">Create your account to access legal services and connect with professionals in the legal field.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="signup-right">
                        <h2 class="form-title">Create Account</h2>
                        <p class="form-subtitle">Fill in your details to get started</p>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('status') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required value="{{ old('name') }}">
                                        <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="{{ old('username') }}">
                                        <label for="username"><i class="fas fa-at me-2"></i>Username</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="{{ old('email') }}">
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                    </div>
                                    <div class="password-requirements">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Minimum 8 characters required
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                                        <label for="password_confirmation"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-signup">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        <div class="login-link">
                            <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
