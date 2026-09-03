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
                    @if($image->processed_path)
                        <span class="badge success">Processed</span>
                    @else
                        <span class="badge muted">Original</span>
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
                    <span class="file-size">{{ $image->formattedSize() }}</span>
                </div>
                <div class="preview-hint">
                    <span>💡 Tip: Adjust width, height or presets and click <strong>Process image</strong></span>
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

            // Form submitting state
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
