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
                    @if($image->processed_path)
                        <button type="button" class="btn-tool" id="btnToggleOriginal" title="Toggle between original and processed">
                            <span id="toggleText">&#128065; View Original</span>
                        </button>
                    @endif
                    <button type="button" class="btn-tool" id="btnZoomOut" title="Zoom Out">&minus;</button>
                    <span class="zoom-level" id="zoomLevel">100%</span>
                    <button type="button" class="btn-tool" id="btnZoomIn" title="Zoom In">&plus;</button>
                    <button type="button" class="btn-tool" id="btnZoomReset" title="Fit to screen">Fit</button>
                </div>
            </div>

            <div class="preview-stage" id="previewStage">
                <div class="preview-canvas-wrapper" id="canvasWrapper">
                    <img id="mainPreviewImage" 
                         src="{{ $imageUrl }}" 
                         data-processed-src="{{ $imageUrl }}" 
                         data-original-src="{{ $originalUrl }}" 
                         alt="{{ $image->original_name }}">
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
                    <span>💡 Tip: Choose <strong>Reduce Size</strong> to hit 10KB, 50KB or 100KB, or <strong>Resize</strong> for custom dimensions.</span>
                </div>
            </div>
        </div>

        <!-- Settings Sidebar Panel -->
        <aside class="panel editor-panel">
            <div class="panel-header-row">
                <h2>Image Settings</h2>
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

            <div class="tool-tabs">
                <button type="button" class="tool-tab-btn @if($image->last_action === 'compressed' || !session('status')) active @endif" id="tabBtnCompress" data-target="panelCompress">
                    ⚡ Reduce Size (KB)
                </button>
                <button type="button" class="tool-tab-btn @if($image->last_action !== 'compressed' && session('status')) active @endif" id="tabBtnResize" data-target="panelResize">
                    🎨 Resize &amp; Background
                </button>
            </div>

            <!-- COMPRESS TAB -->
            <div id="panelCompress" class="tool-tab-panel" style="@if($image->last_action !== 'compressed' && session('status')) display: none; @endif">
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
                        <p class="field-hint">Intelligently optimizes quality and resolution so your file reaches &le; target KB without blocky artifacts.</p>
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

                    <button class="button primary-btn compress-btn" type="submit" id="btnSubmitCompress">
                        <span class="btn-text">⚡ Compress to Target Size</span>
                    </button>
                </form>
            </div>

            <!-- RESIZE TAB -->
            <div id="panelResize" class="tool-tab-panel" style="@if($image->last_action === 'compressed' || !session('status')) display: none; @endif">
                <form id="processForm" class="stack-form" method="POST" action="{{ route('images.process', $image) }}">
                    @csrf

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

                    <!-- Background Options -->
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

                    <!-- AI Background Removal -->
                    <label class="check-row ai-removal-badge">
                        <input type="checkbox" name="remove_background" id="checkRemoveBg" value="1" @if($image->background_removed) checked @endif>
                        <span><strong>Remove background with AI</strong></span>
                    </label>

                    <!-- Admin Backgrounds -->
                    @if($backgroundAssets->isNotEmpty())
                        <div class="form-group">
                            <span class="field-label">Scenic Backgrounds</span>
                            <div class="background-picker">
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

                    <button class="button primary-btn" type="submit" id="btnSubmitProcess">
                        <span class="btn-text">Apply &amp; Process Image</span>
                    </button>
                </form>
            </div>

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
            const btnToggleOriginal = document.getElementById('btnToggleOriginal');
            const toggleText = document.getElementById('toggleText');

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
            lockAspect.addEventListener('change', function() {
                lockIcon.innerHTML = this.checked ? '&#128274; Lock ratio' : '&#128275; Free ratio';
            });

            function updateTargetBadge() {
                const w = parseInt(inputWidth.value, 10);
                const h = parseInt(inputHeight.value, 10);
                if (w && h) {
                    targetBadge.style.display = 'inline-flex';
                    targetSizeText.textContent = `${w} × ${h} px`;
                } else {
                    targetBadge.style.display = 'none';
                }
            }

            // Width change -> recalculate height if aspect locked
            inputWidth.addEventListener('input', function() {
                const w = parseInt(this.value, 10);
                if (lockAspect.checked && w && !isNaN(w)) {
                    inputHeight.value = Math.max(1, Math.round(w / aspectRatio));
                }
                updateTargetBadge();
            });

            // Height change -> recalculate width if aspect locked
            inputHeight.addEventListener('input', function() {
                const h = parseInt(this.value, 10);
                if (lockAspect.checked && h && !isNaN(h)) {
                    inputWidth.value = Math.max(1, Math.round(h * aspectRatio));
                }
                updateTargetBadge();
            });

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
                        lockAspect.checked = false;
                        lockIcon.innerHTML = '&#128275; Free ratio';
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
                    lockAspect.checked = true;
                    lockIcon.innerHTML = '&#128274; Lock ratio';
                    if (presetSelect) presetSelect.value = '';
                    updateTargetBadge();
                });
            }

            // Live Background Color Preview
            const bgMap = {
                'white': '#ffffff',
                'studio': '#e8ecee',
                'sky': '#d2e8fa',
                'forest': '#d6e8d8',
                'transparent': 'transparent'
            };

            function updateStageBackground() {
                const val = selectBg.value;
                if (val in bgMap) {
                    previewStage.style.backgroundColor = bgMap[val];
                } else {
                    previewStage.style.backgroundColor = '';
                }
            }

            selectBg.addEventListener('change', updateStageBackground);
            updateStageBackground();

            // Toggle Original vs Processed
            let showingOriginal = false;
            if (btnToggleOriginal) {
                btnToggleOriginal.addEventListener('click', function() {
                    showingOriginal = !showingOriginal;
                    if (showingOriginal) {
                        mainImg.src = mainImg.getAttribute('data-original-src');
                        toggleText.innerHTML = '&#128260; View Processed';
                    } else {
                        mainImg.src = mainImg.getAttribute('data-processed-src');
                        toggleText.innerHTML = '&#128065; View Original';
                    }
                });
            }

            // Tab toggling logic (Compress vs Resize)
            const tabBtnCompress = document.getElementById('tabBtnCompress');
            const tabBtnResize = document.getElementById('tabBtnResize');
            const panelCompress = document.getElementById('panelCompress');
            const panelResize = document.getElementById('panelResize');

            if (tabBtnCompress && tabBtnResize) {
                tabBtnCompress.addEventListener('click', function() {
                    tabBtnCompress.classList.add('active');
                    tabBtnResize.classList.remove('active');
                    panelCompress.style.display = 'block';
                    panelResize.style.display = 'none';
                });

                tabBtnResize.addEventListener('click', function() {
                    tabBtnResize.classList.add('active');
                    tabBtnCompress.classList.remove('active');
                    panelResize.style.display = 'block';
                    panelCompress.style.display = 'none';
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
                    btnSubmitCompress.querySelector('.btn-text').textContent = 'Compressing... Please wait';
                    btnSubmitCompress.style.opacity = '0.7';
                    btnSubmitCompress.style.cursor = 'wait';
                });
            }

            // Form submitting state (Resize form)
            form.addEventListener('submit', function() {
                btnSubmit.disabled = true;
                btnSubmit.querySelector('.btn-text').textContent = 'Processing... Please wait';
                btnSubmit.style.opacity = '0.7';
                btnSubmit.style.cursor = 'wait';
            });

            updateTargetBadge();
        });
    </script>
@endsection
