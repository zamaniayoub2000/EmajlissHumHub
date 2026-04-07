/**
 * E-Service Module JavaScript
 */
(function () {
    'use strict';

    /**
     * Toggle switch functionality - sync hidden input with toggle.
     */
    function initToggles() {
        document.querySelectorAll('.es-toggle-switch input[type="checkbox"]').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                // Find the associated hidden input (previous sibling with same base name)
                var name = this.name;
                var hiddenInput = document.querySelector('input[type="hidden"][name="' + name + '"]');
                if (hiddenInput) {
                    hiddenInput.value = this.checked ? '1' : '0';
                }
            });
        });
    }

    /**
     * File upload preview with drag and drop support.
     */
    function initFileUpload() {
        var uploadAreas = document.querySelectorAll('.es-file-upload');

        uploadAreas.forEach(function (area) {
            var input = area.querySelector('input[type="file"]');
            var previewContainer = area.querySelector('.es-file-preview');

            if (!input || !previewContainer) return;

            // Drag and drop visual feedback
            ['dragenter', 'dragover'].forEach(function (evt) {
                area.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    area.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (evt) {
                area.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    area.classList.remove('dragover');
                });
            });

            // File selection preview
            input.addEventListener('change', function () {
                previewContainer.innerHTML = '';

                if (this.files && this.files.length > 0) {
                    Array.from(this.files).forEach(function (file) {
                        var item = document.createElement('span');
                        item.className = 'es-file-preview-item';

                        var icon = file.type.indexOf('pdf') !== -1 ? 'fa-file-pdf' : 'fa-file-image';
                        var size = formatFileSize(file.size);

                        item.innerHTML = '<i class="fa ' + icon + '"></i> ' +
                            truncateFilename(file.name, 30) + ' (' + size + ')';

                        previewContainer.appendChild(item);
                    });
                }
            });
        });
    }

    /**
     * Format file size to human readable string.
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /**
     * Truncate filename if too long.
     */
    function truncateFilename(name, maxLen) {
        if (name.length <= maxLen) return name;
        var ext = name.lastIndexOf('.') !== -1 ? name.substring(name.lastIndexOf('.')) : '';
        return name.substring(0, maxLen - ext.length - 3) + '...' + ext;
    }

    /**
     * Form validation visual feedback.
     */
    function initFormValidation() {
        var forms = document.querySelectorAll('.es-form form');

        forms.forEach(function (form) {
            form.addEventListener('submit', function () {
                var requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function (field) {
                    if (!field.value.trim()) {
                        field.classList.add('has-error');
                        field.style.borderColor = '#dc3545';
                    } else {
                        field.classList.remove('has-error');
                        field.style.borderColor = '';
                    }
                });
            });

            // Clear error on input
            form.querySelectorAll('.form-control').forEach(function (field) {
                field.addEventListener('input', function () {
                    this.classList.remove('has-error');
                    this.style.borderColor = '';
                });
            });
        });
    }

    /**
     * Dynamic form field show/hide based on type.
     */
    function initDynamicFields() {
        var typeSelect = document.querySelector('[data-es-type-selector]');
        if (!typeSelect) return;

        typeSelect.addEventListener('change', function () {
            var selectedType = this.value;
            document.querySelectorAll('[data-es-field-group]').forEach(function (group) {
                var types = group.getAttribute('data-es-field-group').split(',');
                if (types.indexOf(selectedType) !== -1 || types.indexOf('all') !== -1) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });
        });
    }

    /**
     * Show/hide "Préciser" field when "Autre" is selected in event dropdown.
     */
    function initPreciserField() {
        var eventSelect = document.getElementById('event-name-select');
        var preciserContainer = document.getElementById('preciser-container');

        if (!eventSelect || !preciserContainer) return;

        function isAutreSelected(select) {
            var selectedOption = select.options[select.selectedIndex];
            return selectedOption && selectedOption.text === 'Autre';
        }

        eventSelect.addEventListener('change', function () {
            if (isAutreSelected(this)) {
                preciserContainer.style.display = '';
            } else {
                preciserContainer.style.display = 'none';
                var input = preciserContainer.querySelector('input');
                if (input) input.value = '';
            }
        });

        // Check initial state
        if (isAutreSelected(eventSelect)) {
            preciserContainer.style.display = '';
        }
    }

    /**
     * Initialize all modules on DOM ready.
     */
    function init() {
        initToggles();
        initFileUpload();
        initFormValidation();
        initDynamicFields();
        initPreciserField();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init on Pjax success (HumHub uses Pjax)
    $(document).on('pjax:success', init);

})();
