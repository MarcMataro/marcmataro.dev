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
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        });
    }

    // Nota: La navegació ara funciona amb pàgines separades, 
    // no necessitem JavaScript per canviar sections

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

// ============================================================================
// PROJECTES PAGE FUNCTIONALITY
// ============================================================================

// Project Modal functionality
const addProjectBtn = document.getElementById('addProjectBtn');
const newProjectModal = document.getElementById('newProjectModal');
const projectModalClose = newProjectModal ? newProjectModal.querySelector('.modal-close') : null;
const newProjectForm = document.getElementById('newProjectForm');

if (addProjectBtn) {
    addProjectBtn.addEventListener('click', () => {
        newProjectModal.classList.add('active');
    });
}

// Add new project cards also trigger modal
const addNewCards = document.querySelectorAll('.project-card-admin.add-new');
addNewCards.forEach(card => {
    card.addEventListener('click', () => {
        if (newProjectModal) {
            newProjectModal.classList.add('active');
        }
    });
});

if (projectModalClose) {
    projectModalClose.addEventListener('click', () => {
        newProjectModal.classList.remove('active');
    });
}

// Close modal when clicking outside
if (newProjectModal) {
    newProjectModal.addEventListener('click', (e) => {
        if (e.target === newProjectModal) {
            newProjectModal.classList.remove('active');
        }
    });
}

// Project form submission
if (newProjectForm) {
    newProjectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        console.log('Nou projecte creat:', Object.fromEntries(formData));
        
        // Here you would send data to server
        // For now, just close modal and show success message
        newProjectModal.classList.remove('active');
        
        // Show success message (you can implement a toast notification)
        alert('Projecte creat amb èxit!');
        
        // Reset form
        this.reset();
    });
}

// Project filters functionality
const filterSelects = document.querySelectorAll('.filters-bar select');
const searchInput = document.querySelector('.search-filter input');

function filterProjects() {
    const projects = document.querySelectorAll('.project-card-admin:not(.add-new)');
    const statusFilter = document.querySelector('.filters-bar select:first-child')?.value || 'Tots els estats';
    const techFilter = document.querySelector('.filters-bar select:last-child')?.value || 'Totes les tecnologies';
    const searchTerm = searchInput?.value.toLowerCase() || '';
    
    projects.forEach(project => {
        const title = project.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = project.querySelector('p')?.textContent.toLowerCase() || '';
        const status = project.querySelector('.project-status')?.textContent || '';
        const techBadges = Array.from(project.querySelectorAll('.tech-badge')).map(badge => badge.textContent);
        
        let showProject = true;
        
        // Filter by search term
        if (searchTerm && !title.includes(searchTerm) && !description.includes(searchTerm)) {
            showProject = false;
        }
        
        // Filter by status
        if (statusFilter !== 'Tots els estats' && status !== statusFilter) {
            showProject = false;
        }
        
        // Filter by technology
        if (techFilter !== 'Totes les tecnologies' && !techBadges.includes(techFilter)) {
            showProject = false;
        }
        
        project.style.display = showProject ? 'block' : 'none';
    });
}

// Attach filter events
filterSelects.forEach(select => {
    select.addEventListener('change', filterProjects);
});

if (searchInput) {
    searchInput.addEventListener('input', debounce(filterProjects, 300));
}

// Project actions functionality
document.addEventListener('click', function(e) {
    // Edit project
    if (e.target.closest('.btn-icon i.fa-edit')) {
        const projectCard = e.target.closest('.project-card-admin');
        const projectTitle = projectCard.querySelector('h3').textContent;
        console.log('Editant projecte:', projectTitle);
        // Here you would open edit modal or redirect to edit page
        alert(`Editant projecte: ${projectTitle}`);
    }
    
    // Delete project
    if (e.target.closest('.btn-icon i.fa-trash')) {
        const projectCard = e.target.closest('.project-card-admin');
        const projectTitle = projectCard.querySelector('h3').textContent;
        
        if (confirm(`Estàs segur que vols eliminar el projecte "${projectTitle}"?`)) {
            // Here you would send delete request to server
            projectCard.style.opacity = '0';
            projectCard.style.transform = 'scale(0.9)';
            setTimeout(() => {
                projectCard.remove();
            }, 300);
            console.log('Projecte eliminat:', projectTitle);
        }
    }
    
    // View project
    if (e.target.closest('.btn-icon i.fa-eye')) {
        const projectCard = e.target.closest('.project-card-admin');
        const projectTitle = projectCard.querySelector('h3').textContent;
        console.log('Veient projecte:', projectTitle);
        // Here you would redirect to project view or open modal
        alert(`Veient projecte: ${projectTitle}`);
    }
});

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}