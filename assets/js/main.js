// Theme Switching
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.classList.contains('dark-theme') ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Update theme class
    body.classList.remove(`${currentTheme}-theme`);
    body.classList.add(`${newTheme}-theme`);
    
    // Save theme preference
    localStorage.setItem('theme', newTheme);
    
    // Update theme icon
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    // Send theme update to server
    updateThemePreference(newTheme);
}

// Language Switching
function changeLanguage(lang) {
    // Save language preference
    localStorage.setItem('language', lang);
    
    // Send language update to server
    updateLanguagePreference(lang);
    
    // Reload page to apply new language
    window.location.reload();
}

// Update theme preference on server
function updateThemePreference(theme) {
    fetch('api/update-theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ theme: theme })
    })
    .catch(error => console.error('Error updating theme:', error));
}

// Update language preference on server
function updateLanguagePreference(lang) {
    fetch('api/update-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ language: lang })
    })
    .catch(error => console.error('Error updating language:', error));
}

// Real-time Clock
function updateClock() {
    const now = new Date();
    const clockElement = document.getElementById('clock');
    if (clockElement) {
        clockElement.textContent = now.toLocaleTimeString();
    }
}

// Initialize clock
setInterval(updateClock, 1000);
updateClock();

// Form Validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// AJAX Form Submission
function submitForm(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) successCallback(data);
        } else {
            if (errorCallback) errorCallback(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorCallback) errorCallback({ error: 'An error occurred' });
    });
}

// Show Loading Spinner
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    document.body.appendChild(spinner);
}

// Hide Loading Spinner
function hideLoading() {
    const spinner = document.querySelector('.spinner');
    if (spinner) {
        spinner.remove();
    }
}

// Common utility functions
const utils = {
    // Show alert message
    showAlert: function(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('main').insertBefore(alertDiv, document.querySelector('main').firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    },
    
    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString();
    },
    
    // Format time
    formatTime: function(time) {
        return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    
    // Get status class
    getStatusClass: function(status) {
        switch (status) {
            case 'present': return 'status-present';
            case 'absent': return 'status-absent';
            case 'late': return 'status-late';
            default: return '';
        }
    }
};

// QR Code functionality
const qrCode = {
    // Generate QR code
    generate: function(data) {
        const qr = new QRCode(document.getElementById('qrcode'), {
            text: data,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    },
    
    // Scan QR code
    scan: function(videoElement, callback) {
        const scanner = new Html5QrcodeScanner('reader', { 
            fps: 10, 
            qrbox: 250 
        });
        
        scanner.render((decodedText) => {
            scanner.clear();
            callback(decodedText);
        });
    }
};

// Attendance functionality
const attendance = {
    // Mark attendance
    mark: function(classId, studentId, status) {
        fetch('ajax/mark_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `class_id=${classId}&student_id=${studentId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                utils.showAlert(data.message);
                // Refresh attendance list
                this.refreshList(classId);
            } else {
                utils.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            utils.showAlert('An error occurred', 'danger');
            console.error('Error:', error);
        });
    },
    
    // Refresh attendance list
    refreshList: function(classId) {
        fetch(`ajax/get_attendance.php?class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateList(data.attendance);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    
    // Update attendance list in DOM
    updateList: function(attendance) {
        const tbody = document.querySelector('#attendanceTable tbody');
        tbody.innerHTML = '';
        
        attendance.forEach(record => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${record.student_name}</td>
                <td>${utils.formatTime(record.timestamp)}</td>
                <td class="${utils.getStatusClass(record.status)}">
                    ${record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Initialize theme from localStorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.body.classList.remove('light-theme', 'dark-theme');
        document.body.classList.add(`${savedTheme}-theme`);
    }
});

// Export functions for use in other modules
window.app = {
    toggleTheme,
    changeLanguage,
    validateForm,
    submitForm,
    showLoading,
    hideLoading,
    showAlert
}; 