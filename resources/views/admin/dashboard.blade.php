@extends('layouts.app')

@section('content')
    <div class="section-head">
        <h1>Admin dashboard</h1>
        <div class="action-row no-margin">
            <a class="button small" href="{{ route('admin.images') }}">All images</a>
            <a class="button secondary small" href="{{ route('admin.backgrounds') }}">Background library</a>
        </div>
    </div>

    <section class="stats-grid">
        <div class="stat"><span>{{ $imageCount }}</span><strong>Total images</strong></div>
        <div class="stat"><span>{{ $backgroundCount }}</span><strong>Backgrounds</strong></div>
        <div class="stat"><span>{{ $userCount }}</span><strong>Users</strong></div>
        <div class="stat"><span>{{ $guestImageCount }}</span><strong>Guest images</strong></div>
    </section>

    <section class="panel">
        <h2>Recent activity</h2>
        <table>
            <thead>
                <tr><th>Action</th><th>Image</th><th>When</th></tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                    <tr>
                        <td>{{ ucfirst($activity->action) }}</td>
                        <td>{{ optional($activity->image)->original_name ?: 'Deleted image' }}</td>
                        <td>{{ $activity->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
