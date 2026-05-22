# Bug Report & Permanent Fix Proposal  
**Module**: `Modules/Survey`

## 1. Tóm tắt vấn đề (Summary)

Khi test API submit phản hồi từ form bên ngoài (devminhan) → CRM backend (minhan) bị lỗi cache Redis.

**Hai bug chính đang tồn tại:**

1. **Cache Serialization Bug** (Root cause của “Không tải được form”)
2. **Unexpected Logout Bug** khi user submit/cập nhật phản hồi form

**Yêu cầu fix**:  
- Fix triệt để, không để tái phát.  
- Hạn chế tối đa việc sử dụng cache trong module Survey (đặc biệt là cache object PHP).  
- Đảm bảo session CRM không bị ảnh hưởng khi form bên ngoài submit.

---

## 2. Chi tiết Bug 1 – Cache Serialization (__PHP_Incomplete_Class)

### Mô tả lỗi
- `Cache::store('redis')->remember()` đang cache **object PHP** (`SurveySchemaData` – Spatie Data object).
- Redis serialize object bằng `serialize()` → lưu class name.
- Khi `remember()` hit cache → Redis unserialize → class `SurveySchemaData` chưa được autoload kịp → biến thành `__PHP_Incomplete_Class`.
- Hậu quả: Lần đầu (cache miss) thì thành công, từ lần thứ 2 trở đi cache bị corrupt → 500 error.

### File liên quan
- `Modules/Survey/app/Data/SurveySchemaData.php`
- Action đang cache: `BuildSurveySchemaAction` (hoặc bất kỳ nơi nào dùng `Cache::remember` với `SurveySchemaData::fromModel()`)

### Code hiện tại (buggy)
```php
// Trước (đang gây lỗi)
Cache::store('redis')->remember($key, $ttl, function () use ($survey) {
    return SurveySchemaData::fromModel($survey);   // ← cache PHP object
});

```

Fix đề xuất (triệt để & an toàn)
Không cache object PHP nữa. Chỉ cache plain PHP array.

// Sau khi fix
Cache::store('redis')->remember($key, $ttl, function () use ($survey) {
    return SurveySchemaData::fromModel($survey)->toArray();   // ← cache array
});

// Khi sử dụng:
$cached = Cache::store('redis')->get($key);
$schema = SurveySchemaData::from($cached);   // reconstruct object

Lợi ích:

Array luôn serialize/deserialize an toàn trên Redis.
Không còn phụ thuộc autoload class khi unserialize.
Vẫn giữ được performance của cache.

3. Chi tiết Bug 2 – Logout tự động khi submit form
Mô tả
Khi người dùng phía form (devminhan) cập nhật / submit phản hồi, phía CRM (minhan) đang login bị logout đột ngột (phải login lại).
Nguyên nhân nghi ngờ:

Có thể đang share Redis session / cache tag / key giữa hai project.
Hoặc action submit form đang clear/flush cache key mà vô tình ảnh hưởng đến session key của CRM.
Hoặc có middleware / event listener đang invalidate session khi cache miss/corrupt.

Yêu cầu:
Phải đảm bảo submit form từ bên ngoài không được chạm vào session của CRM.

4. Khuyến nghị chung (Rất quan trọng)

Hạn chế cache trong module Survey
Chỉ cache những thứ thực sự cần (ví dụ: schema ít thay đổi).
Ưu tiên cache array thay vì object.
Tránh cache global key, nên dùng cache tag hoặc prefix module-specific (survey:).

Tách biệt cache/session giữa các project
CRM và devminhan nên có Redis connection riêng (hoặc database riêng) nếu có thể.
Hoặc ít nhất dùng prefix khác nhau: crm: và survey:.

Clear cache đúng cách
Không dùng Cache::flush() hoặc Cache::clear() global khi chỉ cần clear key của Survey.
Sử dụng Cache::tags(['survey'])->flush() hoặc xóa key cụ thể.

5. Các bước cần thực hiện để fix triệt để

Tìm tất cả chỗ dùng Cache::remember / Cache::get liên quan đến SurveySchemaData.
Thay toàn bộ bằng pattern cache array + reconstruct.
Kiểm tra BuildSurveySchemaAction, SurveyController, SurveyResponseController, và các event listener liên quan đến submit response.
Thêm prefix cache module-specific: survey:schema:{survey_id}.
Kiểm tra session driver và Redis connection config của hai project (CRM vs devminhan).
Test lại toàn bộ flow:
Load form nhiều lần (cache hit).
Submit response từ form bên ngoài.
Kiểm tra CRM vẫn đang login bình thường.

6. Files cần kiểm tra / sửa (danh sách)

Modules/Survey/app/Data/SurveySchemaData.php
Modules/Survey/app/Actions/BuildSurveySchemaAction.php
Tất cả file trong Modules/Survey/app/Http/Controllers/
Modules/Survey/app/Events/ (nếu có event khi submit response)
Config cache/session của project CRM (config/cache.php, config/session.php)


7. Expected Behavior sau khi fix

Cache schema ổn định, không còn __PHP_Incomplete_Class.
Submit form từ devminhan không làm logout CRM.
Performance cache vẫn tốt.
Code dễ maintain, không còn phụ thuộc serialize object PHP.