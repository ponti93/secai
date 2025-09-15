<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Secretary - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }
        
        .subtitle {
            color: #666;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .demo-credentials {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .demo-credentials h6 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .demo-credentials p {
            margin: 0;
            font-size: 0.9rem;
            color: #424242;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #7b1fa2;
            box-shadow: 0 0 0 0.2rem rgba(123, 31, 162, 0.25);
        }
        
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 3;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            z-index: 3;
        }
        
        .password-toggle:hover {
            color: #666;
        }
        
        .btn-signin {
            background: linear-gradient(135deg, #7b1fa2, #9c27b0);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-signin:hover {
            background: linear-gradient(135deg, #6a1b9a, #8e24aa);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(123, 31, 162, 0.3);
        }
        
        .footer-text {
            text-align: center;
            color: #999;
            font-size: 0.85rem;
            margin-top: 2rem;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo and Title -->
        <div class="logo-section">
            <h1 class="logo-text">AI Secretary</h1>
            <p class="subtitle">Sign in to your account</p>
        </div>
        
        <!-- Demo Credentials -->
        <div class="demo-credentials">
            <h6><i class="bi bi-info-circle me-2"></i>Demo Credentials:</h6>
            <p><strong>Email:</strong> admin@admin.com<br>
            <strong>Password:</strong> admin123</p>
        </div>
        
        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', 'admin@admin.com') }}" required>
                </div>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" value="admin123" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-signin">
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="footer-text">
            AI Secretary System - Demo Version
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>