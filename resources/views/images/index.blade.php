@extends('layouts.app')

@section('content')
<div class="home-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-badge">
            <span class="badge-pulse"></span>
            <span class="badge-text">Next-Gen AI Image Studio &bull; v2.0 Engine</span>
        </div>
        <h1 class="hero-title">
            Transform, Remove Backgrounds &amp; <span class="gradient-text">Compress with Studio AI</span>
        </h1>
        <p class="hero-subtitle">
            The all-in-one browser workspace to isolate subjects with neural AI, apply studio backdrops, resize for social &amp; IDs, and compress files down to <strong>10 KB</strong> with crystal-clear fidelity.
        </p>

        <div class="hero-trust-bar">
            <div class="trust-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <span>Instant 1-Click AI Cutout</span>
            </div>
            <div class="trust-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                <span>Exact Target KB Guarantee</span>
            </div>
            <div class="trust-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>100% Private &bull; No Watermark</span>
            </div>
            <div class="trust-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Guest Friendly &bull; Zero Signup</span>
            </div>
        </div>
    </section>

    <!-- Studio Upload Center -->
    <section class="studio-upload-section">
        <div class="studio-card">
            <!-- Studio Header Tabs -->
            <div class="studio-tabs-bar">
                <div class="studio-tab active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z"/></svg>
                    <span>Studio Editor &amp; AI Cutout</span>
                </div>
                <a href="{{ route('images.compress.index') }}" class="studio-tab tab-link">
                    <span class="tab-bolt">⚡</span>
                    <span>Smart Target KB Compressor</span>
                    <span class="tab-chip">Direct Mode</span>
                </a>
            </div>

            <div class="studio-intro-wrap">
                <div class="studio-headline-group">
                    <h2 class="studio-card-title">Upload and process image</h2>
                    <p class="studio-card-desc">Upload as a guest or sign in to permanently save your edit history and assets to your account.</p>
                </div>

                <div class="compressor-promo-card">
                    <div class="promo-content">
                        <span class="promo-badge">⚡ New Feature</span>
                        <h3>Need to reduce image size (KB)?</h3>
                        <p>Compress 2 MB+ files down to <strong>10 KB</strong>, <strong>50 KB</strong>, or <strong>100 KB</strong> with crystal-clear visual quality.</p>
                    </div>
                    <a href="{{ route('images.compress.index') }}" class="button promo-btn">Go to Compressor &rarr;</a>
                </div>
            </div>

            <!-- Upload Form -->
            <form id="studioUploadForm" class="studio-form" method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Category Selector Bar -->
                <div class="category-selector-wrapper">
                    <div class="category-label-row">
                        <span class="field-heading">1. Select Preset / Category</span>
                        <span class="field-subheading">AI optimizes segmentation and edge refinement based on your subject</span>
                    </div>
                    
                    <div class="category-pills" id="categoryPills">
                        <button type="button" class="category-pill {{ empty($selectedCategory) ? 'active' : '' }}" data-value="">
                            <span class="pill-icon">⚡</span>
                            <span class="pill-title">Auto / All</span>
                        </button>
                        <button type="button" class="category-pill {{ $selectedCategory === 'profile' ? 'active' : '' }}" data-value="profile">
                            <span class="pill-icon">👤</span>
                            <span class="pill-title">Profile Photo</span>
                        </button>
                        <button type="button" class="category-pill {{ $selectedCategory === 'product' ? 'active' : '' }}" data-value="product">
                            <span class="pill-icon">🛍️</span>
                            <span class="pill-title">E-Commerce Product</span>
                        </button>
                        <button type="button" class="category-pill {{ $selectedCategory === 'document' ? 'active' : '' }}" data-value="document">
                            <span class="pill-icon">📄</span>
                            <span class="pill-title">Document &amp; ID</span>
                        </button>
                        <button type="button" class="category-pill {{ $selectedCategory === 'social' ? 'active' : '' }}" data-value="social">
                            <span class="pill-icon">📱</span>
                            <span class="pill-title">Social Media</span>
                        </button>
                        <button type="button" class="category-pill {{ $selectedCategory === 'portfolio' ? 'active' : '' }}" data-value="portfolio">
                            <span class="pill-icon">🎨</span>
                            <span class="pill-title">Portfolio Art</span>
                        </button>
                    </div>

                    <input type="hidden" name="category" id="selectedCategoryInput" value="{{ $selectedCategory ?? '' }}">
                </div>

                <!-- Interactive Dropzone -->
                <div class="studio-dropzone-wrapper">
                    <div class="category-label-row">
                        <span class="field-heading">2. Upload Your Image</span>
                        <span class="field-subheading">Drop a file, browse, or press <strong>Ctrl + V</strong> to paste from clipboard</span>
                    </div>

                    <div class="studio-dropzone" id="studioDropzone">
                        <input type="file" name="image" id="studioFileInput" accept="image/png,image/jpeg,image/webp,image/gif" required class="studio-file-input">
                        
                        <!-- Default Idle State -->
                        <div class="dropzone-idle" id="dropzoneIdle">
                            <div class="upload-icon-circle">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <h3 class="dropzone-title">Drag &amp; drop your image here</h3>
                            <p class="dropzone-subtitle">or <span class="browse-highlight">browse file</span> from your device</p>
                            <div class="dropzone-badges">
                                <span class="file-type-badge">PNG</span>
                                <span class="file-type-badge">JPG</span>
                                <span class="file-type-badge">WEBP</span>
                                <span class="file-type-badge">GIF</span>
                                <span class="file-limit-pill">Up to 10 MB</span>
                                <span class="clipboard-pill">📋 Paste (Ctrl+V) Supported</span>
                            </div>
                        </div>

                        <!-- Pre-flight Live Preview State -->
                        <div class="dropzone-active-preview" id="dropzoneActivePreview" style="display: none;">
                            <div class="preview-stage-box">
                                <div class="checkerboard-bg">
                                    <img id="clientPreviewImg" src="" alt="Selected image preview">
                                </div>
                            </div>
                            <div class="preview-details-card">
                                <div class="preview-info-row">
                                    <div class="file-icon-tag">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                    <div class="file-meta-col">
                                        <span class="preview-filename" id="previewFileName">filename.jpg</span>
                                        <div class="preview-chips-row">
                                            <span class="preview-chip" id="previewFileSize">0 KB</span>
                                            <span class="preview-chip" id="previewDimensions">Detecting...</span>
                                            <span class="preview-chip format-chip" id="previewFormat">JPG</span>
                                            <span class="preview-chip cat-chip" id="previewCatBadge">General</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-clear-selection" id="btnClearSelection" title="Choose another file">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Change Image</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="studio-action-bar">
                    <button class="button studio-submit-btn" type="submit" id="studioSubmitBtn">
                        <span class="btn-spinner" id="btnSpinner" style="display: none;"></span>
                        <span class="btn-icon" id="btnIcon">✨</span>
                        <span class="btn-label" id="btnLabel">Launch AI Studio &amp; Preview</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <div class="studio-security-note">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Encrypted SSL transfer &bull; Safe temporary processing</span>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Quick Features Showcase Cards -->
    <section class="tools-grid-section">
        <div class="section-title-wrap">
            <span class="section-badge">Production Suite</span>
            <h2 class="section-heading">Everything You Need For Professional Visuals</h2>
            <p class="section-subtext">Powerful AI tools crafted for creators, e-commerce sellers, and job applicants.</p>
        </div>

        <div class="tools-grid">
            <!-- Tool 1 -->
            <div class="tool-card">
                <div class="tool-icon-wrapper icon-purple">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z"/></svg>
                </div>
                <h3>AI Background Removal</h3>
                <p>Neural Rembg network isolates complex hair strands, fur, transparent glass, and fine product borders in under 2 seconds.</p>
                <div class="tool-tags">
                    <span class="tool-tag">Alpha Masking</span>
                    <span class="tool-tag">Transparent PNG</span>
                    <span class="tool-tag">Fine Edge Detection</span>
                </div>
            </div>

            <!-- Tool 2 -->
            <div class="tool-card featured-tool-card">
                <div class="tool-badge-pill">⚡ Most Popular</div>
                <div class="tool-icon-wrapper icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                </div>
                <h3>Target-KB Smart Compressor</h3>
                <p>Guarantees output &le; 10 KB, 50 KB, or 100 KB for government forms, visas, and job portals without blurry artifacts.</p>
                <div class="tool-tags">
                    <span class="tool-tag">10KB / 50KB Presets</span>
                    <span class="tool-tag">WebP / JPG Engine</span>
                    <span class="tool-tag">Up to 90% Savings</span>
                </div>
                <a href="{{ route('images.compress.index') }}" class="tool-card-cta">Open Compressor &rarr;</a>
            </div>

            <!-- Tool 3 -->
            <div class="tool-card">
                <div class="tool-icon-wrapper icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </div>
                <h3>Studio Scenes &amp; Backdrops</h3>
                <p>Instantly place subjects against sleek studio grey, pure e-commerce white, natural sky, or admin-curated scenery backdrops.</p>
                <div class="tool-tags">
                    <span class="tool-tag">Studio Backdrops</span>
                    <span class="tool-tag">White Canvas</span>
                    <span class="tool-tag">Custom Backgrounds</span>
                </div>
            </div>

            <!-- Tool 4 -->
            <div class="tool-card">
                <div class="tool-icon-wrapper icon-orange">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <h3>Social &amp; ID Resizing</h3>
                <p>Resize images with aspect ratio locking for LinkedIn, Instagram square, passport standards, and bespoke pixel dimensions.</p>
                <div class="tool-tags">
                    <span class="tool-tag">Passport Dimensions</span>
                    <span class="tool-tag">Aspect Lock</span>
                    <span class="tool-tag">Lossless Output</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Before / After Demonstration -->
    <section class="demo-section">
        <div class="demo-container">
            <div class="demo-content">
                <span class="demo-badge">Real-Time AI Precision</span>
                <h2>Flawless Cutouts &bull; Hair-level Details</h2>
                <p>Experience studio-grade subject isolation with crisp contours and zero jagged edges. Whether it's an e-commerce handbag or a corporate portrait, the AI engine delivers print-ready transparency.</p>
                
                <ul class="demo-checklist">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Preserves hair strands, soft fur, and delicate highlights</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Replace with studio presets or custom scenery in 1 tap</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Zero manual lassoing or tedious pen tool masking required</span>
                    </li>
                </ul>

                <a href="#studioDropzone" class="button demo-action-btn" onclick="document.getElementById('studioFileInput').click(); return false;">
                    <span>Try With Your Photo</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>

            <div class="demo-visual-card">
                <div class="comparison-widget" id="comparisonWidget">
                    <!-- Before Layer (Original) -->
                    <div class="comp-layer comp-before">
                        <div class="sample-art original-art">
                            <div class="sample-scene">
                                <div class="scene-item sample-person">
                                    <div class="avatar-silhouette">
                                        <span class="silhouette-head"></span>
                                        <span class="silhouette-body"></span>
                                    </div>
                                    <span class="sample-tag">Original Background</span>
                                </div>
                            </div>
                        </div>
                        <span class="comp-pill pill-before">Before (Messy Background)</span>
                    </div>

                    <!-- After Layer (Studio AI Cutout) -->
                    <div class="comp-layer comp-after" id="compAfterLayer">
                        <div class="sample-art cutout-art">
                            <div class="sample-scene studio-scene">
                                <div class="scene-item sample-person isolated">
                                    <div class="avatar-silhouette isolated-silhouette">
                                        <span class="silhouette-head"></span>
                                        <span class="silhouette-body"></span>
                                    </div>
                                    <span class="sample-tag studio-tag">✨ Studio AI Cutout</span>
                                </div>
                            </div>
                        </div>
                        <span class="comp-pill pill-after">After (Studio Backdrop)</span>
                    </div>

                    <!-- Slider Handle -->
                    <input type="range" min="0" max="100" value="50" class="comp-slider" id="compSlider" aria-label="Compare Before and After">
                    <div class="comp-handle" id="compHandle">
                        <div class="handle-line"></div>
                        <div class="handle-circle">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/><polyline points="9 18 3 12 9 6"/></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/><polyline points="15 18 21 12 15 6"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Workspace Gallery -->
    <section class="workspace-gallery-section" id="recentGallery">
        <div class="gallery-header">
            <div>
                <div class="gallery-title-row">
                    <h2 class="gallery-heading">Recent Workspace Assets</h2>
                    <span class="gallery-count-pill">{{ $images->count() }} Previews</span>
                </div>
                <p class="gallery-subheading">Your recent uploaded and processed files in this active session.</p>
            </div>

            <!-- Category Filter Tabs -->
            <div class="gallery-filter-tabs">
                <a href="{{ route('images.index') }}" class="filter-tab {{ empty($selectedCategory) ? 'active' : '' }}">All</a>
                @foreach($categories as $catKey => $catLabel)
                    <a href="{{ route('images.category', $catKey) }}" class="filter-tab {{ $selectedCategory === $catKey ? 'active' : '' }}">
                        {{ $catLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($images->isNotEmpty())
            <div class="gallery-grid">
                @foreach($images as $image)
                    <article class="gallery-card">
                        <div class="card-thumb-wrap">
                            <a href="{{ route('images.show', $image) }}" class="card-thumb-link">
                                <div class="checkerboard-backdrop">
                                    <img src="{{ asset('storage/' . $image->displayPath()) }}" alt="{{ $image->original_name }}" loading="lazy">
                                </div>
                            </a>
                            <div class="card-badges-top">
                                @if($image->category)
                                    <span class="card-cat-badge">{{ ucfirst($image->category) }}</span>
                                @else
                                    <span class="card-cat-badge muted-badge">Auto</span>
                                @endif

                                @if($image->background_removed)
                                    <span class="card-ai-badge">✨ AI Cutout</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-info">
                            <a href="{{ route('images.show', $image) }}" class="card-title" title="{{ $image->original_name }}">
                                {{ $image->original_name }}
                            </a>

                            <div class="card-meta-chips">
                                <span class="meta-chip">
                                    {{ $image->width && $image->height ? "{$image->width}×{$image->height}px" : 'Auto' }}
                                </span>
                                <span class="meta-chip size-chip">
                                    {{ $image->formattedCurrentSize() }}
                                </span>
                                @if($image->savingsPercent() > 0)
                                    <span class="meta-chip savings-chip">-{{ $image->savingsPercent() }}%</span>
                                @endif
                            </div>

                            <div class="card-footer-row">
                                <span class="card-time">{{ $image->created_at ? $image->created_at->diffForHumans() : 'Recently' }}</span>
                                <div class="card-actions">
                                    <a href="{{ route('images.show', $image) }}" class="action-btn edit-btn" title="Open in Studio">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                    <a href="{{ route('images.download', $image) }}" class="action-btn download-btn" title="Download">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('images.destroy', $image) }}" onsubmit="return confirm('Delete this image from workspace?');" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete-btn" title="Delete">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-workspace-card">
                <div class="empty-icon-ring">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <h3>Your Workspace is Ready</h3>
                <p>No images uploaded yet in this session. Drag an image to the upload zone above or paste directly with Ctrl+V to start your first edit.</p>
                <div class="empty-cta-row">
                    <button type="button" class="button small empty-btn" onclick="document.getElementById('studioFileInput').click();">
                        <span>Browse Image Now</span>
                    </button>
                    <a href="{{ route('images.compress.index') }}" class="button small secondary">
                        <span>Or Try Compressor</span>
                    </a>
                </div>
            </div>
        @endif
    </section>

    <!-- How It Works 3-Step Section -->
    <section class="how-it-works-section">
        <div class="section-title-wrap">
            <span class="section-badge">Simple 3-Step Process</span>
            <h2 class="section-heading">How ImageStudio AI Works</h2>
            <p class="section-subtext">From raw image to publication-ready asset in three seamless steps.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <h3>Drop, Browse or Paste</h3>
                <p>Upload any PNG, JPG, WEBP, or GIF up to 10 MB. Press <strong>Ctrl+V</strong> to paste screenshots straight from your clipboard.</p>
            </div>
            <div class="step-card">
                <div class="step-number">02</div>
                <h3>AI Does The Heavy Lifting</h3>
                <p>Our neural model isolates subjects with microscopic edge precision, applies custom backdrops, or optimizes to your exact target KB size.</p>
            </div>
            <div class="step-card">
                <div class="step-number">03</div>
                <h3>Export &amp; Publish</h3>
                <p>Preview side-by-side and export lossless or compressed assets ready for job portals, e-commerce stores, or social feeds.</p>
            </div>
        </div>
    </section>
</div>

<!-- Interactive Client-side Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('studioDropzone');
    const fileInput = document.getElementById('studioFileInput');
    const idleState = document.getElementById('dropzoneIdle');
    const activePreview = document.getElementById('dropzoneActivePreview');
    const previewImg = document.getElementById('clientPreviewImg');
    const previewFileName = document.getElementById('previewFileName');
    const previewFileSize = document.getElementById('previewFileSize');
    const previewDimensions = document.getElementById('previewDimensions');
    const previewFormat = document.getElementById('previewFormat');
    const previewCatBadge = document.getElementById('previewCatBadge');
    const btnClearSelection = document.getElementById('btnClearSelection');
    const uploadForm = document.getElementById('studioUploadForm');
    const submitBtn = document.getElementById('studioSubmitBtn');
    const btnSpinner = document.getElementById('btnSpinner');
    const btnIcon = document.getElementById('btnIcon');
    const btnLabel = document.getElementById('btnLabel');
    const categoryInput = document.getElementById('selectedCategoryInput');
    const categoryPills = document.querySelectorAll('.category-pill');

    // Helper: format bytes
    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }

    // Helper: show preview
    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            alert('Please select a valid image file (PNG, JPG, WEBP, GIF).');
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        previewImg.src = objectUrl;
        previewFileName.textContent = file.name;
        previewFileSize.textContent = formatBytes(file.size);

        const ext = file.name.split('.').pop().toUpperCase();
        previewFormat.textContent = ext || 'IMG';

        // Load image to calculate dimensions
        const testImg = new Image();
        testImg.onload = function () {
            previewDimensions.textContent = this.naturalWidth + ' × ' + this.naturalHeight + ' px';
        };
        testImg.src = objectUrl;

        idleState.style.display = 'none';
        activePreview.style.display = 'flex';
        dropzone.classList.add('has-file');

        updateCategoryBadge();
    }

    function updateCategoryBadge() {
        const val = categoryInput.value;
        const pillMap = {
            'profile': '👤 Profile',
            'product': '🛍️ Product',
            'document': '📄 Document',
            'social': '📱 Social',
            'portfolio': '🎨 Portfolio'
        };
        previewCatBadge.textContent = pillMap[val] || '⚡ Auto / All';
    }

    // Category pill selection
    categoryPills.forEach(pill => {
        pill.addEventListener('click', function () {
            categoryPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            categoryInput.value = this.dataset.value;
            updateCategoryBadge();
        });
    });

    // File input change
    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            handleFile(this.files[0]);
        }
    });

    // Clear selection
    btnClearSelection.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.value = '';
        previewImg.src = '';
        activePreview.style.display = 'none';
        idleState.style.display = 'flex';
        dropzone.classList.remove('has-file');
    });

    // Drag and drop events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFile(e.dataTransfer.files[0]);
        }
    });

    // Clipboard Paste support (Ctrl + V)
    document.addEventListener('paste', function (e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file' && item.type.startsWith('image/')) {
                const blob = item.getAsFile();
                const container = new DataTransfer();
                container.items.add(blob);
                fileInput.files = container.files;
                handleFile(blob);
                break;
            }
        }
    });

    // Submit loading state
    uploadForm.addEventListener('submit', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            submitBtn.disabled = true;
            btnSpinner.style.display = 'inline-block';
            btnIcon.style.display = 'none';
            btnLabel.textContent = 'Uploading & Initializing AI Preview...';
        }
    });

    // Before/After comparison slider
    const compSlider = document.getElementById('compSlider');
    const compAfterLayer = document.getElementById('compAfterLayer');
    const compHandle = document.getElementById('compHandle');

    if (compSlider && compAfterLayer && compHandle) {
        function updateCompSlider() {
            const val = compSlider.value;
            compAfterLayer.style.clipPath = `polygon(${val}% 0, 100% 0, 100% 100%, ${val}% 100%)`;
            compHandle.style.left = val + '%';
        }
        compSlider.addEventListener('input', updateCompSlider);
        updateCompSlider();
    }
});
</script>
@endsection
