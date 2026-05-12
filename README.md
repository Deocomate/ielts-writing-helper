# Lách IELTS

Nền tảng luyện IELTS Writing theo mô hình Freemium, tập trung vào 3 chế độ học cốt lõi: Dictation, Analyze và Mock Exam chấm điểm AI.

## 1. Mục tiêu sản phẩm

- Biến luyện Writing thành quy trình học có phản hồi tức thời.
- Tăng tốc cải thiện từ vựng, cấu trúc câu và tư duy triển khai luận điểm.
- Cung cấp môi trường thi thử sát thực tế, có chấm điểm theo 4 tiêu chí IELTS.
- Hỗ trợ vận hành mô hình Free -> Pro với thanh toán trực tuyến.

## 2. Các module chính

| Module | Mô tả | Quyền truy cập |
|---|---|---|
| Dictation | Gõ lại sample essay, highlight đúng/sai theo thời gian thực, lưu WPM/Accuracy | Free và Pro |
| Analyze | Đọc bài mẫu có mapping annotation và tooltip giải thích | Free và Pro (nội dung Pro có giới hạn theo gói) |
| Mock Exam | Viết bài trong phòng thi, nộp bài, AI chấm điểm và trả report chi tiết | Pro |
| Dashboard | Thống kê học tập, hồ sơ cá nhân, sổ tay từ vựng, lịch sử thanh toán | Người dùng đã đăng nhập |
| Checkout | Mua gói qua PayOS, theo dõi trạng thái giao dịch | Người dùng đã đăng nhập |
| Admin Panel | Quản lý bài học, annotation, từ vựng, plan, transaction, client, cấu hình AI | Admin và Superadmin |

## 3. Kiến trúc hệ thống

Luồng triển khai bắt buộc của dự án:

`routes/web.php -> app/Models -> app/Services -> app/Http/Controllers -> resources/views`

Đặc điểm kiến trúc:

- Routing tách client và admin rõ ràng.
- Business logic đặt tại service layer.
- Dữ liệu nghiệp vụ được quản lý qua Eloquent models.
- Mock Exam grading chạy bất đồng bộ bằng queue job.
- View bám sát template tĩnh trong thư mục templates.

## 4. Công nghệ và phiên bản

| Nhóm | Công nghệ |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Authentication | Laravel Auth + Laravel Socialite (Google/Facebook) |
| Frontend | Blade + Vanilla JS + Tailwind CDN theo chuẩn dự án |
| Build assets | Vite |
| AI scoring | OpenRouter (OpenAI-compatible API) |
| Payment | PayOS |
| Queue | Database queue |
| Test | Pest + PHPUnit |
| Formatter | Laravel Pint |

## 5. Cấu trúc thư mục trọng yếu

```text
app/
    Helpers/                # Tiện ích dùng lại
    Http/Controllers/       # Controller Client/Admin
    Jobs/                   # Job bất đồng bộ (AI grading)
    Models/                 # Eloquent models
    Services/               # Nghiệp vụ chính
    Services/Client/        # Nghiệp vụ phía client

resources/views/
    admin/                  # Màn hình admin
    client/                 # Màn hình client
    components/             # Blade components theo ngữ cảnh admin/client

routes/web.php            # Tất cả route web chính
templates/                # Template HTML chuẩn để map vào Blade
docs/                     # Tài liệu dự án
database/                 # Migration, factory, seeder
```

## 6. Cài đặt local

### Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- Node.js >= 18
- MariaDB/MySQL

### Cài đặt nhanh

```bash
git clone <repo-url>
cd ielts-writing-helper

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
```

Ngoài cách thủ công, có thể dùng script:

```bash
composer run setup
```

## 7. Biến môi trường quan trọng

### Nhóm ứng dụng

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`

### Nhóm database/queue/session

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `QUEUE_CONNECTION` (khuyến nghị `database`)
- `SESSION_DRIVER`, `CACHE_STORE`

### Nhóm social login

- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI`

### Nhóm AI grading

- `OPENROUTER_API_KEY`
- `OPENROUTER_API_URL`
- `OPENROUTER_MODEL`
- `OPENROUTER_SITE_URL`, `OPENROUTER_SITE_NAME`

### Nhóm thanh toán PayOS

- `PAYOS_CLIENT_ID`
- `PAYOS_API_KEY`
- `PAYOS_CHECKSUM_KEY`
- `PAYOS_RETURN_ROUTE`, `PAYOS_CANCEL_ROUTE`, `PAYOS_WEBHOOK_ROUTE`

## 8. Chạy ứng dụng ở môi trường phát triển

Khuyến nghị chạy tối thiểu 3 tiến trình:

```bash
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:listen --tries=1

# Terminal 3
npm run dev
```

Hoặc dùng một lệnh duy nhất:

```bash
composer run dev
```

Truy cập: `http://localhost:8000`

## 9. Luồng runtime quan trọng

### 9.1 Mock Exam AI Grading

1. Người dùng Pro nộp bài tại phòng thi.
2. Hệ thống tạo bản ghi bài thi với trạng thái grading.
3. Job `GradeMockExamJob` gọi OpenRouter để chấm điểm.
4. Kết quả được chuẩn hóa điểm TR/CC/LR/GRA và lưu report.
5. Màn hình report/polling trạng thái hiển thị kết quả cho người dùng.

### 9.2 Checkout PayOS

1. Người dùng chọn plan tại checkout.
2. Hệ thống tạo transaction trạng thái pending.
3. Người dùng thanh toán qua QR/link PayOS.
4. Webhook cập nhật trạng thái success/failed.
5. Subscription user được cập nhật theo transaction đã xác nhận.

## 10. Kiểm thử và chất lượng mã

```bash
php artisan test
```

Hoặc:

```bash
composer run test
```

Format code PHP:

```bash
vendor/bin/pint --dirty
```

## 11. Troubleshooting nhanh

- Lỗi AI không chấm điểm:
    - Kiểm tra `OPENROUTER_API_KEY`.
    - Kiểm tra queue worker có đang chạy không.
- Giao dịch đứng pending quá lâu:
    - Kiểm tra webhook route PayOS và cấu hình chữ ký.
- Không đăng nhập social được:
    - Kiểm tra callback URL ở provider có khớp `APP_URL`.
- Lỗi session/cache database:
    - Đảm bảo đã migrate đầy đủ các bảng hệ thống.

## 12. Tài liệu dự án

- [docs/urd.md](docs/urd.md): Đặc tả yêu cầu nghiệp vụ và kỹ thuật.
- [docs/rules.md](docs/rules.md): Quy tắc triển khai và coding standards.
- [docs/style-guidelines.md](docs/style-guidelines.md): Quy chuẩn UI/UX và hành vi giao diện.

## 13. License

Dự án phát hành theo giấy phép MIT.
