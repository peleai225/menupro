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

    Alpine.data('cookieConsent', () => ({
        showBanner: false,
        cookieName: 'menupro_cookie_consent',
        cookieExpiryDays: 365,

        init() {
            const consent = this.getConsent();
            if (!consent) {
                this.showBanner = true;
            }
        },

        getConsent() {
            const value = document.cookie
                .split('; ')
                .find(row => row.startsWith(this.cookieName + '='));
            return value ? decodeURIComponent(value.split('=')[1]) : null;
        },

        setCookie(value) {
            const date = new Date();
            date.setTime(date.getTime() + (this.cookieExpiryDays * 24 * 60 * 60 * 1000));
            document.cookie = `${this.cookieName}=${encodeURIComponent(value)}; expires=${date.toUTCString()}; path=/; SameSite=Lax`;
        },

        accept() {
            this.setCookie('accepted');
            this.showBanner = false;
        },

        decline() {
            this.setCookie('essential_only');
            this.showBanner = false;
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

    // Super Admin: cloche notifications (liste + marquer lu)
    Alpine.data('notificationBell', () => ({
        open: false,
        loading: false,
        items: [],
        unreadCount: 0,
        badgesUrl: window.__superAdminBadgesUrl || '',
        init() {
            this.badgesUrl = window.__superAdminBadgesUrl || '';
            if (this.badgesUrl) this.fetchBadges();
        },
        async loadNotifications() {
            this.loading = true;
            try {
                const url = this.badgesUrl.replace('sidebar-badges', 'notifications');
                const r = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await r.json();
                this.items = data.notifications || [];
                if (this.unreadCount > 0) {
                    const markUrl = this.badgesUrl.replace('sidebar-badges', 'notifications/mark-read');
                    await fetch(markUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                }
                await this.fetchBadges();
            } catch (e) {
                console.error('Notifications load error', e);
            }
            this.loading = false;
        },
        async fetchBadges() {
            if (!this.badgesUrl) return;
            try {
                const r = await fetch(this.badgesUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await r.json();
                this.unreadCount = data.unread_notifications ?? 0;
            } catch (_) {}
        },
        formatDate(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            const now = new Date();
            const diff = (now - d) / 60000;
            if (diff < 1) return 'À l\'instant';
            if (diff < 60) return `Il y a ${Math.floor(diff)} min`;
            if (diff < 1440) return `Il y a ${Math.floor(diff/60)} h`;
            return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
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
