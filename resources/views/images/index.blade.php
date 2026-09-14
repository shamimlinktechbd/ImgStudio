@extends('layouts.app')

@section('content')
    <section class="workspace-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h1>Upload and process image</h1>
                    <p>Guest hisebe upload korte parben. Login korle ei kajgula account history-te save thakbe.</p>
                </div>
            </div>

            <div class="compressor-promo-card">
                <div class="promo-content">
                    <span class="promo-badge">⚡ New Feature</span>
                    <h3>Need to reduce image size (KB)?</h3>
                    <p>Compress 2 MB+ files down to <strong>10 KB</strong>, <strong>50 KB</strong>, or <strong>100 KB</strong> with crystal-clear visual quality.</p>
                </div>
                <a href="{{ route('images.compress.index') }}" class="button promo-btn">Go to Compressor &rarr;</a>
            </div>

            <form class="upload-form" method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data">
                @csrf
                <label>
                    <span>Image</span>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif" required>
                </label>

                <label>
                    <span>Category</span>
                    <select name="category">
                        <option value="">Auto / uncategorized</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" @if($selectedCategory === $value) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <button class="button" type="submit">Upload preview</button>
            </form>
        </div>

        <aside class="panel">
            <h2>Category upload</h2>
            <div class="tag-list">
                @foreach($categories as $value => $label)
                    <a class="tag" href="{{ route('images.category', $value) }}">{{ $label }}</a>
                @endforeach
            </div>
            <h2>Recent previews</h2>
            <div class="mini-grid">
                @forelse($images as $image)
                    <a class="mini-item" href="{{ route('images.show', $image) }}">
                        <img src="{{ asset('storage/' . $image->displayPath()) }}" alt="{{ $image->original_name }}">
                        <span>{{ $image->original_name }}</span>
                    </a>
                @empty
                    <p class="muted">Ekhono kono image upload kora hoyni.</p>
                @endforelse
            </div>
        </aside>
    </section>
@endsection
