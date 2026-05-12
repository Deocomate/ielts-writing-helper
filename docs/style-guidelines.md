# Style Guidelines

Tài liệu này định nghĩa chuẩn giao diện và tương tác cho toàn bộ sản phẩm Lách IELTS.

## 1. Mục tiêu thiết kế

- Content-first: bài viết và phản hồi học thuật là trung tâm.
- Tối giản có chiều sâu: ít nhiễu thị giác nhưng vẫn có phân lớp rõ ràng.
- Nhất quán giữa client và admin: khác ngữ cảnh nhưng cùng hệ quy chiếu chất lượng.
- Dễ mở rộng: ưu tiên component hóa theo module và giữ parity với template gốc.

## 2. Nguyên tắc cốt lõi

1. Giữ cấu trúc template chuẩn, chỉ bind dữ liệu động bằng Blade.
2. Không dùng màu trang trí tùy hứng; màu phải có ý nghĩa ngữ nghĩa.
3. Trạng thái tương tác luôn rõ: default, hover, focus, disabled, loading, error, success.
4. Ưu tiên khả năng đọc dài: line-height thoáng, chiều rộng dòng hợp lý.
5. Chế độ Dictation và Mock Exam là desktop-first, không tối ưu cho thao tác mobile sâu.

## 3. Design tokens

### 3.1 Color tokens

#### Nền và phân lớp

- `--bg-app`: `#F9F9FA`
- `--bg-surface`: `#FFFFFF`
- `--border-subtle`: `#E1E4E8`

#### Typography

- `--text-primary`: `#0E101A`
- `--text-secondary`: `#6D758D`
- `--text-disabled`: `#B9BDC5`

#### Semantic trong học tập

- `--semantic-error`: `#FF5E5E` (lỗi ngữ pháp/chính tả)
- `--semantic-coherence`: `#007AFF` (liên kết, mạch lạc)
- `--semantic-lexical`: `#11A683` (từ vựng tốt)
- `--semantic-grammar-range`: `#8F00FF` (cấu trúc ngữ pháp nâng cao)
- `--semantic-pro`: `#FFD500` (Pro/premium/cảnh báo đặc thù)

### 3.2 Typography tokens

- Font chính: `Inter`, `Roboto`, `Segoe UI`, `system-ui`, `sans-serif`
- Font mono cho vùng gõ dictation (tùy ngữ cảnh): `JetBrains Mono`, `Fira Code`, `monospace`

Scale đề xuất:

- `h1`: 24px, weight 600, line-height 1.35
- `h2`: 18px, weight 600, line-height 1.4
- `body`: 16px, weight 400, line-height 1.65
- `caption`: 14px, weight 400, line-height 1.5

### 3.3 Spacing tokens

- Base spacing: 4px
- Các nấc dùng thường xuyên: 8, 12, 16, 20, 24, 32, 40, 48
- Khoảng cách giữa section lớn: tối thiểu 32px

### 3.4 Radius và shadow

- Radius control: 6px đến 10px
- Shadow card:
	- `0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03)`
- Shadow tooltip/modal:
	- `0 4px 6px rgba(0,0,0,0.05), 0 10px 15px rgba(0,0,0,0.1)`

## 4. Component guidelines

### 4.1 Button

- Primary: nền xanh lexical, chữ trắng, radius 8px.
- Secondary: nền xám nhạt hoặc trong suốt, chữ primary.
- Danger: nền semantic-error, chỉ dùng cho tác vụ phá hủy hoặc cảnh báo mạnh.
- Disabled: giảm tương phản, không có hover state giả.

### 4.2 Input/Form

- Label luôn hiển thị rõ ràng, không chỉ dựa vào placeholder.
- Hiển thị lỗi validation sát field, text ngắn và cụ thể.
- Focus ring nhất quán trên toàn hệ thống.

### 4.3 Card/Panel

- Dùng để nhóm thông tin cùng ngữ cảnh.
- Tránh nhồi quá nhiều thông tin trong 1 card.
- Ưu tiên chia block theo hành động của người dùng.

### 4.4 Tooltip/Annotation popup

- Chỉ hiển thị khi hover/focus vào text đã mapping.
- Nội dung ngắn, có cấu trúc: loại nhận xét -> giải thích -> ví dụ.
- Không che kín nội dung đang đọc.

## 5. Quy chuẩn màn hình theo module

### 5.1 Dictation

- Tập trung vào vùng nhập liệu trung tâm.
- Trạng thái đúng/sai phản hồi tức thời.
- Kết quả cuối phiên luôn có WPM, Accuracy và CTA tiếp theo.

### 5.2 Analyze

- Sample essay là vùng ưu tiên cao nhất.
- Màu gạch chân phải đúng semantic tag.
- Sidebar band explanation không lấn át nội dung essay.

### 5.3 Mock Exam

- Layout 2 cột: đề bài và editor.
- Đồng hồ + word count phải luôn dễ quan sát.
- Trạng thái chờ AI cần có loading dễ hiểu, không gây hoang mang.

### 5.4 Dashboard

- Ưu tiên thứ tự: snapshot chỉ số -> analytics -> hoạt động gần đây.
- Các thẻ số liệu phải đọc nhanh, không cần cuộn nhiều để thấy KPI chính.

### 5.5 Checkout

- Tập trung vào plan, giá, trạng thái thanh toán, hướng dẫn hành động tiếp theo.
- Màn pending phải hiển thị trạng thái rõ và tránh dead-end UX.

## 6. Motion và phản hồi tương tác

- Transition chuẩn: 150ms đến 250ms, easing nhẹ.
- Tooltip fade-in có delay ngắn để giảm flicker.
- Tránh animation nặng gây lag trong màn hình có nhiều text.
- Không lạm dụng hiệu ứng trang trí trong ngữ cảnh học tập.

## 7. Responsive và nền tảng

- Desktop-first cho Dictation và Mock Exam.
- Mobile hỗ trợ tốt cho Home, Analyze, Dashboard, Billing.
- Với tính năng không hỗ trợ mobile sâu, hiển thị cảnh báo rõ ràng thay vì cho thao tác lỗi.

Breakpoint tham chiếu:

- `sm`: >= 640px
- `md`: >= 768px
- `lg`: >= 1024px
- `xl`: >= 1280px

## 8. Accessibility baseline

- Độ tương phản text và nền đạt mức dễ đọc.
- Có focus style cho phần tử tương tác bằng bàn phím.
- Icon đơn lẻ cần có label hoặc aria text khi cần.
- Không truyền tải thông tin chỉ bằng màu sắc.

## 9. Template parity policy

- Client views map từ `templates/`, `templates/dashboard/`, `templates/learning/`, `templates/checkout/`.
- Admin views map từ `templates/admin/`.
- Khi refactor UI, giữ parity về cấu trúc và style với template gốc.

## 10. Checklist nghiệm thu UI

1. Màn hình có đúng template nguồn và không phá layout.
2. Trạng thái loading/error/empty/success đã đầy đủ.
3. Màu semantic dùng đúng ngữ cảnh học thuật.
4. Responsive đạt cho các viewport mục tiêu.
5. Điều hướng và CTA chính dễ nhận diện.
6. Không có thành phần khó đọc hoặc nhiễu thị giác.