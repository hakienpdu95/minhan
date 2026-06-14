@component('mail::message')

# Xác minh địa chỉ email của bạn

Xin chào **{{ $userName }}**,

Bạn vừa yêu cầu xác minh địa chỉ email cho tài khoản **Competency Passport**. Nhấp vào nút bên dưới để hoàn tất — link có hiệu lực đến **{{ $expiresAt }}**.

@component('mail::button', ['url' => $verifyUrl, 'color' => 'primary'])
Xác minh email ngay
@endcomponent

Sau khi xác minh, tài khoản của bạn đạt **Trust Level 1**, mở khóa:
- Xem Career Journal & Competency Passport
- Làm khảo sát TDWCF và AI Sandbox
- Nhận chứng nhận nội bộ

---

Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này — tài khoản của bạn không bị ảnh hưởng.

Link đầy đủ nếu nút không hoạt động:
{{ $verifyUrl }}

Trân trọng,
**{{ config('app.name') }}**

@endcomponent
