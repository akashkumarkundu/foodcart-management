import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;

// FoodCart360 Theme Initializer & Switcher
window.FoodCart360 = {
    getTheme() {
        return localStorage.getItem('fc_theme') || document.documentElement.getAttribute('data-theme') || 'modern-light';
    },

    setTheme(themeName) {
        document.documentElement.setAttribute('data-theme', themeName);
        localStorage.setItem('fc_theme', themeName);
        document.cookie = `fc_theme=${themeName};path=/;max-age=31536000;SameSite=Lax`;

        // Dispatch custom event for charts or components listening for theme changes
        window.dispatchEvent(new CustomEvent('fc-theme-changed', { detail: { theme: themeName } }));
    },

    initTheme() {
        const savedTheme = this.getTheme();
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
};

// Immediately execute on page load
FoodCart360.initTheme();

document.addEventListener('livewire:navigated', () => {
    FoodCart360.initTheme();
});
