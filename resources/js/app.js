import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.data('draftForm', () => ({
    dirty: false, saving: false,
    init() { window.addEventListener('beforeunload', event => { if (this.dirty && !this.saving) { event.preventDefault(); event.returnValue = ''; } }); },
    submit() { this.saving = true; this.dirty = false; },
}));
Alpine.start();
window.addEventListener('beforeprint', () => { document.querySelectorAll('details').forEach(el => { el.dataset.wasOpen=el.open?'yes':'no'; el.open=true; }); });
window.addEventListener('afterprint', () => { document.querySelectorAll('details[data-was-open]').forEach(el => { el.open=el.dataset.wasOpen==='yes'; delete el.dataset.wasOpen; }); });

// Print live form values, including unsaved edits and long multiline answers.
window.addEventListener('beforeprint', () => {
    document.querySelectorAll('.field').forEach(field => {
        const control=field.querySelector('input:not([type=file]):not([type=hidden]), textarea, select');
        const output=field.querySelector('.print-field-value');
        if (control && output) output.textContent=control.tagName==='SELECT' ? control.selectedOptions[0]?.textContent ?? '' : control.value;
    });
});
