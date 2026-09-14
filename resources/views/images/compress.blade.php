@extends('layouts.app')

@section('content')
    <div class="compressor-container">
        <header class="compressor-header">
            <div class="header-badge">⚡ Smart Image Compressor</div>
            <h1>Compress &amp; Reduce Image Size</h1>
            <p>Reduce 2MB+ images down to targeted sizes like <strong>10 KB</strong>, <strong>50 KB</strong>, or <strong>100 KB</strong> while preserving crisp, clear visual quality.</p>
        </header>

        <div class="compressor-workspace">
            <div class="panel compressor-card">
                <form id="standaloneCompressForm" method="POST" action="{{ route('images.compress.direct') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Dropzone -->
                    <div class="dropzone" id="dropzone">
                        <input type="file" name="image" id="fileInput" accept="image/png,image/jpeg,image/webp,image/gif" required class="dropzone-input">
                        <div class="dropzone-content" id="dropzonePrompt">
                            <div class="dropzone-icon">&#128229;</div>
                            <h3>Drag &amp; drop your image here</h3>
                            <p>or <span class="browse-btn">browse file</span> from your device</p>
                            <span class="file-limits">Supports JPG, PNG, WEBP, GIF &bull; Up to 20 MB</span>
                        </div>
                        <div class="dropzone-preview" id="dropzonePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Selected image">
                            <div class="preview-meta">
                                <span class="selected-filename" id="previewName"></span>
                                <span class="selected-filesize badge" id="previewSize"></span>
                            </div>
                            <button type="button" class="btn-change-file" id="btnChangeFile">&times; Change Image</button>
                        </div>
                    </div>

                    <!-- Compression Controls -->
                    <div class="compressor-controls">
                        <!-- Mode switch -->
                        <div class="compression-mode-toggle">
                            <label class="mode-radio">
                                <input type="radio" name="compression_type" value="target_kb" id="modeTargetKb" checked>
                                <span>🎯 Target Size (KB)</span>
                            </label>
                            <label class="mode-radio">
                                <input type="radio" name="compression_type" value="quality" id="modeQuality">
                                <span>🎨 Quality Ratio (%)</span>
                            </label>
                        </div>

                        <!-- Target KB Section -->
                        <div id="wrapperTargetKb" class="form-group">
                            <label class="field-label">Choose Target Size</label>
                            <div class="preset-pill-group kb-preset-group">
                                <button type="button" class="btn-pill kb-chip" data-kb="10">10 KB</button>
                                <button type="button" class="btn-pill kb-chip" data-kb="20">20 KB</button>
                                <button type="button" class="btn-pill kb-chip active" data-kb="50">50 KB</button>
                                <button type="button" class="btn-pill kb-chip" data-kb="100">100 KB</button>
                                <button type="button" class="btn-pill kb-chip" data-kb="200">200 KB</button>
                                <button type="button" class="btn-pill kb-chip" data-kb="500">500 KB</button>
                            </div>

                            <div class="input-with-unit" style="margin-top: 14px;">
                                <label for="standaloneTargetKb">Or enter custom target size</label>
                                <input type="number" id="standaloneTargetKb" name="target_kb" value="50" min="5" max="50000" placeholder="e.g. 50">
                                <span class="unit">KB</span>
                            </div>
                            <p class="field-hint">Output file will be guaranteed &le; this size while retaining the sharpest details.</p>
                        </div>

                        <!-- Quality Slider Section -->
                        <div id="wrapperQuality" class="form-group" style="display: none;">
                            <div class="slider-header">
                                <label class="field-label" for="standaloneQuality">Quality Percentage</label>
                                <span class="slider-val" id="standaloneQualityVal">75%</span>
                            </div>
                            <input type="range" id="standaloneQuality" name="quality" min="10" max="95" value="75" class="range-slider">
                            <div class="slider-labels">
                                <span>High Compression (10%)</span>
                                <span>Balanced (75%)</span>
                                <span>Max Quality (95%)</span>
                            </div>
                        </div>

                        <!-- Format options -->
                        <div class="form-group">
                            <label for="standaloneFormat">
                                <span class="field-label">Output Format</span>
                                <select name="format" id="standaloneFormat" required>
                                    <option value="jpg" selected>JPG (Recommended — optimal for portals, job applications &amp; photos)</option>
                                    <option value="webp">WEBP (Modern web format — up to 35% smaller at superior quality)</option>
                                    <option value="png">PNG (Lossless, best for illustrations and diagrams)</option>
                                </select>
                            </label>
                        </div>

                        <button class="button primary-btn full-width" type="submit" id="btnStandaloneSubmit">
                            <span class="btn-text">⚡ Compress Image Now</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Features & Info Sidebar -->
            <aside class="panel compressor-sidebar">
                <h2>Why use this Compressor?</h2>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <div class="feature-text">
                        <strong>Target KB Guarantee</strong>
                        <p>Perfect for online job applications, Bangladesh PSC / Teletalk, visa, passport, or portal requirements where maximum size must be e.g. 50 KB or 100 KB.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🧠</div>
                    <div class="feature-text">
                        <strong>Smart Quality Protection</strong>
                        <p>Unlike basic compressors that turn photos into blurry blocks, our algorithm adapts resolution &amp; compression factors to preserve sharp text and facial clarity.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <div class="feature-text">
                        <strong>Lightning Fast</strong>
                        <p>Powered by high-performance image processing routines (PIL &amp; GD) in under 100 milliseconds.</p>
                    </div>
                </div>

                @if($recentImages->isNotEmpty())
                    <h3 style="margin-top: 24px;">Recent Uploads</h3>
                    <div class="mini-grid">
                        @foreach($recentImages as $item)
                            <a class="mini-item" href="{{ route('images.show', $item) }}">
                                <img src="{{ asset('storage/' . $item->displayPath()) }}" alt="{{ $item->original_name }}">
                                <span>{{ $item->original_name }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const dropzone = document.getElementById('dropzone');
            const dropzonePrompt = document.getElementById('dropzonePrompt');
            const dropzonePreview = document.getElementById('dropzonePreview');
            const previewImg = document.getElementById('previewImg');
            const previewName = document.getElementById('previewName');
            const previewSize = document.getElementById('previewSize');
            const btnChangeFile = document.getElementById('btnChangeFile');
            const form = document.getElementById('standaloneCompressForm');
            const btnSubmit = document.getElementById('btnStandaloneSubmit');

            // Format bytes helper
            function formatBytes(bytes) {
                if (!bytes) return '0 B';
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return bytes + ' B';
            }

            function handleFile(file) {
                if (!file) return;
                previewName.textContent = file.name;
                previewSize.textContent = formatBytes(file.size);

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    dropzonePrompt.style.display = 'none';
                    dropzonePreview.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0]);
                }
            });

            if (btnChangeFile) {
                btnChangeFile.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.value = '';
                    dropzonePrompt.style.display = 'block';
                    dropzonePreview.style.display = 'none';
                    previewImg.src = '';
                });
            }

            // Drag and drop events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt.files && dt.files.length) {
                    fileInput.files = dt.files;
                    handleFile(dt.files[0]);
                }
            });

            // Compression mode switch
            const modeTargetKb = document.getElementById('modeTargetKb');
            const modeQuality = document.getElementById('modeQuality');
            const wrapperTargetKb = document.getElementById('wrapperTargetKb');
            const wrapperQuality = document.getElementById('wrapperQuality');
            const standaloneQuality = document.getElementById('standaloneQuality');
            const standaloneQualityVal = document.getElementById('standaloneQualityVal');
            const standaloneTargetKb = document.getElementById('standaloneTargetKb');
            const kbChips = document.querySelectorAll('.kb-chip');

            function syncMode() {
                if (modeTargetKb && modeTargetKb.checked) {
                    wrapperTargetKb.style.display = 'block';
                    wrapperQuality.style.display = 'none';
                } else {
                    wrapperTargetKb.style.display = 'none';
                    wrapperQuality.style.display = 'block';
                }
            }

            if (modeTargetKb) modeTargetKb.addEventListener('change', syncMode);
            if (modeQuality) modeQuality.addEventListener('change', syncMode);

            if (standaloneQuality && standaloneQualityVal) {
                standaloneQuality.addEventListener('input', function() {
                    standaloneQualityVal.textContent = this.value + '%';
                });
            }

            kbChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    kbChips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    if (standaloneTargetKb) {
                        standaloneTargetKb.value = this.getAttribute('data-kb');
                    }
                });
            });

            if (standaloneTargetKb) {
                standaloneTargetKb.addEventListener('input', function() {
                    const val = this.value;
                    kbChips.forEach(c => {
                        if (c.getAttribute('data-kb') === val) {
                            c.classList.add('active');
                        } else {
                            c.classList.remove('active');
                        }
                    });
                });
            }

            // Submitting state
            form.addEventListener('submit', function() {
                btnSubmit.disabled = true;
                btnSubmit.querySelector('.btn-text').textContent = 'Compressing image... Please wait';
                btnSubmit.style.opacity = '0.7';
                btnSubmit.style.cursor = 'wait';
            });
        });
    </script>
@endsection
