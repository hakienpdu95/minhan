<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #eee; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">

    @if(!empty($extra['body']))
        {!! nl2br(e($extra['body'])) !!}
    @else
        <p>Xin chào,</p>
        <p>Bạn nhận được thông báo tự động này từ hệ thống workflow.</p>

        @if($subjectType && $subjectId)
        <p>Đối tượng: <strong>{{ $subjectType }} #{{ $subjectId }}</strong></p>
        @endif

        @if($actorEmail)
        <p>Người thực hiện: {{ $actorName ?? $actorEmail }}</p>
        @endif

        @if(!empty($extra))
        <table style="width:100%;border-collapse:collapse;margin-top:16px">
            @foreach($extra as $k => $v)
            @if(!is_array($v))
            <tr>
                <td style="padding:4px 8px;border:1px solid #eee;color:#666;font-size:13px">{{ $k }}</td>
                <td style="padding:4px 8px;border:1px solid #eee;font-size:13px">{{ $v }}</td>
            </tr>
            @endif
            @endforeach
        </table>
        @endif
    @endif

    <div class="footer">Email được gửi tự động — vui lòng không trả lời.</div>
</div>
</body>
</html>
