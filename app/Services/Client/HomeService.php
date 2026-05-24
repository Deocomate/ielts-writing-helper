<?php

namespace App\Services\Client;

use App\Helpers\FormatHelper;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class HomeService
{
    public function getHomePageData(): array
    {
        $featuredLessons = $this->getFeaturedLessonsFromDatabase();
        $activePlans = $this->getActivePlansFromDatabase();

        return [
            'seo' => [
                'title' => 'IELTS Type & Learn — Luyện Writing thông minh với AI',
                'description' => 'Nền tảng luyện IELTS Writing toàn diện: Chép chính tả, Phân tích bài mẫu phong cách Grammarly, và Thi thử chấm điểm bằng AI.',
            ],
            'demo_video_url' => app(SettingService::class)->getDemoVideoUrl(),
            'hero' => [
                'badge' => 'AI-Powered Learning',
                'title_lines' => ['Luyện IELTS Writing', 'thông minh hơn,', 'không phải chăm hơn.'],
                'description' => 'Chép chính tả bài mẫu Band 8.0+, phân tích sâu từng cấu trúc câu như Grammarly, và thi thử với AI chấm điểm theo 4 tiêu chí IELTS — tất cả trong một nền tảng.',
                'trust_signals' => [
                    'Miễn phí trọn đời',
                    'Không cần thẻ tín dụng',
                    '500+ bài mẫu',
                ],
            ],
            'social_proof' => $this->getSocialProof(),
            'problem_section' => [
                'title' => 'Tại sao luyện IELTS Writing vẫn khó?',
                'description' => 'Bạn đọc hàng chục bài mẫu nhưng vẫn không biết tại sao chúng đạt Band 8.0? Bạn luyện viết nhưng không ai chấm? Đó là vì phương pháp truyền thống thiếu đi sự phân tích tương tác và phản hồi tức thì.',
                'items' => [
                    [
                        'title' => 'Đọc mà không hiểu',
                        'description' => 'Đọc bài mẫu thụ động, không biết đâu là cấu trúc ăn điểm, đâu là từ vựng Band 8.0.',
                        'bg_class' => 'bg-red-50',
                    ],
                    [
                        'title' => 'Thiếu phản hồi',
                        'description' => 'Viết xong không ai chấm, không biết mình đang ở Band mấy hay mắc lỗi gì.',
                        'bg_class' => 'bg-yellow-50',
                    ],
                    [
                        'title' => 'Nhớ không nổi',
                        'description' => 'Đọc xong lại quên, không có phương pháp ghi nhớ chủ động từ vựng và cấu trúc.',
                        'bg_class' => 'bg-blue-50',
                    ],
                ],
            ],
            'features' => [
                [
                    'title' => 'Chép chính tả',
                    'badge' => 'Miễn phí',
                    'description' => 'Gõ lại bài mẫu Band 8.0+ từng ký tự với phản hồi đúng/sai tức thì để ghi nhớ sâu qua muscle memory.',
                    'highlights' => [
                        'Real-time typing feedback (< 10ms)',
                        'Popup nghĩa tiếng Việt cho từ khó',
                        'Thống kê WPM & Accuracy sau mỗi bài',
                    ],
                ],
                [
                    'title' => 'Đọc & Phân tích',
                    'badge' => 'Miễn phí',
                    'description' => 'Giao diện phong cách Grammarly: từ vựng hay, cấu trúc ăn điểm được gạch chân và giải thích trực quan.',
                    'highlights' => [
                        '4 màu semantic cho LR/GRA/CC/Lỗi',
                        'Tooltip giải thích mượt mà',
                        'Sidebar điểm thành phần TR/CC/LR/GRA',
                    ],
                ],
                [
                    'title' => 'Phòng thi & AI chấm',
                    'badge' => 'PRO',
                    'description' => 'Mô phỏng phòng thi thật với đồng hồ đếm ngược, word count real-time và AI report chi tiết theo 4 tiêu chí IELTS.',
                    'highlights' => [
                        'Đếm ngược chuẩn Task 1/Task 2',
                        'AI report: điểm chi tiết + gợi ý sửa lỗi',
                        'Phân tích lỗi trực quan sau nộp bài',
                    ],
                ],
            ],
            'how_it_works' => [
                [
                    'title' => 'Chọn bài mẫu',
                    'description' => 'Duyệt kho bài theo Task 1/2, dạng bài và band mục tiêu để học đúng trọng tâm.',
                ],
                [
                    'title' => 'Chọn chế độ học',
                    'description' => 'Chép chính tả, Đọc phân tích hoặc Thi thử theo đúng mục tiêu từng buổi học.',
                ],
                [
                    'title' => 'Nhận phản hồi',
                    'description' => 'Xem kết quả và gợi ý cải thiện ngay sau mỗi lần luyện tập.',
                ],
            ],
            'featured_lessons' => $featuredLessons->map(function (Lesson $lesson): array {
                return [
                    'title' => $lesson->title,
                    'task_type' => strtoupper(str_replace('_', ' ', $lesson->task_type)),
                    'question_type' => $lesson->question_type,
                    'band_score' => $lesson->band_score,
                    'tr_score' => $lesson->tr_score,
                    'cc_score' => $lesson->cc_score,
                    'lr_score' => $lesson->lr_score,
                    'gra_score' => $lesson->gra_score,
                    'is_premium' => (bool) $lesson->is_premium,
                ];
            })->values()->all(),
            'pricing' => [
                'free' => [
                    'name' => 'Free',
                    'price_label' => '0đ',
                    'duration_label' => '/mãi mãi',
                    'features' => [
                        ['label' => 'Truy cập bài mẫu cơ bản (Band 6.0–7.0)', 'included' => true],
                        ['label' => 'Chép chính tả (giới hạn lượt/ngày)', 'included' => true],
                        ['label' => 'Đọc & Phân tích (mức cơ bản)', 'included' => true],
                        ['label' => 'Phòng thi & AI chấm điểm', 'included' => false],
                    ],
                ],
                'pro' => $activePlans->map(function (Plan $plan): array {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price_label' => FormatHelper::money($plan->price),
                        'duration_label' => '/'.$plan->duration_days.' ngày',
                    ];
                })->values()->all(),
            ],
            'testimonials' => [
                [
                    'name' => 'Thu Hương',
                    'role' => 'Sinh viên, TP.HCM · Band 7.5',
                    'content' => 'Tính năng chép chính tả giúp mình nhớ từ vựng nhanh hơn gấp 3 lần so với đọc thụ động. Sau 2 tháng, Writing tăng từ 6.0 lên <strong>7.5</strong>!',
                    'initial' => 'TH',
                    'avatar_bg' => 'bg-brand-light',
                    'avatar_text' => 'text-brand',
                ],
                [
                    'name' => 'Minh Khôi',
                    'role' => 'Kỹ sư IT, Hà Nội · Band 8.0',
                    'content' => 'Chế độ phân tích bài mẫu kiểu Grammarly siêu hay! Mình hover vào từng cụm từ, hiểu ngay tại sao bài này đạt Band <strong>8.0</strong>. Đây là cách học mình tìm kiếm bấy lâu.',
                    'initial' => 'MK',
                    'avatar_bg' => 'bg-blue-50',
                    'avatar_text' => 'text-semantic-blue',
                ],
                [
                    'name' => 'Lan Anh',
                    'role' => 'Giáo viên, Đà Nẵng · Band 7.0',
                    'content' => 'AI chấm điểm chính xác đáng ngạc nhiên! Mình so sánh với điểm giáo viên feedback thì chênh lệch không quá 0.5. Tiết kiệm cả triệu tiền học thêm mỗi tháng.',
                    'initial' => 'LA',
                    'avatar_bg' => 'bg-purple-50',
                    'avatar_text' => 'text-semantic-purple',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Tôi có thể dùng miễn phí mãi mãi không?',
                    'answer' => 'Hoàn toàn được! Gói Free cung cấp truy cập bài mẫu cơ bản, chế độ chép chính tả (giới hạn lượt/ngày), và chế độ đọc phân tích ở mức cơ bản. Bạn có thể dùng mãi mãi mà không cần nhập thông tin thanh toán.',
                ],
                [
                    'question' => 'AI chấm điểm có chính xác không?',
                    'answer' => 'AI của chúng tôi được tinh chỉnh dựa trên bộ tiêu chí chính thức của IELTS (TR, CC, LR, GRA) và đã được đối chiếu với hàng ngàn bài chấm thật. Sai số trung bình khoảng 0.5 band — tương đương với sai số giữa hai giám khảo thật.',
                ],
                [
                    'question' => 'Tôi có thể dùng trên điện thoại không?',
                    'answer' => 'Chế độ Đọc & Phân tích và trang cá nhân hoạt động tốt trên mobile. Tuy nhiên, chế độ Chép chính tả và Phòng thi yêu cầu sử dụng trên máy tính để đảm bảo trải nghiệm gõ phím tốt nhất.',
                ],
                [
                    'question' => 'Thanh toán bằng phương thức nào?',
                    'answer' => 'Chúng tôi hỗ trợ nhiều phương thức: chuyển khoản ngân hàng, MoMo, ZaloPay, VNPay, và thẻ quốc tế (Visa/Mastercard). Thanh toán được xử lý qua cổng bảo mật, không lưu trữ thông tin thẻ trên server.',
                ],
                [
                    'question' => 'Tôi có thể hủy gói Pro bất cứ lúc nào không?',
                    'answer' => 'Có, bạn có thể hủy bất cứ lúc nào từ trang Quản lý gói cước trong mục Tài khoản. Sau khi hủy, bạn vẫn sử dụng được tính năng Pro cho đến hết chu kỳ thanh toán hiện tại.',
                ],
            ],
            'final_cta' => [
                'title_line1' => 'Sẵn sàng chinh phục',
                'title_line2' => 'IELTS Writing Band 8.0+?',
                'description' => 'Đăng ký miễn phí ngay hôm nay. Không cần thẻ tín dụng. Bắt đầu chép chính tả bài mẫu đầu tiên trong vòng 30 giây.',
            ],
            'about_page' => [
                'application_intro' => [
                    'title' => 'Nhóm 18 - Bất Cần Đời thực hiện sản phẩm cuối môn cho học phần Thiết kế và PTHTTM MOOCS.',
                    'description' => 'Đây là sản phẩm cuối môn được xây dựng bởi Founder Nguyễn Lê Khánh Linh và Co-Founder Đỗ Ngọc Hà, với mục tiêu tạo ra một nền tảng luyện IELTS Writing trực quan, dễ theo dõi tiến độ và dễ cải thiện theo dữ liệu.',
                    'highlights' => [
                        'Founder: Nguyễn Lê Khánh Linh',
                        'Co-Founder: Đỗ Ngọc Hà',
                        'Môn: Thiết kế và PTHTTM MOOCS',
                    ],
                ],
                'why_choose' => [
                    [
                        'title' => 'Tập trung vào trải nghiệm học thật',
                        'description' => 'Không phải kho bài đọc tĩnh. Mỗi chế độ học được thiết kế để người dùng tương tác, sai, sửa và tiến bộ ngay trên màn hình.',
                    ],
                    [
                        'title' => 'AI chấm điểm theo tiêu chí IELTS',
                        'description' => 'Report trả về bám sát 4 tiêu chí TR/CC/LR/GRA, giúp bạn hiểu rõ vì sao điểm tăng hoặc giảm sau mỗi lần nộp bài.',
                    ],
                    [
                        'title' => 'Lộ trình liên thông từ đầu vào đến đầu ra',
                        'description' => 'Từ chép chính tả để nạp ngôn ngữ, sang phân tích để hiểu logic, rồi thi thử để kiểm chứng năng lực thực tế.',
                    ],
                ],
                'founder' => [
                    'team_name' => 'Nhóm 18 - Bất Cần Đời',
                    'name' => 'Nguyễn Lê Khánh Linh',
                    'role' => 'Founder',
                    'co_founder' => 'Đỗ Ngọc Hà',
                    'course' => 'Thiết kế và PTHTTM MOOCS',
                    'project_type' => 'Sản phẩm cuối môn',
                    'quote' => 'Nhóm 18 - Bất Cần Đời xây dựng sản phẩm này như một dự án cuối môn, tập trung vào trải nghiệm học IELTS Writing có cấu trúc, có dữ liệu và có phản hồi rõ ràng.',
                    'story' => [
                        'Nhóm thực hiện: Nhóm 18 - Bất Cần Đời.',
                        'Founder: Nguyễn Lê Khánh Linh.',
                        'Co-Founder: Đỗ Ngọc Hà.',
                        'Môn học: Thiết kế và PTHTTM MOOCS.',
                    ],
                ],
            ],
            'feature_experience' => [
                [
                    'title' => 'Gõ chép chính tả',
                    'description' => 'Rèn phản xạ câu chuẩn Band cao bằng cách gõ lại từng ký tự, với phản hồi đúng/sai tức thì và thống kê tốc độ gõ.',
                    'experience' => [
                        'Highlight lỗi ngay khi gõ sai để sửa tại chỗ.',
                        'Theo dõi WPM, Accuracy theo từng bài.',
                    ],
                ],
                [
                    'title' => 'Tham khảo bài mẫu band cao',
                    'description' => 'Kho bài mẫu có phân loại Task, dạng đề và mức band để bạn học đúng mục tiêu và đúng ngữ cảnh.',
                    'experience' => [
                        'Xem điểm thành phần TR/CC/LR/GRA của từng bài.',
                        'Đọc giải thích trực quan cho cụm từ và cấu trúc ăn điểm.',
                    ],
                ],
                [
                    'title' => 'Sổ từ vựng',
                    'description' => 'Lưu lại từ/cụm từ quan trọng ngay trong lúc học để tạo bộ từ cá nhân theo chủ đề và theo lỗi thường gặp.',
                    'experience' => [
                        'Quản lý từ vựng trong dashboard cá nhân.',
                        'Ôn tập lại theo ngữ cảnh đã gặp trong bài mẫu.',
                    ],
                ],
                [
                    'title' => 'Chấm điểm AI',
                    'description' => 'Mô phỏng phòng thi và nhận báo cáo chấm điểm chi tiết để biết bài viết đang ở band nào và cần cải thiện gì trước tiên.',
                    'experience' => [
                        'Đánh giá chi tiết theo 4 tiêu chí IELTS.',
                        'Gợi ý sửa lỗi theo mức độ ưu tiên để tăng band nhanh hơn.',
                    ],
                ],
            ],
            'plan_fit' => [
                [
                    'plan' => 'Free',
                    'fit_for' => 'Người mới bắt đầu hoặc muốn test phương pháp học trước khi nâng cấp.',
                    'best_when' => 'Bạn cần làm quen hệ thống, luyện đều mỗi ngày với khối lượng vừa phải.',
                ],
                [
                    'plan' => 'Pro',
                    'fit_for' => 'Người đặt mục tiêu tăng band rõ ràng trong thời gian ngắn.',
                    'best_when' => 'Bạn cần bài mẫu nâng cao, thi thử không giới hạn và AI report chuyên sâu.',
                ],
            ],
            'contact' => [
                'hotline' => '0868061598',
                'email' => 'thoer197765@gmail.com',
                'address' => 'Nhóm 18 - Bất Cần Đời',
                'map_embed_url' => '',
                'facebook_url' => 'https://www.facebook.com/share/14d8fGTUimW/?mibextid=wwXIfr',
                'zalo_url' => '',
                'support_hours' => 'Sản phẩm cuối môn - Thiết kế và PTHTTM MOOCS',
                'team_name' => 'Nhóm 18 - Bất Cần Đời',
                'founder' => 'Nguyễn Lê Khánh Linh',
                'co_founder' => 'Đỗ Ngọc Hà',
                'course' => 'Thiết kế và PTHTTM MOOCS',
                'project_type' => 'Sản phẩm cuối môn',
            ],
        ];
    }

    private function getSocialProof(): array
    {
        $userCount = 0;
        $publishedLessons = 0;

        try {
            $userCount = User::query()->where('role', 'user')->count();
            $publishedLessons = Lesson::query()->where('status', 'published')->count();
        } catch (QueryException) {
        }

        return [
            [
                'value' => number_format($userCount).'+',
                'label' => 'Học viên đang luyện tập',
            ],
            [
                'value' => number_format($publishedLessons).'+',
                'label' => 'Bài mẫu đã xuất bản',
            ],
            [
                'value' => '95%',
                'label' => 'Người dùng tăng điểm sau 30 ngày',
            ],
            [
                'value' => '4.8/5',
                'label' => 'Đánh giá trung bình',
            ],
        ];
    }

    private function getFeaturedLessonsFromDatabase(): Collection
    {
        try {
            return Lesson::query()
                ->where('status', 'published')
                ->latest()
                ->take(3)
                ->get([
                    'id',
                    'title',
                    'task_type',
                    'question_type',
                    'band_score',
                    'tr_score',
                    'cc_score',
                    'lr_score',
                    'gra_score',
                    'is_premium',
                ]);
        } catch (QueryException) {
            return collect();
        }
    }

    private function getActivePlansFromDatabase(): Collection
    {
        try {
            return Plan::query()
                ->where('is_active', true)
                ->orderBy('price')
                ->get(['id', 'name', 'duration_days', 'price']);
        } catch (QueryException) {
            return collect();
        }
    }
}
