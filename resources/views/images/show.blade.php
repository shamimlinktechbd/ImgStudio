@extends('layouts.app')

@section('content')
    <section class="editor-grid">
        <!-- Preview Stage -->
        <div class="preview-card">
            <div class="preview-toolbar">
                <div class="preview-badges">
                    <span class="badge" id="currentDimensionBadge">
                        {{ $image->resize_width ?: $image->width }} &times; {{ $image->resize_height ?: $image->height }} px
                    </span>
                    <span class="badge" id="bgRemovedBadge" style="@if(!$image->background_removed) display: none; @endif background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff;">✨ BG Removed</span>
                    <span class="badge" id="unblurBadge" style="@if($image->last_action !== 'unblurred') display: none; @endif background: linear-gradient(135deg, #0284c7, #06b6d4); color: #ffffff;">🔍 Unblurred &amp; Clear</span>
                    <span class="badge" id="backdropSyncBadge" style="display: none; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600;">✓ Style Applied</span>
                    @if($image->last_action === 'compressed')
                        <span class="badge success">⚡ {{ $image->formattedCurrentSize() }}</span>
                        @if($image->savingsPercent())
                            <span class="badge savings-pill">-{{ $image->savingsPercent() }}%</span>
                        @endif
                    @elseif($image->processed_path)
                        <span class="badge success">Processed ({{ $image->formattedCurrentSize() }})</span>
                    @else
                        <span class="badge muted">Original ({{ $image->formattedSize() }})</span>
                    @endif
                    <span class="badge info" id="targetDimensionBadge" style="display: none;">
                        Target: <span id="targetSizeText"></span>
                    </span>
                </div>

                <div class="preview-controls">
                    <button type="button" class="btn-tool" id="btnToggleOriginal" title="Toggle between original and processed" style="@if(!$image->processed_path) display: none; @endif">
                        <span id="toggleText">&#128065; View Original</span>
                    </button>
                    <button type="button" class="btn-tool" id="btnZoomOut" title="Zoom Out">&minus;</button>
                    <span class="zoom-level" id="zoomLevel">100%</span>
                    <button type="button" class="btn-tool" id="btnZoomIn" title="Zoom In">&plus;</button>
                    <button type="button" class="btn-tool" id="btnZoomReset" title="Fit to screen">Fit</button>
                </div>
            </div>

            <div class="preview-stage" id="previewStage">
                <div class="preview-canvas-wrapper" id="canvasWrapper">
                    <img id="mainPreviewImage" 
                         src="{{ ($cutoutUrl && $image->background_removed) ? $cutoutUrl : $imageUrl }}" 
                         data-processed-src="{{ $imageUrl }}" 
                         data-original-src="{{ $originalUrl }}" 
                         data-cutout-src="{{ $cutoutUrl }}"
                         data-bg-removed="{{ $image->background_removed ? '1' : '0' }}"
                         data-current-bg="{{ $image->background_category ?: 'original' }}"
                         alt="{{ $image->original_name }}"
                         loading="eager"
                         style="@if($image->background_removed)
                            @if($image->background_category === 'white') background-color: #ffffff;
                            @elseif($image->background_category === 'studio') background-color: #374151;
                            @elseif($image->background_category === 'sky') background-color: #38bdf8;
                            @elseif($image->background_category === 'forest') background-color: #15803d;
                            @elseif($image->background_asset_id && isset($backgroundUrls[$image->background_asset_id])) background-image: url('{{ $backgroundUrls[$image->background_asset_id] }}'); background-size: cover; background-position: center;
                            @else background-color: transparent;
                            @endif
                         @endif">
                    <div id="stageOverlayLoader" class="stage-overlay-loader" style="display: none;">
                        <div class="loader-spinner-ring"></div>
                        <strong id="stageLoaderTitle">Removing background with AI...</strong>
                        <span id="stageLoaderSubtitle">Applying studio neural cutout (~1s)</span>
                    </div>
                    <div id="previewFallback" style="display: none; text-align: center; padding: 40px; color: var(--muted);">
                        <div style="font-size: 40px; margin-bottom: 12px;">🖼️</div>
                        <p style="font-weight: 600; margin-bottom: 8px; color: var(--heading);">Image preview failed to display</p>
                        <p style="font-size: 13px; margin-bottom: 16px;">Browser cache might still hold an old request or the file is reloading.</p>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload(true);">Hard Reload Page (Ctrl+F5)</button>
                    </div>
                </div>
            </div>

            <div class="preview-footer">
                <div class="file-info">
                    <span class="file-name" title="{{ $image->original_name }}">{{ $image->original_name }}</span>
                    <div class="size-comparison-tag">
                        @if($image->processed_path && $image->processed_path !== $image->original_path)
                            <span class="size-before">{{ $image->formattedSize() }}</span>
                            <span class="size-arrow">&rarr;</span>
                            <span class="size-after">{{ $image->formattedCurrentSize() }}</span>
                            @if($image->savingsPercent())
                                <span class="savings-tag">-{{ $image->savingsPercent() }}%</span>
                            @endif
                        @else
                            <span class="file-size">{{ $image->formattedSize() }}</span>
                        @endif
                    </div>
                </div>
                <div class="preview-hint">
                    <span>💡 Tip: Use <strong>Remove BG</strong> to isolate subject, <strong>Resize</strong> for custom dimensions, or <strong>Compress</strong> for exact KB limits.</span>
                </div>
            </div>
        </div>

        <!-- Settings Sidebar Panel -->
        <aside class="panel editor-panel">
            <div class="panel-header-row">
                <h2>Studio Editor</h2>
                <a href="{{ route('images.index') }}" class="back-link">&larr; Upload New</a>
            </div>

            <dl class="meta-box">
                <div>
                    <dt>Original Size</dt>
                    <dd>{{ $image->width }} &times; {{ $image->height }} px</dd>
                </div>
                @if($image->resize_width && $image->resize_height)
                    <div>
                        <dt>Current Output</dt>
                        <dd><strong>{{ $image->resize_width }} &times; {{ $image->resize_height }} px</strong></dd>
                    </div>
                @endif
                <div>
                    <dt>Format</dt>
                    <dd>{{ strtoupper($image->processed_format ?: pathinfo($image->original_name, PATHINFO_EXTENSION)) }}</dd>
                </div>
                <div>
                    <dt>Category</dt>
                    <dd>{{ $image->category ?: 'General' }}</dd>
                </div>
            </dl>

            @php
                $activeTab = 'remove_bg';
                if ($image->last_action === 'unblurred') {
                    $activeTab = 'unblur';
                } elseif ($image->last_action === 'compressed') {
                    $activeTab = 'compress';
                } elseif ($image->last_action === 'processed' && $image->resize_width && !$image->background_removed) {
                    $activeTab = 'resize';
                }
            @endphp

            <!-- 4-Tool Switcher Tabs -->
            <div class="tool-tabs">
                <button type="button" class="tool-tab-btn @if($activeTab === 'remove_bg') active @endif" id="tabBtnRemoveBg" data-target="panelRemoveBg">
                    <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m19 11-8-8-8.5 8.5a2.12 2.12 0 0 0 0 3l5 5a2.12 2.12 0 0 0 3 0L19 11Z"></path>
                        <path d="m5 2 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                        <path d="m18 13 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                    </svg>
                    <span>Remove BG</span>
                </button>
                <button type="button" class="tool-tab-btn @if($activeTab === 'unblur') active @endif" id="tabBtnUnblur" data-target="panelUnblur">
                    <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="11" y1="8" x2="11" y2="14"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                    <span>Unblur</span>
                </button>
                <button type="button" class="tool-tab-btn @if($activeTab === 'resize') active @endif" id="tabBtnResize" data-target="panelResize">
                    <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h6v6"></path>
                        <path d="M9 21H3v-6"></path>
                        <path d="M21 3l-7 7"></path>
                        <path d="M3 21l7-7"></path>
                    </svg>
                    <span>Resize</span>
                </button>
                <button type="button" class="tool-tab-btn @if($activeTab === 'compress') active @endif" id="tabBtnCompress" data-target="panelCompress">
                    <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    <span>Compress</span>
                </button>
            </div>

            <!-- TAB 1: AI BACKGROUND REMOVER -->
            <div id="panelRemoveBg" class="tool-tab-panel" style="@if($activeTab !== 'remove_bg') display: none; @endif">
                <form id="bgRemoveForm" class="stack-form" method="POST" action="{{ route('images.process', $image) }}">
                    @csrf
                    <input type="hidden" name="remove_background" value="1">

                    <div id="activeCutoutStatus" class="active-status-card" style="@if(!$image->background_removed) display: none; @endif">
                        <span class="status-icon">✓</span>
                        <div>
                            <strong>AI Background Removed</strong>
                            <p>Cutout is active! Click any card below for <strong>instant live preview</strong>.</p>
                        </div>
                    </div>
                    <div id="featureBannerBox" class="feature-banner-box" style="@if($image->background_removed) display: none; @endif">
                        <div class="banner-icon">✨</div>
                        <div>
                            <strong>1-Click Instant AI Cutout</strong>
                            <p>Click any backdrop style card below to isolate subject and instantly preview.</p>
                        </div>
                    </div>

                    <!-- Backdrop Style Choices -->
                    <div class="form-group">
                        <label class="field-label">Choose Backdrop Style</label>
                        <div class="backdrop-choice-grid" id="backdropChoiceGrid">
                            <label class="backdrop-card @if(!$image->background_category || $image->background_category === 'transparent' || $image->background_category === 'original') active @endif">
                                <input type="radio" name="background" value="transparent" @if(!$image->background_category || $image->background_category === 'transparent' || $image->background_category === 'original') checked @endif>
                                <span class="backdrop-swatch swatch-transparent"></span>
                                <span class="backdrop-name">Transparent</span>
                            </label>
                            <label class="backdrop-card @if($image->background_category === 'white') active @endif">
                                <input type="radio" name="background" value="white" @if($image->background_category === 'white') checked @endif>
                                <span class="backdrop-swatch swatch-white"></span>
                                <span class="backdrop-name">White</span>
                            </label>
                            <label class="backdrop-card @if($image->background_category === 'studio') active @endif">
                                <input type="radio" name="background" value="studio" @if($image->background_category === 'studio') checked @endif>
                                <span class="backdrop-swatch swatch-studio"></span>
                                <span class="backdrop-name">Studio Grey</span>
                            </label>
                            <label class="backdrop-card @if($image->background_category === 'sky') active @endif">
                                <input type="radio" name="background" value="sky" @if($image->background_category === 'sky') checked @endif>
                                <span class="backdrop-swatch swatch-sky"></span>
                                <span class="backdrop-name">Sky Blue</span>
                            </label>
                            <label class="backdrop-card @if($image->background_category === 'forest') active @endif">
                                <input type="radio" name="background" value="forest" @if($image->background_category === 'forest') checked @endif>
                                <span class="backdrop-swatch swatch-forest"></span>
                                <span class="backdrop-name">Forest Green</span>
                            </label>
                        </div>
                    </div>

                    <!-- Scenic Backgrounds Library -->
                    @if($backgroundAssets->isNotEmpty())
                        <div class="form-group">
                            <label class="field-label">Or Scenic Backdrop from Library</label>
                            <div class="background-picker" id="scenicPicker">
                                <label class="background-option @if(! $image->background_asset_id) active @endif">
                                    <input type="radio" name="background_asset_id" value="" @if(! $image->background_asset_id) checked @endif>
                                    <span class="empty-bg">None</span>
                                </label>
                                @foreach($backgroundAssets as $backgroundAsset)
                                    <label class="background-option @if($image->background_asset_id == $backgroundAsset->id) active @endif">
                                        <input type="radio" name="background_asset_id" value="{{ $backgroundAsset->id }}" 
                                               data-url="{{ $backgroundUrls[$backgroundAsset->id] }}"
                                               @if($image->background_asset_id == $backgroundAsset->id) checked @endif>
                                        <img src="{{ $backgroundUrls[$backgroundAsset->id] }}" alt="{{ $backgroundAsset->name }}">
                                        <strong>{{ $backgroundAsset->name }}</strong>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Format Selection -->
                    <div class="form-group">
                        <label for="selectBgFormat">
                            <span class="field-label">Export Format</span>
                            <select name="format" id="selectBgFormat" required>
                                <option value="png" selected>PNG (Recommended &bull; preserves transparent cutout)</option>
                                <option value="webp">WEBP (Modern web format &bull; preserves transparency)</option>
                                <option value="jpg">JPG (For solid color backdrops)</option>
                            </select>
                        </label>
                    </div>

                    <button class="button primary-btn full-width" type="submit" id="btnSubmitRemoveBg">
                        <span class="btn-text" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m19 11-8-8-8.5 8.5a2.12 2.12 0 0 0 0 3l5 5a2.12 2.12 0 0 0 3 0L19 11Z"></path>
                                <path d="m5 2 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                                <path d="m18 13 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"></path>
                            </svg>
                            <span id="btnSubmitRemoveBgText">@if($image->background_removed) Re-Apply Background @else Remove Background with AI @endif</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- TAB 2: AI UNBLUR & CLARITY ENHANCER -->
            <div id="panelUnblur" class="tool-tab-panel" style="@if($activeTab !== 'unblur') display: none; @endif">
                <form id="unblurForm" class="stack-form" method="POST" action="{{ route('images.unblur', $image) }}">
                    @csrf
                    <div class="feature-banner-box unblur-banner">
                        <div class="banner-icon">🔍</div>
                        <div>
                            <strong>AI Clarity &amp; Unblur Engine</strong>
                            <p>Restore crisp edges, deblur motion softness, and enhance micro-contrast details in milliseconds.</p>
                        </div>
                    </div>

                    <div id="unblurSuccessBox" class="active-status-card" style="@if($image->last_action !== 'unblurred') display: none; @endif">
                        <span class="status-icon">✓</span>
                        <div>
                            <strong>Clarity Restored &amp; Sharpened</strong>
                            <p>Photo enhanced with AI Clarity. Toggle "View Original" above to compare before &amp; after.</p>
                        </div>
                    </div>

                    <!-- Strength Presets -->
                    <div class="form-group">
                        <label class="field-label">Clarity Strength Preset</label>
                        <div class="preset-pill-group">
                            <button type="button" class="btn-pill unblur-preset" data-strength="0.35">Subtle (35%)</button>
                            <button type="button" class="btn-pill unblur-preset active" data-strength="0.65">Balanced (65%)</button>
                            <button type="button" class="btn-pill unblur-preset" data-strength="0.85">Crisp (85%)</button>
                            <button type="button" class="btn-pill unblur-preset" data-strength="1.0">Max (100%)</button>
                        </div>
                    </div>

                    <!-- Fine-tune Slider -->
                    <div class="form-group">
                        <div class="slider-header">
                            <label class="field-label" for="unblurStrengthInput">Sharpness Intensity</label>
                            <span class="slider-val" id="unblurStrengthVal">65%</span>
                        </div>
                        <input type="range" id="unblurStrengthInput" name="strength" min="0.1" max="1.0" step="0.05" value="0.65" class="range-slider">
                        <div class="slider-labels">
                            <span>Gentle Soft</span>
                            <span>Balanced</span>
                            <span>Ultra Sharp</span>
                        </div>
                    </div>

                    <!-- Advanced Options Checkboxes -->
                    <div class="form-group">
                        <label class="check-row" style="margin-bottom: 8px;">
                            <input type="checkbox" name="denoise" value="1" checked id="checkUnblurDenoise">
                            <span><strong>Edge-preserving Denoise</strong> (suppress grain in flat zones)</span>
                        </label>
                        <label class="check-row">
                            <input type="checkbox" name="contrast_boost" value="1" checked id="checkUnblurContrast">
                            <span><strong>Micro-contrast Recovery (CLAHE)</strong> (recover depth)</span>
                        </label>
                    </div>

                    <!-- Target Source -->
                    @if($image->processed_path && $image->processed_path !== $image->original_path)
                        <div class="form-group">
                            <label class="field-label">Apply unblur to</label>
                            <div class="radio-pill-group">
                                <label class="radio-pill active">
                                    <input type="radio" name="source_mode" value="current" checked>
                                    <span>Current Studio Output</span>
                                </label>
                                <label class="radio-pill">
                                    <input type="radio" name="source_mode" value="original">
                                    <span>Original Upload</span>
                                </label>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="source_mode" value="original">
                    @endif

                    <button class="button primary-btn unblur-btn full-width" type="submit" id="btnSubmitUnblur">
                        <span class="btn-text" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                            <span id="btnSubmitUnblurText">🔍 Unblur &amp; Restore Clarity Now</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- TAB 3: RESIZE & SCALE -->
            <div id="panelResize" class="tool-tab-panel" style="@if($activeTab !== 'resize') display: none; @endif">
                <form id="processForm" class="stack-form" method="POST" action="{{ route('images.process', $image) }}">
                    @csrf

                    <!-- Retain Background Removal if active -->
                    @if($image->background_removed)
                        <input type="hidden" name="remove_background" value="1">
                        <input type="hidden" name="background" value="{{ $image->background_category ?: 'transparent' }}">
                        @if($image->background_asset_id)
                            <input type="hidden" name="background_asset_id" value="{{ $image->background_asset_id }}">
                        @endif
                    @endif

                    <!-- Resize Presets -->
                    <div class="form-group">
                        <label class="field-label">Quick Scale</label>
                        <div class="preset-pill-group">
                            <button type="button" class="btn-pill" data-scale="0.25">25%</button>
                            <button type="button" class="btn-pill" data-scale="0.50">50%</button>
                            <button type="button" class="btn-pill" data-scale="0.75">75%</button>
                            <button type="button" class="btn-pill" data-scale="1.00">100%</button>
                            <button type="button" class="btn-pill" data-scale="1.50">150%</button>
                            <button type="button" class="btn-pill" data-scale="2.00">200%</button>
                        </div>
                    </div>

                    <!-- Dimension Controls -->
                    <div class="form-group">
                        <div class="dimension-header">
                            <span class="field-label">Dimensions (Width &times; Height)</span>
                            <label class="ratio-lock-toggle" title="Keep proportional width and height">
                                <input type="checkbox" id="lockAspectRatio" checked>
                                <span class="lock-icon" id="lockIcon">&#128274; Lock ratio</span>
                            </label>
                        </div>

                        <div class="dimensions-input-row">
                            <div class="input-with-unit">
                                <label for="inputWidth">Width</label>
                                <input type="number" id="inputWidth" name="width" min="16" max="8000" 
                                       value="{{ $image->resize_width ?: $image->width }}" 
                                       placeholder="{{ $image->width }}">
                                <span class="unit">px</span>
                            </div>

                            <div class="dimension-separator">&times;</div>

                            <div class="input-with-unit">
                                <label for="inputHeight">Height</label>
                                <input type="number" id="inputHeight" name="height" min="16" max="8000" 
                                       value="{{ $image->resize_height ?: $image->height }}" 
                                       placeholder="{{ $image->height }}">
                                <span class="unit">px</span>
                            </div>
                        </div>

                        <!-- Common preset dropdown -->
                        <div class="preset-select-row">
                            <select id="commonPresetSelect">
                                <option value="">-- Choose common preset --</option>
                                <option value="600x600">Passport / Square Avatar (600 &times; 600)</option>
                                <option value="1080x1080">Instagram Post (1080 &times; 1080)</option>
                                <option value="1080x1920">Story / TikTok / Reel (1080 &times; 1920)</option>
                                <option value="1200x630">Social Share / Open Graph (1200 &times; 630)</option>
                                <option value="1280x720">HD 720p (1280 &times; 720)</option>
                                <option value="1920x1080">Full HD 1080p (1920 &times; 1080)</option>
                            </select>
                            <button type="button" class="button secondary small" id="btnResetDimensions" title="Reset to original dimensions">Reset</button>
                        </div>
                    </div>

                    <!-- Resize Mode -->
                    <div class="form-group">
                        <label for="selectMode">
                            <span class="field-label">Resize Mode</span>
                            <select name="mode" id="selectMode">
                                <option value="stretch">Exact Size (Stretch to dimensions)</option>
                                <option value="contain">Fit within dimensions (Keep Aspect Ratio)</option>
                                <option value="cover">Fill and Crop (Cover dimensions)</option>
                            </select>
                        </label>
                    </div>

                    @if(!$image->background_removed)
                        <!-- Optional Background Removal in Resize -->
                        <label class="check-row ai-removal-badge" style="margin-bottom: 12px;">
                            <input type="checkbox" name="remove_background" id="checkRemoveBg" value="1">
                            <span><strong>Also remove background with AI</strong></span>
                        </label>

                        <div class="form-group">
                            <label for="selectBackground">
                                <span class="field-label">Background Color</span>
                                <select name="background" id="selectBackground">
                                    @foreach($backgrounds as $value => $label)
                                        <option value="{{ $value }}" @if($image->background_category === $value) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    @endif

                    <!-- Output Format -->
                    <div class="form-group">
                        <label for="selectFormat">
                            <span class="field-label">Output Format</span>
                            <select name="format" id="selectFormat" required>
                                <option value="png" @if(($image->processed_format ?: pathinfo($image->original_name, PATHINFO_EXTENSION)) === 'png') selected @endif>PNG (High Quality, supports transparency)</option>
                                <option value="jpg" @if(($image->processed_format ?: pathinfo($image->original_name, PATHINFO_EXTENSION)) === 'jpg') selected @endif>JPG (Compressed, smaller file size)</option>
                                <option value="webp" @if(($image->processed_format ?: pathinfo($image->original_name, PATHINFO_EXTENSION)) === 'webp') selected @endif>WEBP (Modern web optimized)</option>
                            </select>
                        </label>
                    </div>

                    <button class="button primary-btn full-width" type="submit" id="btnSubmitProcess">
                        <span class="btn-text" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h6v6"></path>
                                <path d="M9 21H3v-6"></path>
                                <path d="M21 3l-7 7"></path>
                                <path d="M3 21l7-7"></path>
                            </svg>
                            <span id="btnSubmitProcessText">Apply Resize &amp; Scale</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- TAB 3: TARGET KB COMPRESSOR -->
            <div id="panelCompress" class="tool-tab-panel" style="@if($activeTab !== 'compress') display: none; @endif">
                <form id="compressForm" class="stack-form" method="POST" action="{{ route('images.compress', $image) }}">
                    @csrf

                    @if($image->last_action === 'compressed' || ($image->processed_path && $image->savingsPercent() > 0))
                        <div class="compression-result-card">
                            <div class="result-card-header">
                                <span class="result-badge">⚡ Compression Result</span>
                                @if($image->savingsPercent())
                                    <span class="savings-pill">-{{ $image->savingsPercent() }}% Size Saved</span>
                                @endif
                            </div>
                            <div class="result-stats-row">
                                <div class="result-stat-item">
                                    <span class="stat-label">Original</span>
                                    <span class="stat-num">{{ $image->formattedSize() }}</span>
                                </div>
                                <div class="stat-arrow">&rarr;</div>
                                <div class="result-stat-item">
                                    <span class="stat-label">Current</span>
                                    <span class="stat-num highlight">{{ $image->formattedCurrentSize() }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="compression-mode-toggle">
                        <label class="mode-radio">
                            <input type="radio" name="compression_type" value="target_kb" id="typeTargetKb" checked>
                            <span>Target File Size</span>
                        </label>
                        <label class="mode-radio">
                            <input type="radio" name="compression_type" value="quality" id="typeQuality">
                            <span>Quality Ratio</span>
                        </label>
                    </div>

                    <!-- Target KB section -->
                    <div id="sectionTargetKb" class="form-group">
                        <label class="field-label">Quick Target Presets</label>
                        <div class="preset-pill-group kb-preset-group">
                            <button type="button" class="btn-pill kb-preset" data-kb="10">10 KB</button>
                            <button type="button" class="btn-pill kb-preset" data-kb="20">20 KB</button>
                            <button type="button" class="btn-pill kb-preset active" data-kb="50">50 KB</button>
                            <button type="button" class="btn-pill kb-preset" data-kb="100">100 KB</button>
                            <button type="button" class="btn-pill kb-preset" data-kb="200">200 KB</button>
                            <button type="button" class="btn-pill kb-preset" data-kb="500">500 KB</button>
                        </div>

                        <div class="input-with-unit" style="margin-top: 10px;">
                            <label for="inputTargetKb">Target Size (Max KB)</label>
                            <input type="number" id="inputTargetKb" name="target_kb" value="50" min="5" max="50000" placeholder="e.g. 50">
                            <span class="unit">KB</span>
                        </div>
                        <p class="field-hint">Optimizes quality &amp; resolution so your file reaches &le; target KB without blocky artifacts.</p>
                    </div>

                    <!-- Quality section -->
                    <div id="sectionQuality" class="form-group" style="display: none;">
                        <div class="slider-header">
                            <label class="field-label" for="inputQuality">Compression Quality</label>
                            <span class="slider-val" id="qualityDisplay">75%</span>
                        </div>
                        <input type="range" id="inputQuality" name="quality" min="10" max="95" value="75" class="range-slider">
                        <div class="slider-labels">
                            <span>Maximum Compression (10%)</span>
                            <span>High Quality (95%)</span>
                        </div>
                    </div>

                    <!-- Output format -->
                    <div class="form-group">
                        <label for="selectCompressFormat">
                            <span class="field-label">Output Format</span>
                            <select name="format" id="selectCompressFormat" required>
                                <option value="jpg" selected>JPG (Best for job portals, forms &amp; photo compression)</option>
                                <option value="webp">WEBP (Modern web, up to 35% smaller at superior clarity)</option>
                                <option value="png">PNG (Lossless, larger file size)</option>
                            </select>
                        </label>
                    </div>

                    <button class="button primary-btn compress-btn full-width" type="submit" id="btnSubmitCompress">
                        <span class="btn-text" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg class="tab-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                            <span id="btnSubmitCompressText">Compress to Target Size</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- Action buttons -->
            <div class="action-row">
                <a class="button secondary" href="{{ route('images.download', $image) }}">Download Image</a>
                <form method="POST" action="{{ route('images.destroy', $image) }}" onsubmit="return confirm('Are you sure you want to delete this image?');">
                    @csrf
                    @method('DELETE')
                    <button class="button danger" type="submit">Delete</button>
                </form>
            </div>
        </aside>
    </section>

    <!-- Client-side Interactive Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const originalWidth = {{ (int) $image->width ?: 800 }};
            const originalHeight = {{ (int) $image->height ?: 600 }};
            const aspectRatio = originalWidth / (originalHeight || 1);

            const inputWidth = document.getElementById('inputWidth');
            const inputHeight = document.getElementById('inputHeight');
            const lockAspect = document.getElementById('lockAspectRatio');
            const lockIcon = document.getElementById('lockIcon');
            const targetBadge = document.getElementById('targetDimensionBadge');
            const targetSizeText = document.getElementById('targetSizeText');
            const presetSelect = document.getElementById('commonPresetSelect');
            const btnReset = document.getElementById('btnResetDimensions');
            const form = document.getElementById('processForm');
            const btnSubmit = document.getElementById('btnSubmitProcess');
            const selectBg = document.getElementById('selectBackground');
            const previewStage = document.getElementById('previewStage');
            const mainImg = document.getElementById('mainPreviewImage');
            const previewFallback = document.getElementById('previewFallback');
            const btnToggleOriginal = document.getElementById('btnToggleOriginal');
            const toggleText = document.getElementById('toggleText');

            if (mainImg) {
                mainImg.addEventListener('error', function() {
                    this.style.display = 'none';
                    if (previewFallback) previewFallback.style.display = 'block';
                });
                mainImg.addEventListener('load', function() {
                    this.style.display = 'block';
                    if (previewFallback) previewFallback.style.display = 'none';
                });
            }

            // Zoom controls
            let zoom = 1.0;
            const zoomLevel = document.getElementById('zoomLevel');
            const btnZoomIn = document.getElementById('btnZoomIn');
            const btnZoomOut = document.getElementById('btnZoomOut');
            const btnZoomReset = document.getElementById('btnZoomReset');

            function applyZoom(z) {
                zoom = Math.max(0.2, Math.min(3.0, z));
                zoomLevel.textContent = Math.round(zoom * 100) + '%';
                mainImg.style.transform = `scale(${zoom})`;
            }

            if (btnZoomIn) btnZoomIn.addEventListener('click', () => applyZoom(zoom + 0.15));
            if (btnZoomOut) btnZoomOut.addEventListener('click', () => applyZoom(zoom - 0.15));
            if (btnZoomReset) btnZoomReset.addEventListener('click', () => {
                zoom = 1.0;
                applyZoom(1.0);
            });

            // Update lock icon text
            if (lockAspect && lockIcon) {
                lockAspect.addEventListener('change', function() {
                    lockIcon.innerHTML = this.checked ? '&#128274; Lock ratio' : '&#128275; Free ratio';
                });
            }

            function updateTargetBadge() {
                if (!inputWidth || !inputHeight || !targetBadge) return;
                const w = parseInt(inputWidth.value, 10);
                const h = parseInt(inputHeight.value, 10);
                if (w && h) {
                    targetBadge.style.display = 'inline-flex';
                    targetSizeText.textContent = `${w} × ${h} px`;
                } else {
                    targetBadge.style.display = 'none';
                }
            }

            if (inputWidth) {
                inputWidth.addEventListener('input', function() {
                    const w = parseInt(this.value, 10);
                    if (lockAspect && lockAspect.checked && w && !isNaN(w)) {
                        inputHeight.value = Math.max(1, Math.round(w / aspectRatio));
                    }
                    updateTargetBadge();
                });
            }

            if (inputHeight) {
                inputHeight.addEventListener('input', function() {
                    const h = parseInt(this.value, 10);
                    if (lockAspect && lockAspect.checked && h && !isNaN(h)) {
                        inputWidth.value = Math.max(1, Math.round(h * aspectRatio));
                    }
                    updateTargetBadge();
                });
            }

            // Scale percentage buttons
            document.querySelectorAll('.btn-pill[data-scale]').forEach(button => {
                button.addEventListener('click', function() {
                    const scale = parseFloat(this.getAttribute('data-scale'));
                    inputWidth.value = Math.max(16, Math.round(originalWidth * scale));
                    inputHeight.value = Math.max(16, Math.round(originalHeight * scale));
                    updateTargetBadge();
                });
            });

            // Common preset dropdown
            if (presetSelect) {
                presetSelect.addEventListener('change', function() {
                    if (!this.value) return;
                    const parts = this.value.split('x');
                    if (parts.length === 2) {
                        if (lockAspect) {
                            lockAspect.checked = false;
                            lockIcon.innerHTML = '&#128275; Free ratio';
                        }
                        inputWidth.value = parts[0];
                        inputHeight.value = parts[1];
                        updateTargetBadge();
                    }
                });
            }

            // Reset button
            if (btnReset) {
                btnReset.addEventListener('click', function() {
                    inputWidth.value = originalWidth;
                    inputHeight.value = originalHeight;
                    if (lockAspect) {
                        lockAspect.checked = true;
                        lockIcon.innerHTML = '&#128274; Lock ratio';
                    }
                    if (presetSelect) presetSelect.value = '';
                    updateTargetBadge();
                });
            }

            // Toggle Original vs Processed
            let showingOriginal = false;
            let savedBgColor = '';
            let savedBgImage = '';
            if (btnToggleOriginal) {
                btnToggleOriginal.addEventListener('click', function() {
                    showingOriginal = !showingOriginal;
                    if (showingOriginal) {
                        savedBgColor = mainImg.style.backgroundColor;
                        savedBgImage = mainImg.style.backgroundImage;
                        mainImg.style.backgroundColor = 'transparent';
                        mainImg.style.backgroundImage = 'none';
                        mainImg.src = mainImg.getAttribute('data-original-src');
                        toggleText.innerHTML = '&#128260; View Processed';
                    } else {
                        const cutoutSrc = mainImg.getAttribute('data-cutout-src');
                        mainImg.src = cutoutSrc || mainImg.getAttribute('data-processed-src');
                        mainImg.style.backgroundColor = savedBgColor;
                        mainImg.style.backgroundImage = savedBgImage;
                        toggleText.innerHTML = '&#128065; View Original';
                    }
                });
            }

            // 4-Tab toggling logic (Remove BG vs Unblur vs Resize vs Compress)
            const tabBtnRemoveBg = document.getElementById('tabBtnRemoveBg');
            const tabBtnUnblur = document.getElementById('tabBtnUnblur');
            const tabBtnResize = document.getElementById('tabBtnResize');
            const tabBtnCompress = document.getElementById('tabBtnCompress');
            const panelRemoveBg = document.getElementById('panelRemoveBg');
            const panelUnblur = document.getElementById('panelUnblur');
            const panelResize = document.getElementById('panelResize');
            const panelCompress = document.getElementById('panelCompress');

            function switchTab(btn, panel) {
                [tabBtnRemoveBg, tabBtnUnblur, tabBtnResize, tabBtnCompress].forEach(b => b && b.classList.remove('active'));
                [panelRemoveBg, panelUnblur, panelResize, panelCompress].forEach(p => p && (p.style.display = 'none'));
                if (btn) btn.classList.add('active');
                if (panel) panel.style.display = 'block';
            }

            if (tabBtnRemoveBg) tabBtnRemoveBg.addEventListener('click', () => switchTab(tabBtnRemoveBg, panelRemoveBg));
            if (tabBtnUnblur) tabBtnUnblur.addEventListener('click', () => switchTab(tabBtnUnblur, panelUnblur));
            if (tabBtnResize) tabBtnResize.addEventListener('click', () => switchTab(tabBtnResize, panelResize));
            if (tabBtnCompress) tabBtnCompress.addEventListener('click', () => switchTab(tabBtnCompress, panelCompress));

            // Unblur Preset chips & Slider sync
            const unblurPresets = document.querySelectorAll('.unblur-preset');
            const unblurStrengthInput = document.getElementById('unblurStrengthInput');
            const unblurStrengthVal = document.getElementById('unblurStrengthVal');
            const unblurForm = document.getElementById('unblurForm');
            const btnSubmitUnblur = document.getElementById('btnSubmitUnblur');
            const btnSubmitUnblurText = document.getElementById('btnSubmitUnblurText');
            const unblurBadge = document.getElementById('unblurBadge');
            const unblurSuccessBox = document.getElementById('unblurSuccessBox');

            if (unblurStrengthInput && unblurStrengthVal) {
                unblurStrengthInput.addEventListener('input', function() {
                    const percent = Math.round(parseFloat(this.value) * 100);
                    unblurStrengthVal.textContent = percent + '%';
                    unblurPresets.forEach(b => {
                        b.classList.toggle('active', Math.abs(parseFloat(b.getAttribute('data-strength')) - parseFloat(this.value)) < 0.04);
                    });
                });
            }

            unblurPresets.forEach(btn => {
                btn.addEventListener('click', function() {
                    unblurPresets.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const str = parseFloat(this.getAttribute('data-strength'));
                    if (unblurStrengthInput) {
                        unblurStrengthInput.value = str;
                        const percent = Math.round(str * 100);
                        if (unblurStrengthVal) unblurStrengthVal.textContent = percent + '%';
                    }
                });
            });

            // Radio pill toggle for source_mode in unblur
            document.querySelectorAll('#panelUnblur .radio-pill').forEach(pill => {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('#panelUnblur .radio-pill').forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    const r = this.querySelector('input[type="radio"]');
                    if (r) r.checked = true;
                });
            });

            // Unblur Form AJAX submit
            if (unblurForm && btnSubmitUnblur) {
                unblurForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const strengthVal = unblurStrengthInput ? unblurStrengthInput.value : 0.65;
                    const denoiseChecked = document.getElementById('checkUnblurDenoise') ? document.getElementById('checkUnblurDenoise').checked : true;
                    const contrastChecked = document.getElementById('checkUnblurContrast') ? document.getElementById('checkUnblurContrast').checked : true;
                    const sourceRadio = document.querySelector('#panelUnblur input[name="source_mode"]:checked');
                    const sourceModeVal = sourceRadio ? sourceRadio.value : 'original';

                    btnSubmitUnblur.disabled = true;
                    if (btnSubmitUnblurText) btnSubmitUnblurText.textContent = 'Restoring Clarity with AI... Please wait';
                    btnSubmitUnblur.style.opacity = '0.7';

                    if (stageOverlayLoader) {
                        stageOverlayLoader.style.display = 'flex';
                        if (stageLoaderTitle) stageLoaderTitle.textContent = 'Restoring Clarity & Sharpness...';
                        if (stageLoaderSubtitle) stageLoaderSubtitle.textContent = 'Applying multi-scale luminance deblur (~0.3s)';
                    }

                    fetch("{{ route('images.unblur', $image) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            strength: strengthVal,
                            denoise: denoiseChecked ? 1 : 0,
                            contrast_boost: contrastChecked ? 1 : 0,
                            source_mode: sourceModeVal
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Unblur failed with status ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        btnSubmitUnblur.disabled = false;
                        btnSubmitUnblur.style.opacity = '1';
                        if (btnSubmitUnblurText) btnSubmitUnblurText.textContent = '🔍 Unblur & Restore Clarity Now';
                        if (stageOverlayLoader) stageOverlayLoader.style.display = 'none';

                        if (data.success) {
                            mainImg.src = data.imageUrl;
                            mainImg.setAttribute('data-processed-src', data.imageUrl);
                            mainImg.style.display = 'block';

                            if (btnToggleOriginal) btnToggleOriginal.style.display = 'inline-flex';
                            if (unblurBadge) unblurBadge.style.display = 'inline-flex';
                            if (unblurSuccessBox) unblurSuccessBox.style.display = 'flex';

                            showToastBadge('✓ Clarity Restored & Saved!');
                        }
                    })
                    .catch(err => {
                        btnSubmitUnblur.disabled = false;
                        btnSubmitUnblur.style.opacity = '1';
                        if (btnSubmitUnblurText) btnSubmitUnblurText.textContent = '🔍 Unblur & Restore Clarity Now';
                        if (stageOverlayLoader) stageOverlayLoader.style.display = 'none';
                        console.error('Unblur error:', err);
                        alert('Could not unblur image. Please try again.');
                    });
                });
            }

            // Backdrop styling colors map
            const BACKDROP_COLORS = {
                'transparent': 'transparent',
                'white': '#ffffff',
                'studio': '#374151',
                'sky': '#38bdf8',
                'forest': '#15803d'
            };

            const backdropCards = document.querySelectorAll('.backdrop-card');
            const selectBgFormat = document.getElementById('selectBgFormat');
            const scenicOptions = document.querySelectorAll('#scenicPicker .background-option');
            const stageOverlayLoader = document.getElementById('stageOverlayLoader');
            const stageLoaderTitle = document.getElementById('stageLoaderTitle');
            const stageLoaderSubtitle = document.getElementById('stageLoaderSubtitle');
            const bgRemovedBadge = document.getElementById('bgRemovedBadge');
            const backdropSyncBadge = document.getElementById('backdropSyncBadge');
            const activeCutoutStatus = document.getElementById('activeCutoutStatus');
            const featureBannerBox = document.getElementById('featureBannerBox');
            const bgRemoveForm = document.getElementById('bgRemoveForm');
            const btnSubmitRemoveBg = document.getElementById('btnSubmitRemoveBg');

            let syncTimer = null;
            let isProcessingInitialCutout = false;

            function showToastBadge(text) {
                if (!backdropSyncBadge) return;
                backdropSyncBadge.textContent = text;
                backdropSyncBadge.style.display = 'inline-flex';
                clearTimeout(backdropSyncBadge._timer);
                backdropSyncBadge._timer = setTimeout(() => {
                    backdropSyncBadge.style.display = 'none';
                }, 3000);
            }

            function applyBackdropLocally(bgType, assetId = null, assetUrl = null) {
                if (!mainImg) return;
                const cutoutSrc = mainImg.getAttribute('data-cutout-src');
                const isBgRemoved = mainImg.getAttribute('data-bg-removed') === '1';

                // If cutout exists and background was removed, ensure image displays the cutout
                if (cutoutSrc && isBgRemoved) {
                    if (mainImg.src !== cutoutSrc) {
                        mainImg.src = cutoutSrc;
                    }
                }

                if (assetId && assetUrl) {
                    mainImg.style.backgroundColor = 'transparent';
                    mainImg.style.backgroundImage = `url('${assetUrl}')`;
                    mainImg.style.backgroundSize = 'cover';
                    mainImg.style.backgroundPosition = 'center';
                    mainImg.style.backgroundRepeat = 'no-repeat';
                } else {
                    mainImg.style.backgroundImage = 'none';
                    const color = BACKDROP_COLORS[bgType] || 'transparent';
                    mainImg.style.backgroundColor = color;
                }
            }

            function syncBackdropWithServer(bgType, assetId = null, assetUrl = null) {
                const isBgRemoved = mainImg.getAttribute('data-bg-removed') === '1';
                const cutoutSrc = mainImg.getAttribute('data-cutout-src');
                const formatVal = selectBgFormat ? selectBgFormat.value : 'png';

                // Case 1: Initial Cutout not generated yet (e.g. newly uploaded image)
                if (!isBgRemoved || !cutoutSrc) {
                    if (isProcessingInitialCutout) return;
                    isProcessingInitialCutout = true;

                    if (stageOverlayLoader) {
                        stageOverlayLoader.style.display = 'flex';
                        if (stageLoaderTitle) stageLoaderTitle.textContent = 'Removing background with AI...';
                        if (stageLoaderSubtitle) stageLoaderSubtitle.textContent = 'Applying studio neural cutout (~1s)';
                    }
                    if (btnSubmitRemoveBg) {
                        btnSubmitRemoveBg.disabled = true;
                        const textSpan = document.getElementById('btnSubmitRemoveBgText');
                        if (textSpan) textSpan.textContent = 'Processing with AI...';
                    }

                    fetch("{{ route('images.process', $image) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            format: formatVal,
                            background: bgType,
                            background_asset_id: assetId || null,
                            remove_background: 1
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Server returned ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        isProcessingInitialCutout = false;
                        if (stageOverlayLoader) stageOverlayLoader.style.display = 'none';
                        if (btnSubmitRemoveBg) {
                            btnSubmitRemoveBg.disabled = false;
                            const textSpan = document.getElementById('btnSubmitRemoveBgText');
                            if (textSpan) textSpan.textContent = 'Apply Selected Backdrop';
                        }

                        if (data.success) {
                            mainImg.setAttribute('data-bg-removed', '1');
                            if (data.cutoutUrl) mainImg.setAttribute('data-cutout-src', data.cutoutUrl);
                            if (data.imageUrl) mainImg.setAttribute('data-processed-src', data.imageUrl);

                            // Apply cutout and backdrop locally
                            applyBackdropLocally(bgType, assetId, assetUrl);

                            // Update UI badges and status
                            if (bgRemovedBadge) bgRemovedBadge.style.display = 'inline-flex';
                            if (activeCutoutStatus) activeCutoutStatus.style.display = 'flex';
                            if (featureBannerBox) featureBannerBox.style.display = 'none';

                            showToastBadge('✓ Style Applied & Saved');
                        }
                    })
                    .catch(err => {
                        isProcessingInitialCutout = false;
                        if (stageOverlayLoader) stageOverlayLoader.style.display = 'none';
                        if (btnSubmitRemoveBg) {
                            btnSubmitRemoveBg.disabled = false;
                            const textSpan = document.getElementById('btnSubmitRemoveBgText');
                            if (textSpan) textSpan.textContent = 'Apply Selected Backdrop';
                        }
                        console.error('AI background removal error:', err);
                        alert('Could not remove background automatically. Please click the button below to retry.');
                    });
                    return;
                }

                // Case 2: Cutout is ALREADY active! Instant local preview is already rendered.
                // Debounce sync with backend to keep the file on disk updated for downloads
                clearTimeout(syncTimer);
                showToastBadge('⚡ Live Preview');

                syncTimer = setTimeout(() => {
                    fetch("{{ route('images.process', $image) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            format: formatVal,
                            background: bgType,
                            background_asset_id: assetId || null,
                            remove_background: 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (data.imageUrl) mainImg.setAttribute('data-processed-src', data.imageUrl);
                            showToastBadge('✓ Saved');
                        }
                    })
                    .catch(err => console.warn('Background sync warning:', err));
                }, 350);
            }

            // Backdrop cards click listener
            backdropCards.forEach(card => {
                card.addEventListener('click', function() {
                    backdropCards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;

                    // Reset scenic options
                    scenicOptions.forEach(opt => {
                        opt.classList.remove('active');
                        const r = opt.querySelector('input[type="radio"]');
                        if (r && r.value === '') r.checked = true;
                    });

                    const bgValue = radio ? radio.value : 'transparent';

                    // Auto format suggestion
                    if (bgValue === 'transparent' && selectBgFormat) {
                        selectBgFormat.value = 'png';
                    }

                    // 1. Instant local visual update
                    applyBackdropLocally(bgValue, null, null);

                    // 2. Trigger sync / AI cutout
                    syncBackdropWithServer(bgValue, null, null);
                });
            });

            // Scenic backdrop click listener
            scenicOptions.forEach(opt => {
                opt.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    const assetId = radio ? radio.value : null;

                    if (!assetId) {
                        scenicOptions.forEach(o => o.classList.remove('active'));
                        this.classList.add('active');
                        if (radio) radio.checked = true;
                        applyBackdropLocally('transparent', null, null);
                        syncBackdropWithServer('transparent', null, null);
                        return;
                    }

                    scenicOptions.forEach(o => o.classList.remove('active'));
                    this.classList.add('active');
                    if (radio) radio.checked = true;

                    // Uncheck solid cards
                    backdropCards.forEach(c => c.classList.remove('active'));

                    const assetUrl = radio.getAttribute('data-url');

                    // 1. Instant local visual update
                    applyBackdropLocally('admin_asset', assetId, assetUrl);

                    // 2. Trigger sync / AI cutout
                    syncBackdropWithServer('admin_asset', assetId, assetUrl);
                });
            });

            // BG Remove Form Submit
            if (bgRemoveForm) {
                bgRemoveForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const activeCard = document.querySelector('.backdrop-card.active input[type="radio"]');
                    const activeScenic = document.querySelector('#scenicPicker .background-option.active input[type="radio"]');
                    const bgVal = activeCard ? activeCard.value : 'transparent';
                    const scenicVal = activeScenic ? activeScenic.value : null;
                    const scenicUrl = activeScenic ? activeScenic.getAttribute('data-url') : null;

                    if (scenicVal) {
                        applyBackdropLocally('admin_asset', scenicVal, scenicUrl);
                        syncBackdropWithServer('admin_asset', scenicVal, scenicUrl);
                    } else {
                        applyBackdropLocally(bgVal, null, null);
                        syncBackdropWithServer(bgVal, null, null);
                    }
                });
            }

            // Compression mode toggle (Target KB vs Quality)
            const typeTargetKb = document.getElementById('typeTargetKb');
            const typeQuality = document.getElementById('typeQuality');
            const sectionTargetKb = document.getElementById('sectionTargetKb');
            const sectionQuality = document.getElementById('sectionQuality');
            const inputQuality = document.getElementById('inputQuality');
            const qualityDisplay = document.getElementById('qualityDisplay');
            const inputTargetKb = document.getElementById('inputTargetKb');
            const kbPresets = document.querySelectorAll('.btn-pill.kb-preset');

            function syncCompressionMode() {
                if (typeTargetKb && typeTargetKb.checked) {
                    if (sectionTargetKb) sectionTargetKb.style.display = 'block';
                    if (sectionQuality) sectionQuality.style.display = 'none';
                } else if (typeQuality && typeQuality.checked) {
                    if (sectionTargetKb) sectionTargetKb.style.display = 'none';
                    if (sectionQuality) sectionQuality.style.display = 'block';
                }
            }

            if (typeTargetKb) typeTargetKb.addEventListener('change', syncCompressionMode);
            if (typeQuality) typeQuality.addEventListener('change', syncCompressionMode);

            if (inputQuality && qualityDisplay) {
                inputQuality.addEventListener('input', function() {
                    qualityDisplay.textContent = this.value + '%';
                });
            }

            // KB Presets click handling
            kbPresets.forEach(btn => {
                btn.addEventListener('click', function() {
                    kbPresets.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    if (inputTargetKb) {
                        inputTargetKb.value = this.getAttribute('data-kb');
                    }
                });
            });

            if (inputTargetKb) {
                inputTargetKb.addEventListener('input', function() {
                    const currentVal = this.value;
                    kbPresets.forEach(b => {
                        if (b.getAttribute('data-kb') === currentVal) {
                            b.classList.add('active');
                        } else {
                            b.classList.remove('active');
                        }
                    });
                });
            }

            // Compress form submit state
            const compressForm = document.getElementById('compressForm');
            const btnSubmitCompress = document.getElementById('btnSubmitCompress');

            if (compressForm && btnSubmitCompress) {
                compressForm.addEventListener('submit', function() {
                    btnSubmitCompress.disabled = true;
                    const cText = document.getElementById('btnSubmitCompressText');
                    if (cText) cText.textContent = 'Compressing... Please wait';
                    btnSubmitCompress.style.opacity = '0.7';
                    btnSubmitCompress.style.cursor = 'wait';
                });
            }

            // Resize form submit state
            if (form && btnSubmit) {
                form.addEventListener('submit', function() {
                    btnSubmit.disabled = true;
                    const pText = document.getElementById('btnSubmitProcessText');
                    if (pText) pText.textContent = 'Processing & Resizing... Please wait';
                    btnSubmit.style.opacity = '0.7';
                    btnSubmit.style.cursor = 'wait';
                });
            }

            updateTargetBadge();
        });
    </script>
@endsection
