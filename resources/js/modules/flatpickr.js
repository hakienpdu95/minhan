/**
 * resources/js/modules/flatpickr.js
 * Flatpickr date/time picker wrapper
 *
 * Blade:  @vite(['resources/js/modules/flatpickr.js'])
 * Use:    initDatePicker('#myInput', { ...options })
 *         initDateTimePicker('#myInput')
 *         initDateRangePicker('#myInput')
 */
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

// Vietnamese locale
const VI = {
    firstDayOfWeek: 1,
    weekdays: {
        shorthand: ['CN','T2','T3','T4','T5','T6','T7'],
        longhand:  ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'],
    },
    months: {
        shorthand: ['Th1','Th2','Th3','Th4','Th5','Th6','Th7','Th8','Th9','Th10','Th11','Th12'],
        longhand:  ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
    },
};

const BASE = { locale: VI, dateFormat: 'd/m/Y', allowInput: true };

const initDatePicker      = (sel, opts = {}) => flatpickr(sel, { ...BASE, ...opts });
const initDateTimePicker  = (sel, opts = {}) => flatpickr(sel, { ...BASE, enableTime: true, dateFormat: 'd/m/Y H:i', ...opts });
const initDateRangePicker = (sel, opts = {}) => flatpickr(sel, { ...BASE, mode: 'range', ...opts });
const initTimePicker      = (sel, opts = {}) => flatpickr(sel, { ...BASE, noCalendar: true, enableTime: true, dateFormat: 'H:i', ...opts });

window.initDatePicker      = initDatePicker;
window.initDateTimePicker  = initDateTimePicker;
window.initDateRangePicker = initDateRangePicker;
window.initTimePicker      = initTimePicker;
window.flatpickr           = flatpickr;
export { initDatePicker, initDateTimePicker, initDateRangePicker, initTimePicker };
export default flatpickr;
