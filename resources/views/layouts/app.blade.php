<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ImageStudio AI') }} — AI Background Remover, Resizer & Smart Compressor</title>
    <meta name="description" content="Production-grade AI image studio. 1-click background removal, custom backdrops, social resizing, and high-fidelity target-KB compression.">

    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="site-body">
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ route('images.index') }}">
                <span class="brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z" fill="url(#brand-grad)"/>
                        <circle cx="6" cy="6" r="2" fill="#818CF8"/>
                        <circle cx="18" cy="18" r="1.5" fill="#C084FC"/>
                        <defs>
                            <linearGradient id="brand-grad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#4F46E5"/>
                                <stop offset="0.5" stop-color="#7C3AED"/>
                                <stop offset="1" stop-color="#DB2777"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </span>
                <span class="brand-name">ImageStudio</span>
                <span class="brand-tag">AI</span>
            </a>

            <nav class="nav">
                <a href="{{ route('images.index') }}" class="nav-link {{ request()->routeIs('images.index') ? 'active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Studio</span>
                </a>

                <a href="{{ route('images.compress.index') }}" class="nav-highlight {{ request()->routeIs('images.compress.*') ? 'active' : '' }}">
                    <span class="nav-highlight-icon">⚡</span>
                    <span>Compress Image</span>
                    <span class="nav-badge-pill">Target KB</span>
                </a>

                <a href="{{ route('images.history') }}" class="nav-link {{ request()->routeIs('images.history') ? 'active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>History</span>
                </a>

                @auth
                    @if(auth()->user()->is_admin)
                        <div class="nav-divider"></div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.backgrounds') }}" class="nav-link admin-link {{ request()->routeIs('admin.backgrounds') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2l1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
                            <span>Backdrops</span>
                        </a>
                    @endif

                    <div class="nav-divider"></div>
                    <div class="user-menu">
                        <span class="user-chip">
                            <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="user-name">{{ auth()->user()->name }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="logout-btn" title="Sign out">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="nav-divider"></div>
                    <div class="auth-actions">
                        <a href="{{ route('login') }}" class="nav-link">Sign In</a>
                        <a class="button small brand-btn" href="{{ route('register') }}">
                            <span>Get Started</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    <main class="shell">
        @if(session('status'))
            <div class="notice notice-success" role="alert">
                <div class="notice-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="notice-body">
                    <strong>{{ session('status') }}</strong>
                </div>
                <button type="button" class="notice-close" onclick="this.closest('.notice').remove();">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="notice notice-error error" role="alert">
                <div class="notice-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="notice-body">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                <button type="button" class="notice-close" onclick="this.closest('.notice').remove();">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="app-footer">
        <div class="footer-inner">
            <div class="footer-brand-col">
                <div class="footer-brand">
                    <span class="brand-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z" fill="url(#ft-grad)"/>
                            <defs>
                                <linearGradient id="ft-grad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#6366F1"/>
                                    <stop offset="1" stop-color="#EC4899"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </span>
                    <span class="brand-name">ImageStudio <strong>AI</strong></span>
                </div>
                <p class="footer-desc">Next-generation browser studio for 1-click AI background removal, studio backdrops, and guaranteed target-KB image compression.</p>
                <div class="system-status">
                    <span class="status-dot"></span>
                    <span>AI Engines &amp; Processing Operational</span>
                </div>
            </div>

            <div class="footer-links-col">
                <h4>Studio Tools</h4>
                <ul>
                    <li><a href="{{ route('images.index') }}">AI Background Remover</a></li>
                    <li><a href="{{ route('images.compress.index') }}">Smart Target KB Compressor</a></li>
                    <li><a href="{{ route('images.history') }}">Recent Workspace</a></li>
                </ul>
            </div>

            <div class="footer-links-col">
                <h4>Categories</h4>
                <ul>
                    <li><a href="{{ route('images.category', 'profile') }}">Profile Photos</a></li>
                    <li><a href="{{ route('images.category', 'product') }}">E-Commerce Products</a></li>
                    <li><a href="{{ route('images.category', 'document') }}">Documents &amp; IDs</a></li>
                    <li><a href="{{ route('images.category', 'social') }}">Social Media Assets</a></li>
                </ul>
            </div>

            <div class="footer-links-col">
                <h4>Platform</h4>
                <ul>
                    @auth
                        @if(auth()->user()->is_admin)
                            <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                            <li><a href="{{ route('admin.backgrounds') }}">Backdrop Manager</a></li>
                        @endif
                        <li><a href="{{ route('images.history') }}">My Cloud History</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                        <li><a href="{{ route('register') }}">Create Free Account</a></li>
                    @endauth
                    <li><span class="footer-meta">Python 3 + RemBG + PIL Engine</span></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} ImageStudio AI. All rights reserved. High-performance, privacy-first processing.</span>
            <div class="footer-meta-pills">
                <span class="badge-mini">Fast &bull; Private &bull; No Watermark</span>
            </div>
        </div>
    </footer>
</body>
</html>
