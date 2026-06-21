@extends('layouts.app')

@section('content')
    <section class="editor-grid">
        <div class="preview-stage">
            <img src="{{ asset('storage/' . $image->displayPath()) }}" alt="{{ $image->original_name }}">
        </div>

        <aside class="panel">
            <h1>{{ $image->original_name }}</h1>
            <dl class="meta">
                <div><dt>Original</dt><dd>{{ $image->width }} x {{ $image->height }}</dd></div>
                <div><dt>Category</dt><dd>{{ $image->category ?: 'None' }}</dd></div>
                <div><dt>Last action</dt><dd>{{ ucfirst($image->last_action) }}</dd></div>
            </dl>

            <form class="stack-form" method="POST" action="{{ route('images.process', $image) }}">
                @csrf
                <label>
                    <span>Output format</span>
                    <select name="format" required>
                        <option value="png">PNG</option>
                        <option value="jpg">JPG</option>
                        <option value="webp">WEBP</option>
                    </select>
                </label>

                <div class="two-col">
                    <label>
                        <span>Width</span>
                        <input type="number" name="width" min="32" max="5000" placeholder="Auto">
                    </label>
                    <label>
                        <span>Height</span>
                        <input type="number" name="height" min="32" max="5000" placeholder="Auto">
                    </label>
                </div>

                <label>
                    <span>Background</span>
                    <select name="background">
                        @foreach($backgrounds as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="check-row">
                    <input type="checkbox" name="remove_background" value="1">
                    <span>Remove background with AI</span>
                </label>

                <div>
                    <span class="field-label">Admin uploaded background</span>
                    <div class="background-picker">
                        <label class="background-option">
                            <input type="radio" name="background_asset_id" value="">
                            <span class="empty-bg">None</span>
                        </label>
                        @foreach($backgroundAssets as $backgroundAsset)
                            <label class="background-option">
                                <input type="radio" name="background_asset_id" value="{{ $backgroundAsset->id }}">
                                <img src="{{ asset('storage/' . $backgroundAsset->path) }}" alt="{{ $backgroundAsset->name }}">
                                <strong>{{ $backgroundAsset->name }}</strong>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button class="button" type="submit">Process image</button>
            </form>

            <div class="action-row">
                <a class="button secondary" href="{{ route('images.download', $image) }}">Download</a>
                <form method="POST" action="{{ route('images.destroy', $image) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button danger" type="submit">Delete</button>
                </form>
            </div>
        </aside>
    </section>
@endsection
