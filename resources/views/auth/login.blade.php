@extends('layouts.app')

@section('title', '登入 – Cacafly Demo')

@push('styles')
<style>
    body { display: flex; align-items: center; justify-content: center; }

    .login-wrap {
        width: 100%; max-width: 420px;
        text-align: center;
    }

    .login-wrap h1 {
        font-size: 2rem; color: #1877f2; margin-bottom: 8px;
    }

    .login-wrap p {
        color: #606770; margin-bottom: 32px; font-size: 0.95rem;
    }

    .social-btn {
        display: flex; align-items: center; justify-content: center;
        width: 100%; padding: 13px 0;
        border-radius: 8px; border: none;
        font-size: 1rem; font-weight: 600; cursor: pointer;
        text-decoration: none; margin-bottom: 14px;
        transition: filter .15s;
    }
    .social-btn:hover { filter: brightness(0.92); }

    .social-btn svg { margin-right: 10px; }

    .btn-google   { background: #fff; border: 1.5px solid #dddfe2; color: #3c4043; }
    .btn-facebook { background: #1877f2; color: #fff; }

    .divider {
        display: flex; align-items: center; gap: 12px;
        color: #8d949e; font-size: 0.85rem; margin: 20px 0;
    }
    .divider::before, .divider::after {
        content: ''; flex: 1; height: 1px; background: #dddfe2;
    }
</style>
@endpush

@section('content')
<div class="login-wrap">
    <div class="card">
        <h1>Cacafly Demo</h1>
        <p>使用 Google 或 Facebook 帳號登入</p>

        {{-- Google --}}
        <a href="{{ route('auth.google.redirect') }}" class="social-btn btn-google">
            {{-- Google "G" Logo SVG --}}
            <svg width="20" height="20" viewBox="0 0 48 48">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0124 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 01-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
            </svg>
            使用 Google 登入
        </a>

        <div class="divider">或</div>

        {{-- Facebook --}}
        <a href="{{ route('auth.facebook.redirect') }}" class="social-btn btn-facebook">
            <svg width="20" height="20" fill="#fff" viewBox="0 0 24 24">
                <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
            </svg>
            使用 Facebook 登入
        </a>

        <p style="margin-top:16px; font-size:.8rem; color:#8d949e;">
            登入即表示您同意我們的服務條款
        </p>
    </div>
</div>
@endsection
