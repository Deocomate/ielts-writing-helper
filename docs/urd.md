# TÀI LIỆU YÊU CẦU NGƯỜI DÙNG (URD)

- Tên dự án: Lách IELTS
- Phiên bản: 2.1
- Ngày cập nhật: 2026-04-13

Mục tiêu của URD là mô tả đầy đủ yêu cầu chức năng, phi chức năng và tiêu chí nghiệm thu cho nền tảng luyện IELTS Writing.

## 1. Bối cảnh và mục tiêu

Lách IELTS là nền tảng học IELTS Writing theo mô hình Freemium, tập trung vào 3 trục:

1. Học qua thao tác gõ (Dictation).
2. Học qua phân tích bài mẫu (Analyze).
3. Học qua mô phỏng thi thật và phản hồi AI (Mock Exam).

Mục tiêu kinh doanh:

- Tăng chuyển đổi người dùng Free sang Pro bằng giá trị sản phẩm rõ ràng.
- Giữ chân người học bằng feedback liên tục và analytics tiến bộ.

Mục tiêu sản phẩm:

- Cải thiện kỹ năng viết theo chuẩn band descriptors IELTS.
- Cung cấp trải nghiệm học có đo lường và theo dõi được.

## 2. Đối tượng sử dụng và phân quyền

### 2.1 Persona

- Free Learner: người dùng mới, trải nghiệm các nội dung và tính năng giới hạn.
- Pro Learner: người dùng trả phí, mở khóa đầy đủ học liệu và Mock Exam.
- Admin: vận hành nội dung và người dùng theo phạm vi được cấp quyền.
- Superadmin: toàn quyền hệ thống, bao gồm quản lý tài khoản admin.

### 2.2 Role matrix

| Chức năng | Free | Pro | Admin | Superadmin |
|---|---|---|---|---|
| Dictation | Co | Co | Khong ap dung | Khong ap dung |
| Analyze | Co (theo tag truy cap) | Co | Khong ap dung | Khong ap dung |
| Mock Exam | Khong | Co | Khong ap dung | Khong ap dung |
| Checkout | Co | Co | Khong ap dung | Khong ap dung |
| Dashboard | Co | Co | Khong ap dung | Khong ap dung |
| Quan tri bai hoc | Khong | Khong | Co | Co |
| Quan tri admin account | Khong | Khong | Khong | Co |

## 3. Phạm vi chức năng (Functional Requirements)

### FR-01. Đăng ký/đăng nhập và tài khoản

Mô tả:

- Hỗ trợ đăng ký và đăng nhập bằng email/password.
- Hỗ trợ social login qua Google và Facebook.
- Cho phép quên mật khẩu và đặt lại mật khẩu.

Tiêu chí nghiệm thu:

1. Người dùng đăng ký thành công được chuyển vào luồng sử dụng.
2. Đăng nhập social callback hợp lệ tạo hoặc liên kết tài khoản.
3. Người dùng có thể cập nhật profile và đổi mật khẩu ở dashboard.

### FR-02. Thư viện bài học

Mô tả:

- Hiển thị danh sách bài học theo task type, question type, band score, access level.
- Hỗ trợ tìm kiếm và sắp xếp.

Tiêu chí nghiệm thu:

1. Bộ lọc áp dụng đúng và trả về danh sách tương ứng.
2. Bài premium được đánh dấu rõ.
3. Người dùng có thể đi vào đúng chế độ học từ bài đã chọn.

### FR-03. Dictation mode

Mô tả:

- Hiển thị bài mẫu để người học gõ lại.
- Theo dõi WPM và accuracy.
- Lưu lịch sử dictation để báo cáo và dashboard.

Tiêu chí nghiệm thu:

1. Bài premium bị chặn với user chưa Pro.
2. API lưu kết quả bắt buộc đủ `lesson_id`, `wpm`, `accuracy`.
3. Màn report dictation hiển thị đúng dữ liệu đã lưu.

### FR-04. Analyze mode

Mô tả:

- Hiển thị sample essay với annotation (tag + giải thích).
- Tooltip giải thích theo từng vùng mapping.

Tiêu chí nghiệm thu:

1. Annotation hiển thị đúng vị trí đã mapping.
2. Semantic màu sắc khớp loại annotation.
3. Nội dung bị giới hạn theo quyền truy cập được xử lý đúng.

### FR-05. Mock Exam mode và AI grading

Mô tả:

- Chỉ user Pro được vào intro/room/submit.
- User nộp bài, hệ thống đẩy vào queue chấm AI bất đồng bộ.
- Trả report gồm overall band, TR, CC, LR, GRA, feedback chi tiết.

Tiêu chí nghiệm thu:

1. User không Pro nhận phản hồi 403 khi vào luồng thi.
2. Submit tạo bài thi trạng thái grading và trả về report page.
3. Sau khi job hoàn tất, report hiển thị điểm và nhận xét đầy đủ.
4. Nếu AI lỗi sau toàn bộ retry, hệ thống có trạng thái fail và thông báo rõ.

### FR-06. Dashboard người học

Mô tả:

- Hiển thị thống kê học tập tổng quan.
- Hiển thị analytics theo khoảng thời gian.
- Quản lý từ vựng cá nhân và lịch sử thanh toán.

Tiêu chí nghiệm thu:

1. Thống kê và biểu đồ thay đổi theo bộ lọc thời gian.
2. Người dùng thêm/xóa từ vựng thành công.
3. Lịch sử giao dịch hiển thị đúng plan và trạng thái.

### FR-07. Checkout và subscription

Mô tả:

- Người dùng chọn plan và tạo transaction qua PayOS.
- Màn pending theo dõi trạng thái giao dịch.
- Webhook cập nhật trạng thái thanh toán và quyền sử dụng.

Tiêu chí nghiệm thu:

1. Chỉ plan hợp lệ mới được tạo transaction.
2. Webhook sai chữ ký bị từ chối.
3. Transaction success cập nhật subscription user tương ứng.

### FR-08. Quản trị nội dung (Admin)

Mô tả:

- CRUD lesson, nhập prompt/sample essay/image.
- Mapping annotation và vocabulary cho lesson.
- Quản lý plan, transaction, client account và AI settings.

Tiêu chí nghiệm thu:

1. Admin có thể tạo/sửa/xóa lesson và metadata.
2. Mapping annotation hiển thị được ở Analyze mode phía client.
3. Trạng thái client/subscription cập nhật đúng theo thao tác admin.

### FR-09. Quản trị tài khoản admin

Mô tả:

- Superadmin quản lý tài khoản admin/superadmin theo policy hệ thống.

Tiêu chí nghiệm thu:

1. Admin thường không truy cập được chức năng superadmin-only.
2. Chính sách tài khoản superadmin được bảo toàn.

## 4. Yêu cầu phi chức năng (Non-Functional Requirements)

### NFR-01. Hiệu năng

- Typing feedback ở Dictation phải phản hồi tức thời ở phía client.
- Submit Mock Exam không bị block bởi tiến trình chấm AI.
- Hệ thống poll/trả trạng thái chấm phải ổn định và nhất quán.

### NFR-02. Độ tin cậy

- Queue job grading có retry/backoff và cơ chế failed fallback.
- Webhook phải xử lý an toàn và không gây hỏng dữ liệu khi gọi lặp.

### NFR-03. Bảo mật

- Tất cả route nhạy cảm dùng middleware đúng ngữ cảnh.
- Secret/API key chỉ nằm trong env/config.
- Dữ liệu thanh toán không lưu thông tin thẻ ngân hàng của người dùng.

### NFR-04. Trải nghiệm người dùng

- Giao diện thống nhất với template và style guidelines.
- Analyze/Dictation/Mock Exam hiển thị trạng thái rõ ràng: loading, success, error.
- Desktop-first cho Dictation và Mock Exam, mobile có thông báo phù hợp.

### NFR-05. Khả năng bảo trì

- Tuân thủ flow route -> model -> service -> controller -> view.
- Không đưa business logic vào Blade.
- Tài liệu README, rules, style-guidelines, URD phải đồng bộ thuật ngữ.

## 5. Tích hợp hệ thống ngoài

### INT-01. OpenRouter

- Dùng để chấm điểm bài Mock Exam.
- Trả về dữ liệu JSON có điểm thành phần và feedback.

### INT-02. PayOS

- Dùng để tạo giao dịch và xác nhận thanh toán qua webhook.

### INT-03. Social OAuth

- Dùng Google/Facebook cho luồng đăng nhập nhanh.

## 6. Mô hình dữ liệu nghiệp vụ chính

- User
- Lesson
- LessonAnnotation
- LessonVocabulary
- DictationHistory
- MockExam
- Plan
- Transaction
- UserVocabulary
- AiAssistantSetting

## 7. Business rules bắt buộc

1. Mock Exam chỉ mở cho user Pro.
2. Bài học premium phải kiểm tra quyền trước khi truy cập.
3. Subscription thay đổi dựa trên giao dịch hợp lệ.
4. Không chấm điểm đồng bộ trong request cycle.
5. Admin và client không dùng chung layout/component không kiểm soát.

## 8. Ngoài phạm vi (Out of scope hiện tại)

- Chấm điểm Speaking/Reading/Listening.
- Native mobile app độc lập.
- Hệ thống thanh toán đa cổng ngoài PayOS trong phiên bản hiện tại.

## 9. Traceability matrix

| Requirement | Module chính | Đầu ra mong đợi |
|---|---|---|
| FR-01 | Auth + Dashboard | Đăng nhập và quản lý tài khoản ổn định |
| FR-02 | Lesson Library | Chọn đúng bài và đúng chế độ học |
| FR-03 | Dictation | Lưu kết quả tốc độ và độ chính xác |
| FR-04 | Analyze | Hiển thị mapping phân tích trực quan |
| FR-05 | Mock Exam + Queue + AI | Có report band score và feedback |
| FR-06 | Dashboard | Theo dõi tiến bộ và từ vựng cá nhân |
| FR-07 | Checkout + PayOS | Thanh toán và nâng cấp gói thành công |
| FR-08 | Admin Content | Vận hành học liệu và metadata |
| FR-09 | Admin Access Control | Quản trị vai trò đúng phân quyền |

## 10. Checklist nghiệm thu release

1. Tất cả FR đều có đường đi UI và API hợp lệ.
2. Luồng Free/Pro không bị bypass ở route/controller/service.
3. Mock Exam grading chạy qua queue với trạng thái rõ ràng.
4. Checkout webhook xử lý được trường hợp success/failed/signature invalid.
5. Dashboard hiển thị đúng dữ liệu tổng hợp.
6. Tài liệu README, rules, style-guidelines, URD đồng nhất thuật ngữ.
