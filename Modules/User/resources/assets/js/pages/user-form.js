/**
 * pages/user-form.js — Alpine controller cho User create/edit
 *
 * Component: createUserPage (đã có inline trong blade hiện tại)
 * → Đây là target migration: chuyển Alpine.data từ blade sang file này.
 *
 * TODO: migrate logic từ Modules/User/resources/views/create.blade.php
 *       vào đây khi refactor view.
 */

import { makeFormController } from '@shared/form-controller.js';
import { createTs, createTsRemote } from '@shared/tom-select-factory.js';

// Password generator (dùng chung create/edit)
function _genPassword() {
    const chars = {
        upper:   'ABCDEFGHJKMNPQRSTUVWXYZ',
        lower:   'abcdefghjkmnpqrstuvwxyz',
        digits:  '23456789',
        special: '!@#$%&*',
    };
    const all = Object.values(chars).join('');
    let pwd = Object.values(chars).map(s => s[Math.floor(Math.random() * s.length)]).join('');
    for (let i = 4; i < 14; i++) pwd += all[Math.floor(Math.random() * all.length)];
    return pwd.split('').sort(() => .5 - Math.random()).join('');
}

document.addEventListener('alpine:init', () => {

    // Hàm tạo component — nhận serverData từ Blade qua Js::from()
    Alpine.data('createUserPage', function () {
        // NOTE: serverData đã được khởi tạo bởi blade script inline.
        // Khi migrate hoàn toàn, truyền qua: x-data="createUserPage({{ Js::from($data) }})"
        return {
            ...makeFormController({}, {
                rules: {
                    name:  v => !String(v).trim()                                   ? 'Họ và tên là bắt buộc'
                              : String(v).trim().length < 2                         ? 'Tên phải có ít nhất 2 ký tự' : null,
                    email: v => !String(v).trim()                                   ? 'Email là bắt buộc'
                              : !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(v))   ? 'Địa chỉ email không hợp lệ' : null,
                    password: v => !v                                               ? 'Mật khẩu là bắt buộc'
                              : String(v).length < 8                               ? 'Phải có ít nhất 8 ký tự'
                              : !/[A-Z]/.test(v)                                   ? 'Cần ít nhất 1 chữ HOA'
                              : !/[a-z]/.test(v)                                   ? 'Cần ít nhất 1 chữ thường'
                              : !/[0-9]/.test(v)                                   ? 'Cần ít nhất 1 chữ số' : null,
                    organization_id: v => !v ? 'Vui lòng chọn tổ chức' : null,
                    system_role:     v => !v ? 'Vui lòng chọn vai trò' : null,
                },
                requiredFields: ['name', 'email', 'password', 'organization_id', 'system_role'],
            }),

            // State
            name:     '',
            email:    '',
            password: '',
            pwConfirm: '',
            showPw:   false,
            sendWelcomeEmail: false,
            avatarUrl: '',
            selectedOrg:       '',
            selectedRole:      '',
            selectedRoleLabel: '',

            // Password computed
            get pwChecks() {
                const p = this.password;
                return { length: p.length>=8, upper:/[A-Z]/.test(p),
                         lower:/[a-z]/.test(p), number:/[0-9]/.test(p), special:/[^A-Za-z0-9]/.test(p) };
            },
            get strength() {
                const c = this.pwChecks;
                const score = [c.length, c.upper&&c.lower, c.number, c.special, this.password.length>=12].filter(Boolean).length;
                return [
                    { label:'',           barCls:'progress-base-300', textCls:'text-base-content/30', pct:0 },
                    { label:'Yếu',        barCls:'progress-error',    textCls:'text-error',            pct:20 },
                    { label:'Trung bình', barCls:'progress-warning',  textCls:'text-warning',          pct:40 },
                    { label:'Tốt',        barCls:'progress-info',     textCls:'text-info',             pct:65 },
                    { label:'Mạnh',       barCls:'progress-success',  textCls:'text-success',          pct:85 },
                    { label:'Rất mạnh',   barCls:'progress-success',  textCls:'text-success',          pct:100 },
                ][Math.min(score, 5)];
            },

            generatePassword() {
                const pwd = _genPassword();
                this.password = this.pwConfirm = pwd;
                this.showPw = true;
                this._touched.password = true;
                ['password-input','password-confirm-input'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = pwd;
                });
            },

            updateAvatar() {
                this.avatarUrl = this.name.trim()
                    ? 'https://api.dicebear.com/9.x/initials/svg?seed=' + encodeURIComponent(this.name.trim())
                      + '&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700'
                    : '';
            },

            init() {
                // TomSelect được khởi tạo bởi _setup() — gọi sau DOMContentLoaded
                // vì Alpine init() có thể chạy trước DOM ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this._setup());
                } else {
                    this.$nextTick(() => this._setup());
                }
            },

            _setup() {
                // Inline implementation giữ nguyên để không break existing views
                // TODO: refactor dùng createTs() khi migrate view
            },
        };
    });

});
