window.onload = function () {
    document.querySelectorAll('.fade-in').forEach(el => {
        el.style.opacity = 0;
        setTimeout(() => {
            el.style.transition = "opacity 1s";
            el.style.opacity = 1;
        }, 500);
    });
}
document.addEventListener("DOMContentLoaded", () => {
  console.log("Website siap digunakan");
});

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.createElement('div');
    
    // Create overlay for mobile menu
    overlay.classList.add('fixed', 'inset-0', 'bg-black', 'bg-opacity-50', 'z-30', 'hidden');
    document.body.appendChild(overlay);

    // Toggle mobile menu
    function toggleMobileMenu() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    mobileMenuButton?.addEventListener('click', toggleMobileMenu);
    overlay.addEventListener('click', toggleMobileMenu);

    // Close mobile menu on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && !sidebar.classList.contains('-translate-x-full')) {
            toggleMobileMenu();
        }
    });
});

// Fade-in animation for elements
window.addEventListener('DOMContentLoaded', function() {
    const fadeElements = document.querySelectorAll('.fade-in');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('opacity-100');
                entry.target.classList.remove('opacity-0', 'translate-y-4');
            }
        });
    }, {
        threshold: 0.1
    });

    fadeElements.forEach(element => {
        element.classList.add('opacity-0', 'translate-y-4', 'transition-all', 'duration-700');
        observer.observe(element);
    });
});

// Format currency inputs
document.addEventListener('DOMContentLoaded', function() {
    const currencyInputs = document.querySelectorAll('input[data-type="currency"]');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                value = parseInt(value, 10).toLocaleString('id-ID');
                this.value = value;
            }
        });

        input.addEventListener('blur', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                value = parseInt(value, 10).toLocaleString('id-ID');
                this.value = value;
            }
        });
    });
});

// Initialize date picker if available
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    
                    // Add error message if not exists
                    if (!field.nextElementSibling?.classList.contains('error-message')) {
                        const error = document.createElement('p');
                        error.classList.add('text-red-500', 'text-xs', 'mt-1', 'error-message');
                        error.textContent = 'Field ini wajib diisi';
                        field.parentNode.insertBefore(error, field.nextSibling);
                    }
                } else {
                    field.classList.remove('border-red-500');
                    const error = field.nextElementSibling;
                    if (error?.classList.contains('error-message')) {
                        error.remove();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});