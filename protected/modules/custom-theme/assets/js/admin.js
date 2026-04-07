/**
 * Custom Theme Admin
 */
(function() {
    'use strict';
    // Tab support dans les textareas code editor
    document.querySelectorAll('.ct-code-editor').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                var s = this.selectionStart;
                var end = this.selectionEnd;
                this.value = this.value.substring(0, s) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = s + 4;
            }
        });
    });
})();
