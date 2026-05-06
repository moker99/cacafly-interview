@extends('layouts.app')

@section('title', '個人頁面 – Cacafly Demo')

@push('styles')
<style>
    .profile-header {
        display: flex; align-items: center; gap: 20px;
        margin-bottom: 24px;
    }
    .profile-header img {
        width: 80px; height: 80px; border-radius: 50%;
        border: 3px solid #e4e6eb; object-fit: cover;
    }
    .profile-header .info h2 { font-size: 1.4rem; margin-bottom: 4px; }
    .profile-header .info span {
        font-size: 0.9rem; color: #606770;
        background: #e4e6eb; padding: 2px 10px; border-radius: 20px;
    }

    .detail-row {
        display: flex; gap: 12px; align-items: center;
        padding: 10px 0; border-bottom: 1px solid #f0f2f5;
        font-size: 0.95rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { width: 100px; color: #606770; flex-shrink: 0; }

    .liked-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px; margin-top: 16px;
    }
    .liked-card {
        border: 1px solid #dddfe2; border-radius: 8px;
        padding: 14px; display: flex; align-items: center; gap: 12px;
    }
    .liked-card img {
        width: 48px; height: 48px; border-radius: 6px; object-fit: cover;
    }
    .liked-card .page-info .name { font-weight: 600; font-size: 0.9rem; }
    .liked-card .page-info .cat  { font-size: 0.8rem; color: #606770; margin-top: 2px; }

    .empty-note {
        text-align: center; padding: 32px; color: #8d949e;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')

{{-- ── Q1: User Profile ─────────────────────────────────────────── --}}
<div class="card">
    <div class="profile-header">
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="avatar">
        @else
            <div style="width:80px;height:80px;border-radius:50%;background:#1877f2;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700;">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
        @endif
        <div class="info">
            <h2>{{ $user->name }}</h2>
            <span>{{ ucfirst($user->provider ?? 'local') }}</span>
        </div>
    </div>

    <div class="detail-row">
        <span class="label">Email</span>
        <span>{{ $user->email ?? '（未提供）' }}</span>
    </div>
    <div class="detail-row">
        <span class="label">登入方式</span>
        <span>{{ ucfirst($user->provider ?? '—') }}</span>
    </div>
    <div class="detail-row">
        <span class="label">帳號 ID</span>
        <span>{{ $user->provider_id ?? '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="label">加入時間</span>
        <span>{{ $user->created_at->format('Y-m-d H:i') }}</span>
    </div>
</div>

{{-- ── Q1: Liked Pages ──────────────────────────────────────────── --}}
<div class="card">
    <h3 style="margin-bottom:4px;">按讚的粉絲專頁</h3>
    <p style="font-size:.85rem;color:#606770;margin-bottom:12px;">
        @if($user->provider === 'facebook')
            透過 Facebook Graph API <code>GET /me/likes</code> 取得，需 <code>user_likes</code> 權限。
        @elseif($user->provider === 'google')
            Google 帳號無「按讚頁面」功能，此欄僅適用於 Facebook 登入。
        @endif
    </p>

    @if($user->provider === 'facebook' && !empty($user->liked_pages))
        <div class="liked-grid">
            @foreach($user->liked_pages as $page)
            <div class="liked-card">
                @if(!empty($page['picture']['data']['url']))
                    <img src="{{ $page['picture']['data']['url'] }}" alt="{{ $page['name'] }}">
                @else
                    <div style="width:48px;height:48px;border-radius:6px;background:#1877f2;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                        {{ mb_substr($page['name'], 0, 1) }}
                    </div>
                @endif
                <div class="page-info">
                    <div class="name">{{ $page['name'] }}</div>
                    <div class="cat">{{ $page['category'] ?? '' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    @elseif($user->provider === 'facebook')
        <div class="empty-note">目前沒有公開的按讚頁面，或 <code>user_likes</code> 權限尚未授予。</div>
    @else
        <div class="empty-note">——</div>
    @endif
</div>

@endsection
