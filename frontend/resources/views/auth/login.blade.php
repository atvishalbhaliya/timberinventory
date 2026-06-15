@extends('layouts.auth')

@section('title', 'Login - Timber Inventory')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            <div class="erp-card">
                <h1 class="h4 mb-1">Timber Inventory</h1>
                <p class="text-muted mb-4">Sign in to continue</p>

                <div id="error-alert" class="alert alert-danger d-none" role="alert"></div>
                <div id="loading-spinner" class="d-none text-center mb-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <form id="login-form">
                    <div class="mb-3">
                        <label class="form-label" for="login_id">Login ID</label>
                        <input class="form-control" id="login_id" name="login_id" type="text" autocomplete="username" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" id="remember" name="remember" type="checkbox">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <button class="btn btn-primary w-100" type="submit" id="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const errorAlert = document.getElementById('error-alert');
            const loadingSpinner = document.getElementById('loading-spinner');
            const loginBtn = document.getElementById('login-btn');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const login_id = document.getElementById('login_id').value;
                const password = document.getElementById('password').value;
                const remember = document.getElementById('remember').checked;

                // Show loading spinner and disable button
                loadingSpinner.classList.remove('d-none');
                loginBtn.disabled = true;
                errorAlert.classList.add('d-none');

                try {
                    const response = await window.axios.post('/v1/auth/login', {
                        login_id: login_id,
                        password: password,
                        remember: remember
                    });

                    if (response.data.success) {
                        // Store token
                        localStorage.setItem('auth_token', response.data.data.token);
                        localStorage.setItem('user', JSON.stringify(response.data.data.user));
                        localStorage.removeItem('timber-sidebar-navigation-v1');
                        localStorage.removeItem('timber-sidebar-navigation-v2');
                        localStorage.removeItem('timber-sidebar-navigation-v3');
                        localStorage.removeItem('timber-sidebar-navigation-v4');
                        
                        // Update axios header
                        window.axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;
                        
                        // Redirect to dashboard
                        window.location.href = '/dashboard';
                    }
                } catch (error) {
                    loadingSpinner.classList.add('d-none');
                    loginBtn.disabled = false;
                    
                    const message = error.response?.data?.message || 'Login failed. Please check your credentials.';
                    errorAlert.textContent = message;
                    errorAlert.classList.remove('d-none');
                }
            });
        });
    </script>
@endsection
