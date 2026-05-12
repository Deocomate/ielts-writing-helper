# Quy tắc phát triển dự án

Tài liệu này là chuẩn triển khai bắt buộc cho toàn bộ codebase Lách IELTS.

## 1. Luồng code bắt buộc

Mọi tính năng mới hoặc chỉnh sửa logic phải đi theo luồng:

`routes/web.php -> app/Models -> app/Services -> app/Http/Controllers -> resources/views`

Ý nghĩa từng lớp:

- Route: khai báo endpoint, middleware, đặt tên route rõ nghĩa.
- Model: định nghĩa quan hệ và dữ liệu nghiệp vụ.
- Service: chứa business logic, không nhồi logic nặng vào controller.
- Controller: điều phối request/response, validate input, gọi service.
- View: chỉ xử lý hiển thị, không chứa nghiệp vụ.

## 2. Quy tắc cấu trúc thư mục

- Helper dùng chung đặt tại `app/Helpers`.
- Logic client đặt tại `app/Services/Client` và controller client tương ứng.
- Không tạo thư mục gốc mới khi chưa có phê duyệt kiến trúc.

## 3. Quy tắc phân tách admin và client

- Component tái sử dụng phải tách riêng cho admin và client.
- Không dùng lẫn component giữa hai khu vực nếu chưa có lý do rõ ràng.
- Layout của admin và client bắt buộc đặt tại:
  - `resources/views/components/admin`
  - `resources/views/components/client`

## 4. Quy tắc đặt tên

### 4.1 View

- `admin.{module}.{action}`
- `client.{module}.{action}`

Ví dụ:

- `admin.lessons.index`
- `client.learning.mock-exam-room`

### 4.2 Blade component

- `components.admin.{group}.{name}`
- `components.client.{group}.{name}`

### 4.3 Route name

- Route client bắt đầu bằng `client.`.
- Route admin bắt đầu bằng `admin.`.
- Tên route phải phản ánh đúng ngữ cảnh màn hình/hành động.

## 5. Quy tắc tích hợp template

- Trước khi sửa Blade, phải xác định template HTML tương ứng trong thư mục `templates`.
- Giữ nguyên cấu trúc DOM, class, spacing, hierarchy của template gốc.
- Chỉ thay phần dữ liệu tĩnh bằng Blade bindings/directives.
- Nếu không có template phù hợp, cần xác nhận trước khi tự thiết kế layout mới.

## 6. Quy tắc frontend

- Dự án ưu tiên Tailwind qua CDN cho UI trong Blade.
- Không đưa logic bundling frontend phức tạp vào phần giao diện chỉ cần HTML/CSS đơn giản.
- Không tự ý đổi visual language khác template chuẩn của dự án.

## 7. Quy tắc backend và Laravel

- Dùng Laravel conventions cho model/service/controller/request lifecycle.
- Validate request đầy đủ ở controller hoặc form request trước khi gọi service.
- Với logic nhạy cảm (auth, subscription, payment, AI call), luôn có xử lý lỗi và fallback.
- Không gọi `env()` trực tiếp ngoài file config.
- Không hardcode API key, secret, credential trong source code.

## 8. Quy tắc nghiệp vụ theo module

### 8.1 Dictation

- Kiểm tra quyền truy cập bài premium trước khi hiển thị.
- Kết quả lưu tối thiểu: `lesson_id`, `wpm`, `accuracy`.

### 8.2 Analyze

- Mapping annotation phải có kiểu tag rõ ràng và nội dung giải thích đầy đủ.
- Nội dung premium phải được kiểm soát theo trạng thái người dùng.

### 8.3 Mock Exam

- Chỉ user Pro được vào intro/room/submit.
- Chấm điểm AI chạy qua queue job, không block request người dùng.
- Report phải có điểm TR, CC, LR, GRA và overall band.

### 8.4 Checkout/Subscription

- Luồng thanh toán chính dùng PayOS.
- Webhook phải xác thực chữ ký và xử lý trạng thái idempotent.
- Cập nhật subscription chỉ sau khi giao dịch hợp lệ.

## 9. Quy tắc bảo mật

- Bảo vệ route bằng middleware phù hợp (`auth`, `guest`, `role`, `throttle`).
- Không trả thông tin nhạy cảm ra response hoặc log.
- Dữ liệu nhập từ người dùng luôn phải validate và sanitize theo ngữ cảnh.
- API endpoint nhạy cảm cần giới hạn tần suất khi cần thiết.

## 10. Quy tắc chất lượng code

- Mã phải rõ ràng, dễ đọc, dễ bảo trì.
- Tránh lặp logic, ưu tiên tái sử dụng service/helper có sẵn.
- Không sửa lan man ngoài phạm vi tác vụ.
- Giữ thay đổi nhỏ gọn, tập trung đúng mục tiêu.

## 11. Kiểm thử và kiểm tra trước merge

Tối thiểu phải thực hiện:

1. Chạy test liên quan đến phần đã sửa.
2. Kiểm tra không có lỗi cú pháp/runtime rõ ràng.
3. Đảm bảo không phá vỡ luồng Free/Pro, Auth, Checkout, Mock Exam.
4. Format code PHP bằng Pint khi có thay đổi file PHP.

## 12. Quy tắc tài liệu

- Khi thay đổi yêu cầu/chức năng chính, cập nhật tài liệu tương ứng trong `docs/`.
- README, rules, style-guidelines, URD phải nhất quán thuật ngữ.
- Không mô tả công nghệ hoặc luồng nghiệp vụ trái với code hiện tại.

## 13. Anti-pattern cần tránh

- Đưa business logic vào Blade.
- Bỏ qua validate vì cho rằng dữ liệu front-end đã đúng.
- Trộn component admin/client trong cùng ngữ cảnh UI.
- Sửa layout lệch template gốc không có lý do chức năng.
- Dùng dữ liệu giả hoặc workaround tạm thời để qua test/build.