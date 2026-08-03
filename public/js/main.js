// ============================================================
// TANTRA GROUP OF INDUSTRIES
// Main JavaScript
// Interactive Components & UI Enhancements
// ============================================================

(function() {
    'use strict';

    // ============================================================
    // Mobile Navigation Toggle
    // ============================================================
    const mobileToggle = document.getElementById('mobileToggle');
    const mainNav = document.getElementById('mainNav');
    
    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', function() {
            mainNav.classList.toggle('open');
        });
        
        // Close nav when clicking outside
        document.addEventListener('click', function(e) {
            if (!mainNav.contains(e.target) && !mobileToggle.contains(e.target)) {
                mainNav.classList.remove('open');
            }
        });
    }

    // ============================================================
    // Dashboard Sidebar Toggle
    // ============================================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Close sidebar when a nav link is clicked on mobile
        const sidebarLinks = sidebar.querySelectorAll('.sidebar-nav a');
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                }
            });
        });
    }

    // ============================================================
    // Flash Messages Auto-Dismiss
    // ============================================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.3s ease';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });

    // ============================================================
    // Confirm Dialog Enhancement
    // ============================================================
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-confirm]');
        if (target) {
            const message = target.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });

    // ============================================================
    // Form Auto-Uppercase for Company Code
    // ============================================================
    const companyCodeFields = document.querySelectorAll('#company_code');
    companyCodeFields.forEach(function(field) {
        field.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // ============================================================
    // Active Navigation Highlight
    // ============================================================
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a, .sidebar-nav a');
    
    navLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && href !== '#' && href !== '/') {
            if (currentPath.startsWith(href) || currentPath === href) {
                link.classList.add('active');
            }
        } else if (href === '/' && currentPath === '/') {
            link.classList.add('active');
        }
    });

    // ============================================================
    // Table Row Click Enhancement
    // ============================================================
    const clickableRows = document.querySelectorAll('[data-href]');
    clickableRows.forEach(function(row) {
        row.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
        row.style.cursor = 'pointer';
    });

    // ============================================================
    // File Input Display Enhancement
    // ============================================================
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            const label = this.closest('.form-group').querySelector('label');
            if (label) {
                const fileIndicator = label.querySelector('.file-name') || document.createElement('span');
                fileIndicator.className = 'file-name';
                fileIndicator.textContent = ' - ' + fileName;
                if (!label.querySelector('.file-name')) {
                    label.appendChild(fileIndicator);
                } else {
                    label.querySelector('.file-name').textContent = ' - ' + fileName;
                }
            }
        });
    });

    // ============================================================
    // Smooth Scroll for Anchor Links
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ============================================================
    // Window Resize Handler for Sidebar
    // ============================================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('open');
            }
        }, 250);
    });

})();