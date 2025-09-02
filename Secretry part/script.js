// At the very beginning of the script (before DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function() {
    // Check and apply dark mode on page load
    const isDark = localStorage.getItem('theme') === 'dark' || 
                  (!localStorage.getItem('theme') && 
                   window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.body.classList.toggle('dark', isDark);

    // Setup dark mode toggle
    const switchMode = document.getElementById('switch-mode');
    if (switchMode) {
        switchMode.checked = isDark;
        switchMode.addEventListener('change', function() {
            const isDarkMode = this.checked;
            document.body.classList.toggle('dark', isDarkMode);
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        });
    }
});

// Rest of your existing code remains unchanged...
