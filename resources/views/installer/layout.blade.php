{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
</head>
<body class="text-gray-800 bg-gray-100">
    <div class="p-6 mx-auto mt-10 max-w-2xl bg-white rounded shadow-md">
        <h1 class="mb-6 text-2xl font-bold">Installation Wizard</h1>
        @yield('content')
    </div>
</body>
</html> --}}


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - Installation Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --gray-light: #f8fafc;
            --gray-border: #e2e8f0;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .installer-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .form-input {
            transition: all 0.2s ease;
            border: 1px solid var(--gray-border);
        }
        
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary);
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .requirement-item {
            border-bottom: 1px solid var(--gray-border);
            padding: 12px 0;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 120px;
        }
        
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 20px;
            left: 60px;
            width: 60px;
            height: 2px;
            background-color: #d1d5db;
        }
        
        .step.active:not(:last-child):after {
            background-color: var(--primary);
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 8px;
            background-color: #e5e7eb;
            color: #6b7280;
        }
        
        .step.active .step-circle {
            background-color: var(--primary);
            color: white;
        }
        
        .step.completed .step-circle {
            background-color: var(--success);
            color: white;
        }
        
        .step-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 500;
        }
    </style>
</head>
<body class="flex justify-center items-center p-4 min-h-screen">
    <div class="w-full max-w-3xl">
        <div class="mb-8 text-center">
            <div class="flex justify-center items-center mb-4">
                <i class="mr-3 text-3xl text-blue-500 fas fa-hands-helping"></i>
                <h1 class="text-3xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            </div>
            <p class="text-gray-600">Installation Wizard</p>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step @if(Request::is('install/requirements')) active @elseif(Request::is('install/database') || Request::is('install/admin') || Request::is('install/complete')) completed @endif">
                    <div class="step-circle">
                        @if(Request::is('install/requirements')) 1 @else <i class="fas fa-check"></i> @endif
                    </div>
                    <span class="step-label">Requirements</span>
                </div>
                <div class="step @if(Request::is('install/database')) active @elseif(Request::is('install/admin') || Request::is('install/complete')) completed @endif">
                    <div class="step-circle">
                        @if(Request::is('install/database')) 2 @elseif(Request::is('install/admin') || Request::is('install/complete')) <i class="fas fa-check"></i> @else 2 @endif
                    </div>
                    <span class="step-label">Database</span>
                </div>
                <div class="step @if(Request::is('install/admin')) active @elseif(Request::is('install/complete')) completed @endif">
                    <div class="step-circle">
                        @if(Request::is('install/admin')) 3 @elseif(Request::is('install/complete')) <i class="fas fa-check"></i> @else 3 @endif
                    </div>
                    <span class="step-label">Admin Setup</span>
                </div>
                <div class="step @if(Request::is('install/complete')) active @endif">
                    <div class="step-circle">
                        @if(Request::is('install/complete')) 4 @else 4 @endif
                    </div>
                    <span class="step-label">Complete</span>
                </div>
            </div>
        </div>
        
        <div class="p-8 installer-card">
            @yield('content')
        </div>
        
        <div class="mt-6 text-sm text-center text-gray-500">
            {{ config('app.name') }} Installer &copy; {{ date('Y') }}
        </div>
    </div>

    <script>
        // Simple form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const inputs = this.querySelectorAll('input[required]');
                    let valid = true;
                    
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            valid = false;
                            input.classList.add('border-red-500');
                            
                            // Add error message
                            if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'error-message text-red-500 text-xs mt-1';
                                errorMsg.textContent = 'This field is required';
                                input.parentNode.appendChild(errorMsg);
                            }
                        } else {
                            input.classList.remove('border-red-500');
                            const errorMsg = input.parentNode.querySelector('.error-message');
                            if (errorMsg) {
                                errorMsg.remove();
                            }
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>