<?php

namespace App\Shared\Ocr\Facades;

use App\Shared\Ocr\Data\CccdData;
use App\Shared\Ocr\Data\OcrResult;
use App\Shared\Ocr\Enums\DocumentType;
use App\Shared\Ocr\OcrManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static OcrResult extract(string|\Illuminate\Http\UploadedFile $image, bool $preprocess = true)
 * @method static CccdData  readCccd(string|\Illuminate\Http\UploadedFile $image, DocumentType $side = DocumentType::CCCD_FRONT, bool $preprocess = true)
 *
 * @see OcrManager
 */
class Ocr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OcrManager::class;
    }
}
