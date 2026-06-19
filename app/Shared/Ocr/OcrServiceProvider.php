<?php

namespace App\Shared\Ocr;

use App\Shared\Ocr\Contracts\OcrDriverContract;
use App\Shared\Ocr\Drivers\TesseractDriver;
use App\Shared\Ocr\Parsers\CccdParser;
use App\Shared\Ocr\Preprocessors\ImagePreprocessor;
use Illuminate\Support\ServiceProvider;

class OcrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../config/ocr.php', 'ocr');

        $this->app->singleton(OcrDriverContract::class, function () {
            $driver = config('ocr.driver', 'tesseract');
            $config = config("ocr.drivers.{$driver}", []);

            return match ($driver) {
                'tesseract' => new TesseractDriver($config),
                default     => throw new \InvalidArgumentException("OCR driver [{$driver}] not supported."),
            };
        });

        $this->app->singleton(OcrManager::class, function ($app) {
            return new OcrManager(
                driver:       $app->make(OcrDriverContract::class),
                preprocessor: new ImagePreprocessor(config('ocr.preprocessing', [])),
                cccdParser:   new CccdParser(),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../../config/ocr.php' => config_path('ocr.php'),
            ], 'ocr-config');
        }
    }
}
