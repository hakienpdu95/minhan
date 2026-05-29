Bạn hãy tham khảo  module Organization và làm theo kiến trúc **Architecture**: Advanced Vertical Slice (AVSA) + CQRS-lite + Laravel Modules + Laravel Actions này nhé. Áp dụng tối ưu, dễ scale. Và hãy tuân thủ nghiêm ngặt kiến trúc trên

refactor toàn bộ Lead module theo đúng kiến trúc AVSA

Kết quả refactor Lead module → AVSA + CQRS-lite

Kiến trúc boundary

  Survey ──submit──► RunAssessmentAction ──► ScoringEngineService
                                                      │
                                            AssessmentCompleted event
                                             ┌────────┴──────────┐
                                 Survey (webhook)         WorkflowAutomation (triggers)


Mọi view hiện tại dùng inline classic <script> → alpine:init listener đăng ký lúc HTML parse → vẫn
  hoạt động đúng sau fix. Chỉ module JS mới được hưởng lợi từ việc Alpine.start() được delay đến
  DOMContentLoaded.

  Giải thích rõ về root cause và tại sao fix hoạt động:

  TRƯỚC FIX — thứ tự thực thi:
  1. HTML parse: inline <script> classic chạy → đăng ký alpine:init listeners ✓
  2. app.js (module, deferred): Alpine.start() → alpine:init fires → xử lý DOM
  3. organization.js (module, deferred): đăng ký alpine:init listener ← QUÁ MUỘN, DOM đã xong
  → Error: orgListPage is not defined

  SAU FIX — thứ tự thực thi:
  1. HTML parse: inline <script> classic chạy → đăng ký alpine:init listeners ✓
  2. app.js (module): KHÔNG start Alpine, chỉ đăng ký DOMContentLoaded
  3. organization.js (module): đăng ký alpine:init listener ✓
  4. DOMContentLoaded: Alpine.start() → alpine:init fires → CẢ HAI đã đăng ký → xử lý DOM ✓

  Tóm tắt toàn bộ implementation module Organization:

  7 file tạo mới:
  - assets/sass/organization.scss — entry SCSS: @use 'form-patterns' + @use 'tom-select'
  - assets/js/organization.js — entry JS gọi 2 page controllers
  - assets/js/pages/organization-form.js — khởi tạo Jodit + initFormValidation, có guard
  [data-org-form]
  - assets/js/pages/organization-index.js — toàn bộ Alpine orgListPage, Tabulator columns, delete
  AJAX — 200 dòng trước đây inline trong blade, nay là module JS clean
  
  4 file cập nhật:
  - create.blade.php / edit.blade.php — xóa inline <script>, thêm @push('styles') module SCSS +
  @push('scripts') module JS
  - index.blade.php — xóa 300+ dòng <script> inline, thay bằng Js::from([...]) truyền server data
  vào x-data, @vite module assets
  - app.js — Alpine.start() chuyển vào DOMContentLoaded để fix timing với ES module scripts