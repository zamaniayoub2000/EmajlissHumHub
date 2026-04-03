/**
 * Custom Theme Admin - Scripts d'administration
 * Gère les toggles AJAX et les interactions de l'interface admin
 */
(function() {
    'use strict';

    // Auto-resize des textareas code editor
    document.querySelectorAll('.ct-code-editor').forEach(function(editor) {
        editor.addEventListener('keydown', function(e) {
            // Support Tab dans les textareas
            if (e.key === 'Tab') {
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    });

})();
