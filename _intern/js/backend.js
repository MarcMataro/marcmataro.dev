// backend.js - Scripts per al panell de control

document.addEventListener('DOMContentLoaded', function() {
    // Amagar loading overlay
    setTimeout(() => {
        document.getElementById('loadingOverlay').style.opacity = '0';
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
        }, 300);
    }, 1000);

    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed');
    });

    // Navigation
    const navItems = document.querySelectorAll('.nav-item');
    const contentSections = document.querySelectorAll('.content-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            navItems.forEach(nav => nav.classList.remove('active'));
            contentSections.forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Show corresponding section
            const targetId = this.querySelector('a').getAttribute('href').substring(1);
            document.getElementById(targetId).classList.add('active');
            
            // Update page title
            document.querySelector('.content-header h1').textContent = this.querySelector('span').textContent;
        });
    });

    // New Post Modal
    const addPostBtn = document.getElementById('addPostBtn');
    const newPostModal = document.getElementById('newPostModal');
    const modalClose = document.querySelector('.modal-close');
    
    if (addPostBtn) {
        addPostBtn.addEventListener('click', () => {
            newPostModal.classList.add('active');
        });
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            newPostModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    newPostModal.addEventListener('click', (e) => {
        if (e.target === newPostModal) {
            newPostModal.classList.remove('active');
        }
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí aniria la lògica de validació i enviament
            console.log('Formulari enviat');
        });
    });

    // Table functionality
    const selectAllCheckbox = document.querySelector('.select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.data-table tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Notification bell
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            // Aquí aniria la lògica per mostrar notificacions
            console.log('Mostrant notificacions');
        });
    }

    // Traffic chart (simulat)
    const trafficChart = document.getElementById('trafficChart');
    if (trafficChart) {
        // Aquí aniria la inicialització del gràfic amb Chart.js
        console.log('Inicialitzant gràfic de tràfic');
    }

    // Responsive adjustments
    function handleResize() {
        if (window.innerWidth < 1024) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Executar al carregar

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N per a nova entrada
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            if (addPostBtn) addPostBtn.click();
        }
        
        // Escape per tancar modals
        if (e.key === 'Escape') {
            newPostModal.classList.remove('active');
        }
    });

    // Exportar dades (exemple)
    window.exportData = function(format) {
        console.log(`Exportant dades en format ${format}`);
        // Aquí aniria la lògica d'exportació
    };

    // Importar dades (exemple)
    window.importData = function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json,.csv';
        input.onchange = function(e) {
            const file = e.target.files[0];
            console.log(`Important arxiu: ${file.name}`);
            // Aquí aniria la lògica d'importació
        };
        input.click();
    };
});

// Funcions globals per a gestió d'estat
window.setStatus = function(element, status) {
    const statusElement = element.closest('tr').querySelector('.status-badge');
    if (statusElement) {
        statusElement.className = 'status-badge ' + status;
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }
};

// Gestió d'arxius (exemple)
window.uploadFile = function() {
    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.onchange = function(e) {
        Array.from(e.target.files).forEach(file => {
            console.log(`Pujant arxiu: ${file.name}`);
            // Aquí aniria la lògica de pujada d'arxius
        });
    };
    input.click();
};