/**
 * Conference Registration System - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // Accommodation toggle
    const accommodationToggle = document.getElementById('needs_accommodation');
    const accommodationFields = document.getElementById('accommodation-fields');

    if (accommodationToggle && accommodationFields) {
        accommodationToggle.addEventListener('change', function() {
            if (this.checked) {
                accommodationFields.classList.add('show');
                // Make fields required
                accommodationFields.querySelectorAll('input[type="date"], select').forEach(field => {
                    field.required = true;
                });
            } else {
                accommodationFields.classList.remove('show');
                // Remove required
                accommodationFields.querySelectorAll('input, select').forEach(field => {
                    field.required = false;
                });
            }
        });

        // Trigger on page load if already checked
        if (accommodationToggle.checked) {
            accommodationFields.classList.add('show');
        }
    }

    // Dietary "other" field toggle
    const dietarySelect = document.getElementById('dietary_requirements');
    const dietaryOther = document.getElementById('dietary_other_container');

    if (dietarySelect && dietaryOther) {
        dietarySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                dietaryOther.style.display = 'block';
                dietaryOther.querySelector('textarea').required = true;
            } else {
                dietaryOther.style.display = 'none';
                dietaryOther.querySelector('textarea').required = false;
            }
        });

        // Trigger on page load
        if (dietarySelect.value === 'other') {
            dietaryOther.style.display = 'block';
        }
    }

    // Abstract word counter
    const abstractText = document.getElementById('abstract_text');
    const wordCountDisplay = document.getElementById('word-count');
    const maxWords = 500;

    if (abstractText && wordCountDisplay) {
        function updateWordCount() {
            const text = abstractText.value.trim();
            const words = text ? text.split(/\s+/).length : 0;
            wordCountDisplay.textContent = `${words} / ${maxWords} words`;

            wordCountDisplay.classList.remove('warning', 'danger');
            if (words > maxWords) {
                wordCountDisplay.classList.add('danger');
            } else if (words > maxWords * 0.9) {
                wordCountDisplay.classList.add('warning');
            }
        }

        abstractText.addEventListener('input', updateWordCount);
        updateWordCount(); // Initial count
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password confirmation validation
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');

    if (passwordField && confirmPasswordField) {
        function validatePassword() {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        }

        passwordField.addEventListener('change', validatePassword);
        confirmPasswordField.addEventListener('input', validatePassword);
    }

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength');

    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let feedback = '';

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            if (password.length === 0) {
                feedback = '';
            } else if (strength < 2) {
                feedback = '<span class="text-danger">Weak</span>';
            } else if (strength < 4) {
                feedback = '<span class="text-warning">Medium</span>';
            } else {
                feedback = '<span class="text-success">Strong</span>';
            }

            strengthIndicator.innerHTML = feedback;
        });
    }

    // File upload validation
    const fileInput = document.getElementById('abstract_file');
    const maxFileSize = 5 * 1024 * 1024; // 5MB

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // Check file size
                if (file.size > maxFileSize) {
                    alert('File size exceeds 5MB limit');
                    this.value = '';
                    return;
                }

                // Check file type
                if (file.type !== 'application/pdf') {
                    alert('Only PDF files are allowed');
                    this.value = '';
                    return;
                }
            }
        });
    }

    // Confirmation dialogs
    const deleteButtons = document.querySelectorAll('[data-confirm]');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');

    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Date picker min/max for accommodation
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');

    if (checkInDate && checkOutDate) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        checkInDate.min = today;

        checkInDate.addEventListener('change', function() {
            checkOutDate.min = this.value;
            if (checkOutDate.value && checkOutDate.value < this.value) {
                checkOutDate.value = this.value;
            }
        });
    }

    // Search functionality for admin tables
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('data-table-body');

    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    }

});
