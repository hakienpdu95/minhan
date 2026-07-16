<?php

namespace Modules\BusinessProject\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rule R1 — "1 Business Project chỉ có 1 Business Context". Ném ở tầng Action
 * TRƯỚC khi chạm DB, độc lập với unique index (2 lưới an toàn, không thay nhau).
 */
class DuplicateContextException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            statusCode: 422,
            message: 'Business Project này đã có Business Context. Vui lòng cập nhật bản ghi hiện có thay vì tạo mới.',
        );
    }
}
