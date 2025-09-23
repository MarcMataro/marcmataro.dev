// Language Selector Functionality
document.addEventListener('DOMContentLoaded', function() {
    const languageSelector = document.querySelector('.language-selector-bottom');
    const langToggle = document.querySelector('.language-selector-bottom .lang-toggle');
    const langDropdown = document.querySelector('.language-selector-bottom .lang-dropdown');
    const langOptions = document.querySelectorAll('.language-selector-bottom .lang-option');

    if (!languageSelector || !langToggle || !langDropdown) {
        return; // Exit if elements don't exist
    }

    // Toggle dropdown visibility
    function toggleDropdown() {
        const isExpanded = langToggle.getAttribute('aria-expanded') === 'true';
        langToggle.setAttribute('aria-expanded', !isExpanded);
        langDropdown.classList.toggle('show');
    }

    // Close dropdown
    function closeDropdown() {
        langToggle.setAttribute('aria-expanded', 'false');
        langDropdown.classList.remove('show');
    }

    // Update active language
    function updateActiveLanguage(selectedLang, selectedFlag, selectedText) {
        // Update toggle button
        const flagIcon = langToggle.querySelector('.flag-icon');
        const langText = langToggle.querySelector('.lang-text');
        
        if (flagIcon) flagIcon.src = selectedFlag;
        if (langText) langText.textContent = selectedText;

        // Update active state in dropdown
        langOptions.forEach(option => {
            option.classList.remove('active');
            if (option.dataset.lang === selectedLang) {
                option.classList.add('active');
            }
        });
    }

    // Event listeners
    langToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });

    // Handle language selection
    langOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedLang = this.dataset.lang;
            const selectedFlag = this.querySelector('.flag-icon').src;
            const selectedText = selectedLang.toUpperCase();
            
            updateActiveLanguage(selectedLang, selectedFlag, selectedText);
            closeDropdown();
            
            // Here you can add actual language switching logic
            // For now, we'll just update the URL parameter
            const url = new URL(window.location);
            url.searchParams.set('lang', selectedLang);
            window.history.pushState({}, '', url);
            
            // Optional: reload page or trigger language change
            // window.location.href = url.toString();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!languageSelector.contains(e.target)) {
            closeDropdown();
        }
    });

    // Close dropdown on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Initialize language based on URL parameter or default
    function initializeLanguage() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentLang = urlParams.get('lang') || 'ca'; // Default to Catalan
        
        const languages = {
            'ca': { flag: 'img/cat.png', text: 'CA' },
            'es': { flag: 'img/esp.png', text: 'ES' },
            'en': { flag: 'img/eng.png', text: 'EN' }
        };
        
        const lang = languages[currentLang];
        if (lang) {
            updateActiveLanguage(currentLang, lang.flag, lang.text);
        }
    }

    // Initialize on page load
    initializeLanguage();
});