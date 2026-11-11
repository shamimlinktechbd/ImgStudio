@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-split-card">
        <!-- Left Showcase Pane (Visual Branding & Social Proof) -->
        <div class="auth-showcase-pane">
            <div class="showcase-top">
                <div class="auth-brand-badge">
                    <span class="badge-dot"></span>
                    <span>AI Studio Suite &bull; Pro Level</span>
                </div>

                <h1 class="auth-showcase-heading">
                    Transform Visuals with <span class="gradient-text">Enterprise Neural AI</span>
                </h1>

                <p class="auth-showcase-desc">
                    Accelerate your visual workflow. Automate background removal, restore sharpness to blurry photos, and compress assets to exact portal limits.
                </p>

                <div class="auth-feature-list">
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m19 11-8-8-8.5 8.5a2.12 2.12 0 0 0 0 3l5 5a2.12 2.12 0 0 0 3 0L19 11Z"></path>
                                <path d="m5 2 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                                <path d="m18 13 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                            </svg>
                        </div>
                        <div class="auth-feature-content">
                            <strong>1-Click AI Cutout &amp; Backdrops</strong>
                            <span>Isolate complex hair, fur, and glass in ~1.0s onto studio backdrops.</span>
                        </div>
                    </div>

                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </div>
                        <div class="auth-feature-content">
                            <strong>AI Unblur &amp; Multi-Scale Clarity</strong>
                            <span>Restore focus and recover lost micro-contrast from soft or camera-shaken photos.</span>
                        </div>
                    </div>

                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                        </div>
                        <div class="auth-feature-content">
                            <strong>Guaranteed Target-KB Compression</strong>
                            <span>Hit 10 KB, 50 KB, or 100 KB limits for portals with zero blur or degradation.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Trust Quote -->
            <div class="auth-trust-card">
                <div class="auth-trust-metrics">
                    <div class="auth-avatars-group">
                        <span class="auth-avatar-pill av-1">SL</span>
                        <span class="auth-avatar-pill av-2">MR</span>
                        <span class="auth-avatar-pill av-3">KD</span>
                        <span class="auth-avatar-pill av-more">+50k</span>
                    </div>
                    <div class="auth-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                </div>
                <p class="auth-trust-quote">
                    &ldquo;ImageStudio cut our catalog editing time by 85%. The clarity and cutout speed are truly best in class.&rdquo;
                </p>
            </div>
        </div>

        <!-- Right Form Pane -->
        <div class="auth-form-pane">
            <div class="auth-form-header">
                <div class="header-brand-mark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z" fill="url(#auth-logo-grad)"/>
                        <circle cx="6" cy="6" r="2" fill="#818CF8"/>
                        <circle cx="18" cy="18" r="1.5" fill="#C084FC"/>
                        <defs>
                            <linearGradient id="auth-logo-grad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#4F46E5"/>
                                <stop offset="0.5" stop-color="#7C3AED"/>
                                <stop offset="1" stop-color="#DB2777"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="brand-name">ImageStudio</span>
                    <span class="brand-tag">AI</span>
                </div>
                <h2 class="auth-form-title">Sign In to Studio</h2>
                <p class="auth-form-subtitle">Enter your account credentials to access your studio workspace.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <!-- Email Input -->
                <div class="auth-input-group">
                    <label class="auth-label" for="loginEmail">Email Address</label>
                    <div class="auth-input-wrapper">
                        <input type="email" 
                               name="email" 
                               id="loginEmail" 
                               class="auth-input @error('email') auth-input-error @enderror" 
                               value="{{ old('email') }}" 
                               placeholder="name@company.com" 
                               required 
                               autofocus>
                        <span class="auth-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                    </div>
                    @error('email')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="auth-input-group">
                    <label class="auth-label" for="loginPassword">Password</label>
                    <div class="auth-input-wrapper">
                        <input type="password" 
                               name="password" 
                               id="loginPassword" 
                               class="auth-input @error('password') auth-input-error @enderror" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                               required>
                        <span class="auth-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <button type="button" class="auth-password-toggle" onclick="togglePasswordVisibility('loginPassword', this)" title="Show / hide password" aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Options Row: Remember Me -->
                <div class="auth-options-row">
                    <label class="auth-remember-label">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    <span class="auth-forgot-link" style="color: #64748b; font-size: 12px;">Protected session</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-submit-btn">
                    <span>Sign In to Studio</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>

                <!-- Security Trust Badge -->
                <div class="auth-security-badge">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>256-bit SSL encrypted &bull; Zero data sharing</span>
                </div>

                <!-- Switch Link -->
                <div class="auth-switch-prompt">
                    Don't have an account yet? 
                    <a href="{{ route('register') }}">Create account free &rarr;</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
        } else {
            input.type = 'password';
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
        }
    }
</script>
@endsection
