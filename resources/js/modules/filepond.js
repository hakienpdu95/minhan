/**
 * resources/js/modules/filepond.js
 * FilePond v4 + plugins wrapper
 *
 * Plugins đã đăng ký:
 *  · ImagePreview
 *  · FileValidateSize  (mặc định max 10MB)
 *  · FileRename
 *  · ImageExifOrientation
 *
 * Blade:  @vite(['resources/js/modules/filepond.js'])
 * Use:    initFilePond('#myInput', { ...options })
 */
import * as FilePond                 from 'filepond';
import 'filepond/dist/filepond.min.css';

import FilePondPluginImagePreview         from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import FilePondPluginFileValidateSize     from 'filepond-plugin-file-validate-size';
import FilePondPluginFileRename           from 'filepond-plugin-file-rename';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';

FilePond.registerPlugin(
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileRename,
);

const DEFAULTS = {
    allowMultiple:        true,
    maxFiles:             10,
    maxFileSize:          '10MB',
    labelIdle:            'Kéo thả file vào đây hoặc <span class="filepond--label-action">Duyệt file</span>',
    labelMaxFileSizeExceeded: 'File quá lớn',
    labelMaxFileSize:         'Kích thước tối đa: {filesize}',
    labelFileTypeNotAllowed:  'Loại file không được hỗ trợ',
    imagePreviewMaxHeight:    200,
};

const FilePondInstances = new Map();
window.FilePondInstances = FilePondInstances;

function initFilePond(selector, options = {}) {
    const el = typeof selector === 'string'
        ? document.querySelector(selector)
        : selector;
    if (!el) { console.warn('[FilePond] Element not found:', selector); return null; }

    const pond = FilePond.create(el, { ...DEFAULTS, ...options });
    FilePondInstances.set(typeof selector === 'string' ? selector : el.id, pond);
    return pond;
}

window.initFilePond = initFilePond;
window.FilePond = FilePond;
export { initFilePond, FilePond, FilePondInstances };
export default FilePond;
