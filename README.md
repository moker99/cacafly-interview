# Cacafly Interview — Laravel 13 Demo

面試題目實作，包含兩個功能模組：

- **Q1**：Facebook / Google OAuth 登入，取得 User Profile 與按讚粉絲專頁
- **Q2**：上傳最多 3 張圖片至 Dropbox 雲端，含每張即時進度條與完成通知

---

## 環境需求

| 工具 | 版本 |
|------|------|
| Docker | 20+ |
| Docker Compose | 2+ |

不需要本機安裝 PHP 或 Composer，所有指令皆在 Docker 容器內執行。

---

## 快速部署

### 1. 複製專案

```bash
git clone <repository-url>
cd cacafly-interview
```

### 2. 建立環境設定檔

```bash
cp .env.example .env
```

開啟 `.env`，填入以下必要設定：

```env
# Q1 — Google OAuth
GOOGLE_CLIENT_ID=       # Google Cloud Console 取得
GOOGLE_CLIENT_SECRET=   # Google Cloud Console 取得

# Q1 — Facebook OAuth
FACEBOOK_CLIENT_ID=     # Meta for Developers 取得
FACEBOOK_CLIENT_SECRET= # Meta for Developers 取得

# Q2 — Dropbox 上傳
DROPBOX_ACCESS_TOKEN=   # Dropbox App Console 取得
```

> 詳細取得方式見下方「第三方服務設定」

### 3. 啟動 Docker

```bash
docker-compose up -d
```

### 4. 安裝 PHP 套件

```bash
docker exec -w /var/www cacafly_app composer install
```

### 5. 產生 App Key

```bash
docker exec -w /var/www cacafly_app php artisan key:generate
```

### 6. 執行資料庫 Migration

```bash
docker exec -w /var/www cacafly_app php artisan migrate
```

### 7. 建立 Storage 連結

```bash
docker exec -w /var/www cacafly_app php artisan storage:link
```

### 8. 開啟瀏覽器

```
http://localhost:8080
```

---

## 第三方服務設定

### Google OAuth

1. 前往 [Google Cloud Console](https://console.cloud.google.com/)
2. 建立專案 → **API 和服務** → **憑證** → **建立 OAuth 用戶端 ID**
3. 應用程式類型選「**網頁應用程式**」
4. 已授權的重新導向 URI 填入：
   ```
   http://localhost:8080/auth/google/callback
   ```
5. 複製 Client ID 與 Client Secret 填入 `.env`

### Facebook OAuth

1. 前往 [Meta for Developers](https://developers.facebook.com/)
2. **My Apps** → **Create App** → 選「**Consumer**」
3. 新增 **Facebook Login** 產品 → 選「**Web**」
4. **Facebook Login → Settings → Valid OAuth Redirect URIs** 填入：
   ```
   http://localhost:8080/auth/facebook/callback
   ```
5. **App Settings → Basic** 複製 App ID 與 App Secret 填入 `.env`

> `user_likes`（按讚頁面）為 Meta 受限權限，需通過 App Review 才能對所有用戶開放。開發模式下 App 管理員帳號可自行測試。

### Dropbox

1. 前往 [Dropbox App Console](https://www.dropbox.com/developers/apps)
2. **Create App** → **Scoped Access** → **Full Dropbox**
3. **Permissions** 分頁勾選 `files.content.write` 與 `files.content.read` → Submit
4. **Settings** 分頁 → **OAuth 2 → Generated access token** → Generate
5. 複製 token 填入 `.env`

上傳的圖片會存放於 Dropbox 帳號的 `uploads/` 資料夾。

---

## 常用指令

```bash
# 清除設定快取（修改 .env 後執行）
docker exec -w /var/www cacafly_app php artisan config:clear

# 查看 Laravel log
docker exec cacafly_app cat /var/www/storage/logs/laravel.log

# 重新執行 Migration
docker exec -w /var/www cacafly_app php artisan migrate:fresh

# 停止 Docker
docker-compose down
```

---

## 專案結構

```
app/
├── Http/Controllers/
│   ├── AuthController.php     # Q1: Google / Facebook OAuth
│   └── UploadController.php   # Q2: Dropbox 圖片上傳
├── Models/
│   ├── User.php               # 使用者模型（含 OAuth 欄位）
│   └── ImageUpload.php        # 上傳紀錄模型
└── Providers/
    └── AppServiceProvider.php # 註冊 Dropbox Filesystem Driver

resources/views/
├── layouts/app.blade.php      # 共用版面
├── auth/login.blade.php       # 登入頁（Q1）
├── dashboard.blade.php        # 個人頁面 + 按讚清單（Q1）
└── upload/index.blade.php     # 圖片上傳頁（Q2）
```

---

## 技術棧

| 類別 | 使用技術 |
|------|---------|
| 後端框架 | Laravel 13 |
| 社群登入 | Laravel Socialite 5 |
| 雲端儲存 | Dropbox（spatie/flysystem-dropbox） |
| 資料庫 | MySQL 8.0 |
| Web Server | Nginx + PHP-FPM 8.3 |
| 容器化 | Docker / Docker Compose |
