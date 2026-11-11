@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-split-card">
        <!-- Left Showcase Pane (Visual Branding & Value Proposition) -->
        <div class="auth-showcase-pane">
            <div class="showcase-top">
                <div class="auth-brand-badge">
                    <span class="badge-dot"></span>
                    <span>Start Creating &bull; Zero Signup Cost</span>
                </div>

                <h1 class="auth-showcase-heading">
                    Unlock Studio Power with <span class="gradient-text">Zero Limitations</span>
                </h1>

                <p class="auth-showcase-desc">
                    Join thousands of creators, e-commerce sellers, and teams using ImageStudio AI to process, clarify, and optimize imagery at production speed.
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
                            <strong>Instant AI Background Removal</strong>
                            <span>Ultra-fast 1.0s neural cutout engine with transparent PNG &amp; solid backdrops.</span>
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
                            <strong>AI Unblur &amp; Clarity Restoration</strong>
                            <span>Deblur camera shake and enhance micro-contrast details on any blurry photo.</span>
                        </div>
                    </div>

                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <path d="M3 9h18"></path>
                                <path d="M9 21V9"></path>
                            </svg>
                        </div>
                        <div class="auth-feature-content">
                            <strong>Persistent Workspace &amp; History</strong>
                            <span>Save and manage all your processed assets, download anytime in full quality.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Trust Quote -->
            <div class="auth-trust-card">
                <div class="auth-trust-metrics">
                    <div class="auth-avatars-group">
                        <span class="auth-avatar-pill av-1">AJ</span>
                        <span class="auth-avatar-pill av-2">TH</span>
                        <span class="auth-avatar-pill av-3">EM</span>
                        <span class="auth-avatar-pill av-more">+50k</span>
                    </div>
                    <div class="auth-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                </div>
                <p class="auth-trust-quote">
                    &ldquo;Finally an image tool that actually hits target KB without ruining faces or quality. Absolutely love it.&rdquo;
                </p>
            </div>
        </div>

        <!-- Right Form Pane -->
        <div class="auth-form-pane">
            <div class="auth-form-header">
                <div class="header-brand-mark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z" fill="url(#reg-logo-grad)"/>
                        <circle cx="6" cy="6" r="2" fill="#818CF8"/>
                        <circle cx="18" cy="18" r="1.5" fill="#C084FC"/>
                        <defs>
                            <linearGradient id="reg-logo-grad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#4F46E5"/>
                                <stop offset="0.5" stop-color="#7C3AED"/>
                                <stop offset="1" stop-color="#DB2777"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="brand-name">ImageStudio</span>
                    <span class="brand-tag">AI</span>
                </div>
                <h2 class="auth-form-title">Create Free Account</h2>
                <p class="auth-form-subtitle">Get started with ImageStudio AI in seconds. No credit card required.</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf

                <!-- Name Input -->
                <div class="auth-input-group">
                    <label class="auth-label" for="regName">Full Name</label>
                    <div class="auth-input-wrapper">
                        <input type="text" 
                               name="name" 
                               id="regName" 
                               class="auth-input @error('name') auth-input-error @enderror" 
                               value="{{ old('name') }}" 
                               placeholder="Alex Morgan" 
                               required 
                               autofocus>
                        <span class="auth-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                    </div>
                    @error('name')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Input -->
                <div class="auth-input-group">
                    <label class="auth-label" for="regEmail">Email Address</label>
                    <div class="auth-input-wrapper">
                        <input type="email" 
                               name="email" 
                               id="regEmail" 
                               class="auth-input @error('email') auth-input-error @enderror" 
                               value="{{ old('email') }}" 
                               placeholder="alex@company.com" 
                               required>
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
                    <label class="auth-label" for="regPassword">Password (Min. 8 characters)</label>
                    <div class="auth-input-wrapper">
                        <input type="password" 
                               name="password" 
                               id="regPassword" 
                               class="auth-input @error('password') auth-input-error @enderror" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                               required>
                        <span class="auth-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <button type="button" class="auth-password-toggle" onclick="togglePasswordVisibility('regPassword', this)" title="Show / hide password" aria-label="Toggle password visibility">
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

                <!-- Confirm Password Input -->
                <div class="auth-input-group">
                    <label class="auth-label" for="regConfirmPassword">Confirm Password</label>
                    <div class="auth-input-wrapper">
                        <input type="password" 
                               name="password_confirmation" 
                               id="regConfirmPassword" 
                               class="auth-input" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                               required>
                        <span class="auth-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </span>
                        <button type="button" class="auth-password-toggle" onclick="togglePasswordVisibility('regConfirmPassword', this)" title="Show / hide password" aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-submit-btn">
                    <span>Create Free Studio Account</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>

                <!-- Security Trust Badge -->
                <div class="auth-security-badge">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>100% Free Tier &bull; No Credit Card Required</span>
                </div>

                <!-- Switch Link -->
                <div class="auth-switch-prompt">
                    Already have an account? 
                    <a href="{{ route('login') }}">Sign in here &rarr;</a>
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
