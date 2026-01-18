import './bootstrap';

// Wait for Livewire's Alpine to be available, then register our custom components
document.addEventListener('alpine:init', () => {
    // Alpine.js Global Data
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }));

    Alpine.data('modal', () => ({
        show: false,
        open() {
            this.show = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.show = false;
            document.body.style.overflow = '';
        }
    }));

    Alpine.data('sidebar', () => ({
        expanded: true,
        mobileOpen: false,
        toggle() {
            this.expanded = !this.expanded;
        },
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        }
    }));

    Alpine.data('cart', () => ({
        items: [],
        open: false,
        
        init() {
            const saved = localStorage.getItem('menupro_cart');
            if (saved) {
                this.items = JSON.parse(saved);
            }
        },
        
        add(item) {
            const existing = this.items.find(i => i.id === item.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({ ...item, quantity: 1 });
            }
            this.save();
            this.open = true;
        },
        
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
            this.save();
        },
        
        updateQuantity(id, quantity) {
            const item = this.items.find(i => i.id === id);
            if (item) {
                if (quantity <= 0) {
                    this.remove(id);
                } else {
                    item.quantity = quantity;
                    this.save();
                }
            }
        },
        
        clear() {
            this.items = [];
            this.save();
        },
        
        save() {
            localStorage.setItem('menupro_cart', JSON.stringify(this.items));
        },
        
        get total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        
        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },
        
        toggle() {
            this.open = !this.open;
        }
    }));

    Alpine.data('notification', () => ({
        show: false,
        message: '',
        type: 'success',
        timeout: null,
        
        notify(message, type = 'success', duration = 3000) {
            this.message = message;
            this.type = type;
            this.show = true;
            
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
            
            this.timeout = setTimeout(() => {
                this.show = false;
            }, duration);
        },
        
        close() {
            this.show = false;
        }
    }));
});

// Livewire event listeners
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', (event) => {
        // Dispatch to Alpine notification component
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { 
                message: event.message, 
                type: event.type || 'success' 
            } 
        }));
    });
    
    Livewire.on('cart-updated', () => {
        // Refresh cart data
        window.dispatchEvent(new CustomEvent('cart-updated'));
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Format currency helper
window.formatCurrency = (amount, currency = 'XOF') => {
    return new Intl.NumberFormat('fr-CI', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0
    }).format(amount);
};

// Format date helper
window.formatDate = (date) => {
    return new Intl.DateTimeFormat('fr-CI', {
        dateStyle: 'long',
        timeStyle: 'short'
    }).format(new Date(date));
};
