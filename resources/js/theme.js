/**
 * OpenKuasa Theme Manager
 * -----------------------
 * Suis mod cerah / mod gelap yang ringkas, pantas, dan profesional.
 * - Lalai sistem: Mod Cerah (Light Mode)
 * - Simpanan pilihan: localStorage ('openkuasa_theme')
 * - Bersih, pantas, tanpa animasi berat atau AI slop.
 */

class ThemeManager {
    constructor() {
        this.STORAGE_KEY = 'openkuasa_theme';
        this.currentTheme = this.getStoredTheme();
        this.applyThemeToDOM(this.currentTheme, false);
    }

    /**
     * Dapatkan tema dari simpanan tempatan.
     * Lalai sentiasa 'light' (Mod Cerah).
     */
    getStoredTheme() {
        try {
            const saved = localStorage.getItem(this.STORAGE_KEY);
            return saved === 'dark' ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    /**
     * Semak mod gelap aktif
     */
    isDark() {
        return this.currentTheme === 'dark';
    }

    /**
     * Guna tema pada DOM
     */
    applyThemeToDOM(theme, dispatchEvent = true) {
        this.currentTheme = theme;
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        try {
            localStorage.setItem(this.STORAGE_KEY, theme);
        } catch (e) {}

        if (dispatchEvent) {
            window.dispatchEvent(new CustomEvent('openkuasa:theme-changed', {
                detail: { theme, isDark: theme === 'dark' }
            }));
        }

        this.updateButtonsUI();
    }

    /**
     * Kemaskini UI semua butang toggle dan kad tema
     */
    updateButtonsUI() {
        const isDark = this.isDark();
        document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
            const iconEl = btn.querySelector('[data-theme-icon]');
            const textEl = btn.querySelector('[data-theme-text]');
            const trackEl = btn.querySelector('[data-theme-track]');

            if (iconEl) {
                iconEl.innerHTML = isDark
                    ? `<svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`
                    : `<svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>`;
            }
            if (textEl) {
                textEl.textContent = isDark ? 'Mod Gelap' : 'Mod Cerah';
            }
            btn.setAttribute('title', isDark ? 'Tukar ke Mod Cerah' : 'Tukar ke Mod Gelap');
            if (trackEl) {
                if (isDark) {
                    trackEl.classList.add('bg-indigo-600');
                    trackEl.classList.remove('bg-slate-300');
                } else {
                    trackEl.classList.remove('bg-indigo-600');
                    trackEl.classList.add('bg-slate-300');
                }
            }
        });

        // Tetapan Sistem radio buttons
        document.querySelectorAll('input[name="theme_preference"]').forEach(radio => {
            radio.checked = (radio.value === this.currentTheme);
        });

        // Tetapan Sistem kad pemilihan tema
        document.querySelectorAll('[data-theme-card]').forEach(card => {
            const cardTheme = card.getAttribute('data-theme-card');
            const isSelected = (cardTheme === this.currentTheme);
            if (isSelected) {
                card.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-500/20', 'bg-indigo-50/40', 'dark:bg-indigo-950/20');
                card.classList.remove('border-slate-200', 'dark:border-slate-800');
            } else {
                card.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-500/20', 'bg-indigo-50/40', 'dark:bg-indigo-950/20');
                card.classList.add('border-slate-200', 'dark:border-slate-800');
            }
        });
    }

    /**
     * Tukar tema Cerah <-> Gelap secara pantas & lancar
     */
    toggle() {
        const nextTheme = this.isDark() ? 'light' : 'dark';
        this.setTheme(nextTheme);
    }

    /**
     * Tetapkan tema secara langsung
     */
    setTheme(targetTheme) {
        this.applyThemeToDOM(targetTheme, true);
    }
}

// Pasang instans tunggal global
window.openkuasaTheme = new ThemeManager();

// Pasang event listener interaksi
document.addEventListener('DOMContentLoaded', () => {
    window.openkuasaTheme.updateButtonsUI();

    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-theme-toggle]');
        if (toggleBtn) {
            e.preventDefault();
            window.openkuasaTheme.toggle();
            return;
        }

        const selectBtn = e.target.closest('[data-theme-select]');
        if (selectBtn) {
            e.preventDefault();
            const targetTheme = selectBtn.getAttribute('data-theme-select');
            if (targetTheme && targetTheme !== window.openkuasaTheme.currentTheme) {
                window.openkuasaTheme.setTheme(targetTheme);
            }
        }
    });
});

export default window.openkuasaTheme;
