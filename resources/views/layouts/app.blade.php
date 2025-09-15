<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecretaryAI')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #7b1fa2;
            --primary-dark: #6a1b9a;
            --secondary-color: #9c27b0;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --background-default: #ffffff;
            --background-grey: #f8f9fa;
            --border-color: #e9ecef;
        }
        
        body {
            font-family: 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background-color: var(--background-grey);
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--background-default);
            border-right: 1px solid var(--border-color);
            width: 240px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        
        .sidebar .logo-section {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar .logo-icon {
            width: 32px;
            height: 32px;
            background-color: var(--primary-color);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .sidebar .logo-text {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--text-primary);
        }
        
        .sidebar .nav-link {
            color: var(--text-secondary);
            border-radius: 4px;
            margin: 0.125rem 0.5rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover {
            color: var(--text-primary);
            background-color: #f8f9fa;
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: var(--primary-color);
        }
        
        .sidebar .nav-link.active:hover {
            background-color: var(--primary-dark);
        }
        
        .sidebar .nav-icon {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-badge {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: auto;
        }
        
        .sidebar .user-section {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .sidebar .user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
            background-color: var(--background-grey);
        }
        
        .top-header {
            background-color: var(--background-default);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-primary);
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .search-box {
            width: 300px;
            position: relative;
        }
        
        .search-box input {
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            width: 100%;
            font-size: 0.875rem;
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            display: none;
        }
        
        .search-result-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .search-result-item:hover {
            background-color: var(--background-grey);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-title {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .search-result-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .search-result-meta {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .search-result-type {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.5rem;
        }
        
        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }
        
        .notification-btn:hover {
            background-color: #f8f9fa;
            color: var(--text-primary);
        }
        
        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .user-avatar-btn {
            background: none;
            border: none;
            padding: 0;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .user-avatar-btn img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .content-area {
            padding: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background-color: var(--background-default);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem 1rem;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            font-weight: 500;
            text-transform: none;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            font-weight: 500;
            text-transform: none;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .stats-card {
            background-color: var(--background-default);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: 100%;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .stats-subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .quick-action-btn {
            height: 100px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--background-default);
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            border-color: var(--primary-color);
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .quick-action-btn .icon {
            font-size: 1.5rem;
        }
        
        .quick-action-btn .title {
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .quick-action-btn .subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 24px;
            height: 24px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .search-box {
                width: 200px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">S</div>
                <div class="logo-text">SecretaryAI</div>
            </div>
            
            <!-- Navigation Menu -->
            <div class="nav-menu">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house-door nav-icon"></i>
                    Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('emails.*') ? 'active' : '' }}" href="{{ route('emails.index') }}">
                    <i class="bi bi-envelope nav-icon"></i>
                    Emails
                    
                </a>
                <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">
                    <i class="bi bi-calendar3 nav-icon"></i>
                    Calendar
                </a>
                <a class="nav-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" href="{{ route('meetings.index') }}">
                    <i class="bi bi-camera-video nav-icon"></i>
                    Meetings
                </a>
                <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                    <i class="bi bi-file-text nav-icon"></i>
                    Documents
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <i class="bi bi-box-seam nav-icon"></i>
                    Inventory
                </a>
                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="bi bi-receipt nav-icon"></i>
                    Expenses
                </a>
            </div>
            
            <!-- User Profile Section -->
            <div class="user-section">
                <div class="d-flex align-items-center">
                    <div class="user-avatar">JS</div>
                    <div>
                        <div class="fw-bold">John Secretary</div>
                        <div class="small text-muted">john@company.com</div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1 class="page-title">@yield('page-title', 'SecretaryAI')</h1>
                <div class="header-actions">
                    <!-- Search Box -->
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="globalSearch" placeholder="Search..." class="form-control" autocomplete="off">
                        <div id="searchResults" class="search-results"></div>
                    </div>
                    
                    <!-- Notifications -->
                    <button class="notification-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </button>
                    
                    <!-- User Avatar -->
                    <div class="dropdown">
                        <button class="user-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="bi bi-person me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('settings') }}">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @hasSection('page-actions')
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">@yield('page-title', 'Dashboard')</h1>
                    <div>
                        @yield('page-actions')
                    </div>
                </div>
                @else
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">@yield('page-title', 'Dashboard')</h1>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    <!-- Global Search Functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function performSearch(query) {
            fetch(`/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        function displaySearchResults(data) {
            const allResults = [
                ...data.emails,
                ...data.documents,
                ...data.meetings,
                ...data.events,
                ...data.expenses,
                ...data.inventory
            ];

            if (allResults.length === 0) {
                searchResults.innerHTML = '<div class="search-result-item text-center text-muted">No results found</div>';
            } else {
                searchResults.innerHTML = allResults.map(result => `
                    <div class="search-result-item" onclick="window.location.href='${result.url}'">
                        <div class="search-result-title">${result.title}</div>
                        <div class="search-result-subtitle">${result.subtitle}</div>
                        <div class="search-result-meta">
                            <span class="search-result-type">${result.type}</span>
                            ${result.date}
                        </div>
                    </div>
                `).join('');
            }

            searchResults.style.display = 'block';
        }
    });
    </script>
    
    @yield('scripts')
</body>
</html>
