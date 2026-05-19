<?php

namespace App\Console\Commands;

use App\Models\Province;
use App\Models\Region;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportProvincesAndWards extends Command
{
    protected $signature = 'import:provinces-wards {--chunk=1000 : Number of records per chunk} {--force : Force truncate and re-import data}';
    protected $description = 'Import or update regions, provinces, and wards from JSON file into the database';

    public function handle()
    {
        $this->info('Starting import of regions, provinces, and wards...');

        // Đường dẫn tới file JSON
        $jsonFile = base_path('datafiles/provinces.json');

        // Kiểm tra sự tồn tại của file
        if (!File::exists($jsonFile)) {
            $this->error('JSON file not found.');
            return 1;
        }

        // Đọc và parse dữ liệu từ file JSON
        $jsonData = json_decode(File::get($jsonFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON format in provinces.json.');
            return 1;
        }

        // Lấy dữ liệu regions, provinces và wards từ JSON
        $regions = collect($jsonData)->where('type', 'table')->where('name', 'regions')->first()['data'] ?? [];
        $provinces = collect($jsonData)->where('type', 'table')->where('name', 'provinces')->first()['data'] ?? [];
        $wards = collect($jsonData)->where('type', 'table')->where('name', 'wards')->first()['data'] ?? [];

        // Số lượng bản ghi mỗi lần chèn
        $chunkSize = $this->option('chunk');

        // Xóa dữ liệu cũ nếu có tùy chọn --force
        if ($this->option('force')) {
            if ($this->confirm('This will truncate regions, provinces, and wards tables. Are you sure?')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Ward::truncate();
                Province::truncate();
                Region::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->info('Tables truncated successfully.');
            } else {
                $this->info('Import cancelled.');
                return 1;
            }
        }

        // Chèn dữ liệu vùng
        $this->info('Importing regions...');
        $this->importRegions($regions, $chunkSize);

        // Chèn dữ liệu tỉnh/thành phố
        $this->info('Importing provinces...');
        $this->importProvinces($provinces, $chunkSize);

        // Chèn dữ liệu phường/xã
        $this->info('Importing wards...');
        $this->importWards($wards, $chunkSize);

        $this->info('Import completed successfully!');
        return 0;
    }

    protected function importRegions($regions, $chunkSize)
    {
        // Chia nhỏ dữ liệu để chèn theo chunk
        $regionData = collect($regions)->map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'created_at' => $item['created_at'] ?? now(),
                'updated_at' => $item['updated_at'] ?? now(),
            ];
        })->chunk($chunkSize);

        // Chèn dữ liệu theo chunk
        DB::transaction(function () use ($regionData) {
            foreach ($regionData as $chunk) {
                Region::upsert(
                    $chunk->toArray(),
                    ['id'], // Khóa duy nhất để kiểm tra
                    ['name', 'created_at', 'updated_at']
                );
                $this->info('Inserted/Updated ' . $chunk->count() . ' regions.');
            }
        });
    }

    protected function importProvinces($provinces, $chunkSize)
    {
        // Lấy ánh xạ region_id -> id
        $regionMap = Region::pluck('id', 'id')->toArray();

        // Chia nhỏ dữ liệu để chèn theo chunk
        $provinceData = collect($provinces)->map(function ($item) use ($regionMap) {
            $regionId = $item['region_id'] ?? null;
            if (!isset($regionMap[$regionId])) {
                $this->warn("Region ID {$regionId} not found for province {$item['name']}. Skipping.");
                return null;
            }

            // Chuyển đổi place_type
            $placeType = $item['place_type'] === 'Tỉnh' ? 'tinh' : 'thanh-pho';

            return [
                'id' => $item['id'],
                'province_code' => $item['province_code'],
                'name' => $item['name'],
                'short_name' => $item['short_name'],
                'place_type' => $placeType,
                'region_id' => $regionId,
                'country' => $item['country'],
                'is_active' => true,
                'created_at' => $item['created_at'] ?? now(),
                'updated_at' => $item['updated_at'] ?? now(),
            ];
        })->filter()->chunk($chunkSize);

        // Chèn dữ liệu theo chunk
        DB::transaction(function () use ($provinceData) {
            foreach ($provinceData as $chunk) {
                Province::upsert(
                    $chunk->toArray(),
                    ['province_code'], // Khóa duy nhất để kiểm tra
                    ['name', 'short_name', 'place_type', 'region_id', 'country', 'is_active', 'created_at', 'updated_at']
                );
                $this->info('Inserted/Updated ' . $chunk->count() . ' provinces.');
            }
        });
    }

    protected function importWards($wards, $chunkSize)
    {
        // Lấy ánh xạ province_code -> province_code
        $provinceMap = Province::pluck('province_code', 'province_code')->toArray();

        // Chia nhỏ dữ liệu để chèn theo chunk
        $wardData = collect($wards)->map(function ($item) use ($provinceMap) {
            $provinceCode = $item['province_code'];
            if (!isset($provinceMap[$provinceCode])) {
                $this->warn("Province code {$provinceCode} not found for ward {$item['name']}. Skipping.");
                return null;
            }

            // Chuyển đổi place_type dựa trên name
            $placeType = 'dac-khu';
            if (strpos($item['name'], 'Phường') !== false) {
                $placeType = 'phuong';
            } elseif (strpos($item['name'], 'Xã') !== false) {
                $placeType = 'xa';
            }

            return [
                'id' => $item['id'],
                'ward_code' => $item['ward_code'],
                'name' => $item['name'],
                'place_type' => $placeType,
                'province_code' => $provinceCode,
                'is_active' => true,
                'created_at' => $item['created_at'] ?? now(),
                'updated_at' => $item['updated_at'] ?? now(),
            ];
        })->filter()->chunk($chunkSize);

        // Chèn dữ liệu theo chunk
        DB::transaction(function () use ($wardData) {
            foreach ($wardData as $chunk) {
                Ward::upsert(
                    $chunk->toArray(),
                    ['ward_code'], // Khóa duy nhất để kiểm tra
                    ['name', 'place_type', 'province_code', 'is_active', 'created_at', 'updated_at']
                );
                $this->info('Inserted/Updated ' . $chunk->count() . ' wards.');
            }
        });
    }
}