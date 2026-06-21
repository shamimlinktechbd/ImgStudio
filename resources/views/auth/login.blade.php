@extends('layouts.app')

@section('content')
    <section class="auth-panel">
        <h1>Login</h1>
        <form class="stack-form" method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required>
            </label>
            <label class="check-row">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <button class="button" type="submit">Login</button>
        </form>
    </section>
@endsection
