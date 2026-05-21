{{--
    Survey Builder — Alpine.js component
    Nhận: $survey, $sectionsData (array), $fieldTypes (array), $isLocked (bool)
    Tất cả ghi/sửa dùng fetch() → JSON endpoints. Không reload trang.
--}}

<div
    x-data="surveyBuilder(@js($sectionsData), @js($fieldTypes), @js($survey->id), @js($isLocked), @js(csrf_token()))"
    class="space-y-4"
>
    {{-- Flash message --}}
    <div x-show="flash.text" x-cloak x-transition
         :class="flash.type === 'error' ? 'alert-error' : 'alert-success'"
         class="alert text-sm py-2 px-4">
        <span x-text="flash.text"></span>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <h2 class="font-bold text-base">Cấu trúc khảo sát</h2>
        <div class="flex gap-2 items-center">
            <span x-show="isLocked" class="badge badge-warning badge-sm gap-1">
                🔒 Đã có responses — chỉ deactivate
            </span>
            <button @click="openSectionModal()"
                    class="btn btn-primary btn-sm gap-1"
                    :disabled="saving">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm section
            </button>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="sections.length === 0" class="text-center py-14 text-base-content/40 border-2 border-dashed border-base-300 rounded-xl">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <p class="font-medium">Chưa có section nào</p>
        <p class="text-xs mt-1">Thêm section để bắt đầu xây dựng khảo sát</p>
    </div>

    {{-- Section list --}}
    <template x-for="(section, sIdx) in sections" :key="section.id">
        <div class="border border-base-200 rounded-xl bg-base-100 shadow-sm overflow-hidden">

            {{-- Section header --}}
            <div class="flex items-center gap-2 px-4 py-3 bg-base-50 border-b border-base-200">
                <button @click="section._open = !section._open"
                        class="flex-1 flex items-center gap-2 text-left min-w-0">
                    <svg :class="section._open ? 'rotate-90' : ''" class="w-4 h-4 text-base-content/40 shrink-0 transition-transform"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/>
                    </svg>
                    <span class="font-semibold text-sm truncate" x-text="section.title || 'Section ' + (sIdx+1)"></span>
                    <span class="badge badge-ghost badge-xs shrink-0"
                          x-text="section.fields.length + ' fields'"></span>
                </button>

                <div class="flex items-center gap-1 shrink-0">
                    <button @click="moveSectionUp(sIdx)" :disabled="sIdx===0 || saving"
                            class="btn btn-ghost btn-xs btn-circle" title="Lên">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button @click="moveSectionDown(sIdx)" :disabled="sIdx===sections.length-1 || saving"
                            class="btn btn-ghost btn-xs btn-circle" title="Xuống">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button @click="openSectionModal(section)"
                            class="btn btn-ghost btn-xs btn-circle" title="Sửa">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button @click="deleteSection(section, sIdx)" :disabled="saving || isLocked"
                            class="btn btn-ghost btn-xs btn-circle text-error" title="Xóa">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Section body --}}
            <div x-show="section._open" x-collapse>
                <div class="p-3 space-y-2">

                    {{-- Field list --}}
                    <template x-for="(field, fIdx) in section.fields" :key="field.id">
                        <div :class="field.is_active ? 'bg-white' : 'bg-base-200 opacity-60'"
                             class="rounded-lg border border-base-200">

                            {{-- Field row --}}
                            <div class="flex items-center gap-2 px-3 py-2.5">
                                {{-- Type badge --}}
                                <span class="badge badge-outline badge-xs shrink-0"
                                      x-text="getTypeLabel(field.field_type)"></span>

                                {{-- Label + key --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" x-text="field.label"></p>
                                    <p class="text-xs text-base-content/40 font-mono" x-text="field.field_key"></p>
                                </div>

                                {{-- Badges --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <span x-show="field.is_required"
                                          class="badge badge-warning badge-xs">*</span>
                                    <span x-show="!field.is_active"
                                          class="badge badge-error badge-xs">off</span>
                                </div>

                                {{-- Field actions --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <button @click="moveFieldUp(section, fIdx)" :disabled="fIdx===0 || saving"
                                            class="btn btn-ghost btn-xs btn-circle" title="Lên">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button @click="moveFieldDown(section, fIdx)" :disabled="fIdx===section.fields.length-1 || saving"
                                            class="btn btn-ghost btn-xs btn-circle" title="Xuống">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button @click="openFieldModal(section, field)"
                                            class="btn btn-ghost btn-xs btn-circle" title="Sửa">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="toggleField(section, field)"
                                            :disabled="saving"
                                            :class="field.is_active ? 'text-warning' : 'text-success'"
                                            class="btn btn-ghost btn-xs btn-circle"
                                            :title="field.is_active ? 'Deactivate' : 'Activate'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path x-show="field.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            <path x-show="!field.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Inline options (choice fields) --}}
                            <div x-show="isChoiceType(field.field_type)" class="border-t border-base-100 px-3 pb-2 pt-1.5">
                                <p class="text-xs font-semibold text-base-content/50 uppercase mb-1.5">Lựa chọn</p>

                                <div class="space-y-1 mb-2">
                                    <template x-for="(opt, oIdx) in field.options" :key="opt.id">
                                        <div class="flex items-center gap-2 group">
                                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="moveOptionUp(field, oIdx)" :disabled="oIdx===0 || saving"
                                                        class="btn btn-ghost btn-xs btn-circle p-0 h-5 min-h-0 w-5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                                </button>
                                                <button @click="moveOptionDown(field, oIdx)" :disabled="oIdx===field.options.length-1 || saving"
                                                        class="btn btn-ghost btn-xs btn-circle p-0 h-5 min-h-0 w-5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            </div>
                                            <span class="text-xs font-mono text-base-content/40 w-24 truncate" x-text="opt.option_value"></span>
                                            <span class="text-sm flex-1 truncate" x-text="opt.label"></span>
                                            <span x-show="opt.is_other" class="badge badge-ghost badge-xs">other</span>
                                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openOptionEdit(field, opt)"
                                                        class="btn btn-ghost btn-xs btn-circle p-0 h-5 min-h-0 w-5">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteOption(field, opt, oIdx)"
                                                        :disabled="saving || isLocked"
                                                        class="btn btn-ghost btn-xs btn-circle text-error p-0 h-5 min-h-0 w-5">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Add / edit option inline form --}}
                                <div x-show="optForm.fieldId === field.id" x-cloak class="mt-2">
                                    <div class="flex gap-2 flex-wrap items-end bg-base-50 rounded-lg p-2 border border-base-300">
                                        <div class="form-control">
                                            <label class="label py-0"><span class="label-text text-xs">Value (machine)</span></label>
                                            <input type="text" x-model="optForm.optionValue"
                                                   @input="if(!optForm.labelEdited) optForm.label = optForm.optionValue"
                                                   :readonly="optForm.id && isLocked"
                                                   class="input input-bordered input-xs font-mono w-32"
                                                   placeholder="vd: chatgpt">
                                        </div>
                                        <div class="form-control flex-1 min-w-36">
                                            <label class="label py-0"><span class="label-text text-xs">Nhãn hiển thị</span></label>
                                            <input type="text" x-model="optForm.label"
                                                   @input="optForm.labelEdited = true"
                                                   class="input input-bordered input-xs w-full"
                                                   placeholder="vd: ChatGPT">
                                        </div>
                                        <label class="flex items-center gap-1 text-xs cursor-pointer pb-0.5">
                                            <input type="checkbox" x-model="optForm.isOther" class="checkbox checkbox-xs"> Khác
                                        </label>
                                        <div class="flex gap-1 pb-0.5">
                                            <button @click="saveOption(field)" :disabled="saving"
                                                    class="btn btn-primary btn-xs">
                                                <span x-text="optForm.id ? 'Lưu' : 'Thêm'"></span>
                                            </button>
                                            <button @click="closeOptionForm()" class="btn btn-ghost btn-xs">Hủy</button>
                                        </div>
                                    </div>
                                </div>

                                <button x-show="optForm.fieldId !== field.id"
                                        @click="openOptionAdd(field)"
                                        class="btn btn-ghost btn-xs gap-1 mt-1 text-base-content/50">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    Thêm lựa chọn
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Add field button --}}
                    <button @click="openFieldModal(section)"
                            class="w-full border-2 border-dashed border-base-300 hover:border-primary hover:text-primary rounded-lg py-2.5 text-sm text-base-content/40 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm field vào section này
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ─────────────────────────────────────────────────────────── --}}
    {{-- MODAL: Section --}}
    <div x-show="sModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="sModal.open = false"></div>
        <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
            <h3 class="font-bold text-lg mb-4" x-text="sModal.id ? 'Sửa section' : 'Thêm section'"></h3>

            <div class="form-control mb-4">
                <label class="label pb-1"><span class="label-text font-medium">Tiêu đề section <span class="text-error">*</span></span></label>
                <input type="text" x-model="sModal.title" class="input input-bordered" placeholder="VD: Thông tin cá nhân"
                       @keydown.enter="saveSection()">
            </div>

            <div class="flex justify-end gap-2">
                <button @click="sModal.open = false" class="btn btn-ghost btn-sm">Hủy</button>
                <button @click="saveSection()" :disabled="saving || !sModal.title.trim()"
                        class="btn btn-primary btn-sm">
                    <span x-show="saving" class="loading loading-spinner loading-xs mr-1"></span>
                    <span x-text="sModal.id ? 'Lưu' : 'Thêm'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────── --}}
    {{-- MODAL: Field --}}
    <div x-show="fModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="fModal.open = false"></div>
        <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h3 class="font-bold text-lg mb-5" x-text="fModal.id ? 'Sửa field' : 'Thêm field'"></h3>

            <div class="space-y-4">
                {{-- Label --}}
                <div class="form-control">
                    <label class="label pb-1"><span class="label-text font-medium">Nhãn hiển thị <span class="text-error">*</span></span></label>
                    <input type="text" x-model="fModal.label"
                           @input="if(!fModal.keyEdited && !fModal.id) fModal.fieldKey = toSnake(fModal.label)"
                           class="input input-bordered input-sm" placeholder="VD: Họ và tên">
                </div>

                {{-- Field key --}}
                <div class="form-control">
                    <label class="label pb-1">
                        <span class="label-text font-medium">Field key <span class="text-error">*</span></span>
                        <span class="label-text-alt text-base-content/40">unique trong survey</span>
                    </label>
                    <input type="text" x-model="fModal.fieldKey"
                           @input="fModal.keyEdited = true"
                           :readonly="fModal.id && isLocked"
                           class="input input-bordered input-sm font-mono"
                           placeholder="vd: full_name">
                    <span class="label-text-alt text-base-content/40 mt-1">Chỉ chữ thường, số, dấu _</span>
                </div>

                {{-- Field type --}}
                <div class="form-control">
                    <label class="label pb-1"><span class="label-text font-medium">Loại field <span class="text-error">*</span></span></label>
                    <select x-model.number="fModal.fieldType"
                            :disabled="fModal.id && isLocked"
                            class="select select-bordered select-sm">
                        <template x-for="ft in fieldTypes" :key="ft.value">
                            <option :value="ft.value" x-text="ft.label"></option>
                        </template>
                    </select>
                </div>

                {{-- Rules: text/textarea/number/rating --}}
                <div x-show="hasRulesType(fModal.fieldType)" class="grid grid-cols-2 gap-3">
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-sm">Min</span></label>
                        <input type="number" x-model.number="fModal.ruleMin" class="input input-bordered input-sm">
                    </div>
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-sm">Max</span></label>
                        <input type="number" x-model.number="fModal.ruleMax" class="input input-bordered input-sm">
                    </div>
                </div>

                {{-- Max select (checkbox only) --}}
                <div x-show="fModal.fieldType === 6" class="form-control">
                    <label class="label pb-1"><span class="label-text font-medium">Tối đa được chọn</span></label>
                    <input type="number" x-model.number="fModal.ruleMaxSelect"
                           class="input input-bordered input-sm w-28" min="1">
                </div>

                {{-- Placeholder (text/textarea) --}}
                <div x-show="fModal.fieldType === 1 || fModal.fieldType === 2" class="form-control">
                    <label class="label pb-1"><span class="label-text font-medium">Placeholder</span></label>
                    <input type="text" x-model="fModal.placeholder"
                           class="input input-bordered input-sm" placeholder="Gợi ý nhập liệu...">
                </div>

                {{-- Note for choice fields --}}
                <div x-show="isChoiceType(fModal.fieldType)"
                     class="alert alert-info py-2 text-xs">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Thêm lựa chọn trực tiếp trên card field sau khi lưu.
                </div>

                {{-- Required --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="fModal.isRequired" class="checkbox checkbox-sm checkbox-primary">
                    <span class="text-sm font-medium">Bắt buộc điền</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button @click="fModal.open = false" class="btn btn-ghost btn-sm">Hủy</button>
                <button @click="saveField()" :disabled="saving || !fModal.label.trim() || !fModal.fieldKey.trim()"
                        class="btn btn-primary btn-sm">
                    <span x-show="saving" class="loading loading-spinner loading-xs mr-1"></span>
                    <span x-text="fModal.id ? 'Lưu thay đổi' : 'Thêm field'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function surveyBuilder(sectionsData, fieldTypes, surveyId, isLocked, csrfToken) {
    return {
        sections: sectionsData.map(s => ({ ...s, _open: true })),
        fieldTypes,
        surveyId,
        isLocked,
        csrfToken,
        saving: false,
        flash: { text: '', type: 'success' },

        // ── Section modal ──────────────────────────────────────────────
        sModal: { open: false, id: null, title: '' },

        openSectionModal(section = null) {
            this.sModal = {
                open:  true,
                id:    section?.id ?? null,
                title: section?.title ?? '',
            };
        },

        async saveSection() {
            if (!this.sModal.title.trim()) return;
            const url = this.sModal.id
                ? `/dashboard/surveys/${this.surveyId}/sections/${this.sModal.id}`
                : `/dashboard/surveys/${this.surveyId}/sections`;
            const method = this.sModal.id ? 'PUT' : 'POST';

            const res = await this.api(url, method, { title: this.sModal.title });
            if (!res) return;

            if (this.sModal.id) {
                const s = this.sections.find(s => s.id === this.sModal.id);
                if (s) s.title = res.data.title;
            } else {
                this.sections.push({ ...res.data, fields: [], _open: true });
            }
            this.sModal.open = false;
            this.ok(res.message);
        },

        async deleteSection(section, idx) {
            if (!confirm(`Xóa section "${section.title}"? Các field bên trong cũng sẽ bị xóa.`)) return;
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/sections/${section.id}`, 'DELETE');
            if (!res) return;
            this.sections.splice(idx, 1);
            this.ok(res.message);
        },

        async moveSectionUp(idx) {
            if (idx === 0) return;
            [this.sections[idx - 1], this.sections[idx]] = [this.sections[idx], this.sections[idx - 1]];
            await this.reorder('sections', this.sections);
        },

        async moveSectionDown(idx) {
            if (idx === this.sections.length - 1) return;
            [this.sections[idx + 1], this.sections[idx]] = [this.sections[idx], this.sections[idx + 1]];
            await this.reorder('sections', this.sections);
        },

        // ── Field modal ────────────────────────────────────────────────
        fModal: {
            open: false, id: null, sectionId: null,
            fieldKey: '', keyEdited: false,
            label: '', fieldType: 1, isRequired: false,
            ruleMin: null, ruleMax: null, ruleMaxSelect: null,
            placeholder: '',
        },

        openFieldModal(section, field = null) {
            this.fModal = {
                open:         true,
                id:           field?.id ?? null,
                sectionId:    section.id,
                fieldKey:     field?.field_key ?? '',
                keyEdited:    !!field,
                label:        field?.label ?? '',
                fieldType:    field?.field_type ?? 1,
                isRequired:   field?.is_required ?? false,
                ruleMin:      field?.rule_min ?? null,
                ruleMax:      field?.rule_max ?? null,
                ruleMaxSelect:field?.rule_max_select ?? null,
                placeholder:  field?.placeholder ?? '',
            };
        },

        async saveField() {
            if (!this.fModal.label.trim() || !this.fModal.fieldKey.trim()) return;
            const url = this.fModal.id
                ? `/dashboard/surveys/${this.surveyId}/fields/${this.fModal.id}`
                : `/dashboard/surveys/${this.surveyId}/fields`;
            const method = this.fModal.id ? 'PUT' : 'POST';

            const body = {
                section_id:      this.fModal.sectionId,
                field_key:       this.fModal.fieldKey,
                label:           this.fModal.label,
                field_type:      this.fModal.fieldType,
                is_required:     this.fModal.isRequired,
                rule_min:        this.fModal.ruleMin || null,
                rule_max:        this.fModal.ruleMax || null,
                rule_max_select: this.fModal.ruleMaxSelect || null,
                placeholder:     this.fModal.placeholder || null,
            };

            const res = await this.api(url, method, body);
            if (!res) return;

            const section = this.sections.find(s => s.id === this.fModal.sectionId);
            if (!section) return;

            if (this.fModal.id) {
                const idx = section.fields.findIndex(f => f.id === this.fModal.id);
                if (idx >= 0) section.fields[idx] = res.data;
            } else {
                section.fields.push(res.data);
            }
            this.fModal.open = false;
            this.ok(res.message);
        },

        async toggleField(section, field) {
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/fields/${field.id}/toggle`, 'PATCH');
            if (!res) return;
            field.is_active = res.is_active;
            this.ok(res.message);
        },

        async moveFieldUp(section, idx) {
            if (idx === 0) return;
            [section.fields[idx - 1], section.fields[idx]] = [section.fields[idx], section.fields[idx - 1]];
            await this.reorder('fields', section.fields);
        },

        async moveFieldDown(section, idx) {
            if (idx === section.fields.length - 1) return;
            [section.fields[idx + 1], section.fields[idx]] = [section.fields[idx], section.fields[idx + 1]];
            await this.reorder('fields', section.fields);
        },

        // ── Option inline form ─────────────────────────────────────────
        optForm: { fieldId: null, id: null, optionValue: '', label: '', isOther: false, labelEdited: false },

        openOptionAdd(field) {
            this.optForm = { fieldId: field.id, id: null, optionValue: '', label: '', isOther: false, labelEdited: false };
        },

        openOptionEdit(field, opt) {
            this.optForm = { fieldId: field.id, id: opt.id, optionValue: opt.option_value, label: opt.label, isOther: opt.is_other, labelEdited: true };
        },

        closeOptionForm() {
            this.optForm.fieldId = null;
        },

        async saveOption(field) {
            if (!this.optForm.optionValue.trim() || !this.optForm.label.trim()) return;
            const body = { option_value: this.optForm.optionValue, label: this.optForm.label, is_other: this.optForm.isOther };

            let url, method;
            if (this.optForm.id) {
                url    = `/dashboard/surveys/${this.surveyId}/fields/${field.id}/options/${this.optForm.id}`;
                method = 'PUT';
            } else {
                url    = `/dashboard/surveys/${this.surveyId}/fields/${field.id}/options`;
                method = 'POST';
            }

            const res = await this.api(url, method, body);
            if (!res) return;

            if (this.optForm.id) {
                const idx = field.options.findIndex(o => o.id === this.optForm.id);
                if (idx >= 0) field.options[idx] = res.data;
            } else {
                field.options.push(res.data);
            }
            this.closeOptionForm();
            this.ok(res.message);
        },

        async deleteOption(field, opt, idx) {
            if (!confirm(`Xóa option "${opt.label}"?`)) return;
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/fields/${field.id}/options/${opt.id}`, 'DELETE');
            if (!res) return;
            field.options.splice(idx, 1);
            this.ok(res.message);
        },

        async moveOptionUp(field, idx) {
            if (idx === 0) return;
            [field.options[idx - 1], field.options[idx]] = [field.options[idx], field.options[idx - 1]];
            await this.reorder('options', field.options, field.id);
        },

        async moveOptionDown(field, idx) {
            if (idx === field.options.length - 1) return;
            [field.options[idx + 1], field.options[idx]] = [field.options[idx], field.options[idx + 1]];
            await this.reorder('options', field.options, field.id);
        },

        // ── Reorder helper ─────────────────────────────────────────────
        async reorder(type, items, fieldId = null) {
            const payload = items.map((item, i) => ({ id: item.id, sort_order: i + 1 }));
            let url;
            if (type === 'sections') url = `/dashboard/surveys/${this.surveyId}/sections/reorder`;
            else if (type === 'fields') url = `/dashboard/surveys/${this.surveyId}/fields/reorder`;
            else url = `/dashboard/surveys/${this.surveyId}/fields/${fieldId}/options/reorder`;
            await this.api(url, 'PATCH', { items: payload });
        },

        // ── Helpers ────────────────────────────────────────────────────
        getTypeLabel(val) {
            return this.fieldTypes.find(t => t.value === val)?.label ?? '?';
        },

        isChoiceType(val) {
            return [4, 5, 6].includes(val); // Select, Radio, Checkbox
        },

        hasRulesType(val) {
            return [1, 2, 3, 7].includes(val); // Text, Textarea, Number, Rating
        },

        toSnake(str) {
            return str.toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/đ/g, 'd')
                .replace(/[^a-z0-9\s_]/g, '')
                .trim()
                .replace(/\s+/g, '_')
                .substring(0, 100);
        },

        ok(msg)  { this.flash = { text: msg, type: 'success' }; setTimeout(() => this.flash.text = '', 3500); },
        err(msg) { this.flash = { text: msg, type: 'error' };   setTimeout(() => this.flash.text = '', 5000); },

        async api(url, method, body = null) {
            this.saving = true;
            try {
                const opts = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                };
                if (body && method !== 'GET') opts.body = JSON.stringify(body);

                const response = await fetch(url, opts);
                const json = await response.json();

                if (!response.ok) {
                    const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Có lỗi xảy ra.');
                    this.err(msg);
                    return null;
                }
                return json;
            } catch (e) {
                this.err('Lỗi kết nối. Vui lòng thử lại.');
                return null;
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
