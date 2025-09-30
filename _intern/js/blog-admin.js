/**
 * Blog Admin JavaScript
 * Gestió integral del blog multilingüe
 */

// Variables globals
let currentTab = 'entrades';
let blogAPI = null;

// Inicialització
document.addEventListener('DOMContentLoaded', function() {
    // Inicialitzar API del blog
    blogAPI = new BlogAPI();
    
    // Carregar dades inicials
    carregarDadesInicials();
    
    // Configurar event listeners
    configurarEventListeners();
    
    // Inicialitzar editors de text
    inicialitzarEditors();
});

/**
 * API del Blog - Comunicació amb el backend
 */
class BlogAPI {
    constructor() {
        this.baseURL = window.location.origin + window.location.pathname.replace('blog.php', '');
    }
    
    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(this.baseURL + 'api/blog/' + endpoint, options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            mostrarNotificacio('Error de connexió amb el servidor', 'error');
            throw error;
        }
    }
    
    // Mètodes específics per cada entitat
    async obtenirEntrades(filtres = {}) {
        return this.request('entrades?' + new URLSearchParams(filtres));
    }
    
    async crearEntrada(dades) {
        return this.request('entrades', 'POST', dades);
    }
    
    async actualitzarEntrada(id, dades) {
        return this.request(`entrades/${id}`, 'PUT', dades);
    }
    
    async eliminarEntrada(id) {
        return this.request(`entrades/${id}`, 'DELETE');
    }
    
    async obtenirCategories(idioma = 'ca') {
        return this.request(`categories?idioma=${idioma}`);
    }
    
    async obtenirComentaris(filtres = {}) {
        return this.request('comentaris?' + new URLSearchParams(filtres));
    }
    
    async moderarComentari(id, accio) {
        return this.request(`comentaris/${id}/${accio}`, 'PUT');
    }
    
    async obtenirUsuaris(filtres = {}) {
        return this.request('usuaris?' + new URLSearchParams(filtres));
    }
}

/**
 * Gestió de pestanyes
 */
function canviarTab(tabName) {
    // Actualitzar navegació
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[onclick="canviarTab('${tabName}')"]`).classList.add('active');
    
    // Mostrar contingut
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    currentTab = tabName;
    
    // Carregar dades segons la pestanya
    switch(tabName) {
        case 'entrades':
            carregarEntrades();
            break;
        case 'categories':
            carregarCategories();
            break;
        case 'comentaris':
            carregarComentaris();
            break;
        case 'usuaris':
            carregarUsuaris();
            break;
        case 'configuracio':
            carregarConfiguracio();
            break;
    }
}

/**
 * Carrega les dades inicials de la pàgina
 */
async function carregarDadesInicials() {
    // Carregar entrades per defecte
    await carregarEntrades();
}

/**
 * Configura els event listeners
 */
function configurarEventListeners() {
    // Filtres d'entrades
    document.getElementById('filtro-estat-entrades')?.addEventListener('change', filtrarEntrades);
    document.getElementById('filtro-idioma-entrades')?.addEventListener('change', filtrarEntrades);
    
    // Filtres de categories
    document.getElementById('filtro-idioma-categories')?.addEventListener('change', filtrarCategories);
    
    // Filtres de comentaris
    document.getElementById('filtro-estat-comentaris')?.addEventListener('change', filtrarComentaris);
    
    // Filtres d'usuaris
    document.getElementById('filtro-rol-usuaris')?.addEventListener('change', filtrarUsuaris);
    
    // Tancar modals quan es clica fora
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
}

/**
 * Carrega la llista d'entrades
 */
async function carregarEntrades() {
    const tbody = document.querySelector('#taula-entrades tbody');
    if (!tbody) return;
    
    try {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Carregant...</td></tr>';
        
        const filtres = {
            estat: document.getElementById('filtro-estat-entrades')?.value || '',
            idioma: document.getElementById('filtro-idioma-entrades')?.value || ''
        };
        
        const entrades = await blogAPI.obtenirEntrades(filtres);
        
        if (entrades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hi ha entrades</td></tr>';
            return;
        }
        
        tbody.innerHTML = entrades.map(entrada => `
            <tr>
                <td>
                    <strong>${entrada.titol}</strong>
                    ${entrada.destacat ? '<span class="badge badge-primary">Destacat</span>' : ''}
                </td>
                <td>${entrada.autor_nom}</td>
                <td>
                    <img src="../img/${entrada.idioma}.png" alt="${entrada.idioma}" width="20" height="15">
                    ${entrada.idioma.toUpperCase()}
                </td>
                <td><span class="estat ${entrada.estat}">${entrada.estat}</span></td>
                <td>${formatarData(entrada.data_publicacio)}</td>
                <td>${entrada.visites || 0}</td>
                <td>${entrada.comentaris_count || 0}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="editarEntrada(${entrada.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarEntrada(${entrada.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error carregant entrades</td></tr>';
    }
}

/**
 * Carrega l'arbre de categories
 */
async function carregarCategories() {
    const container = document.getElementById('categories-tree');
    if (!container) return;
    
    try {
        container.innerHTML = '<div class="text-center">Carregant categories...</div>';
        
        const idioma = document.getElementById('filtro-idioma-categories')?.value || 'ca';
        const categories = await blogAPI.obtenirCategories(idioma);
        
        container.innerHTML = renderitzarArbreCategories(categories);
        
    } catch (error) {
        container.innerHTML = '<div class="text-center text-danger">Error carregant categories</div>';
    }
}

/**
 * Renderitza l'arbre jeràrquic de categories
 */
function renderitzarArbreCategories(categories, parentId = null, level = 0) {
    const categoriesNivell = categories.filter(cat => cat.categoria_pare_id == parentId);
    
    if (categoriesNivell.length === 0) return '';
    
    return categoriesNivell.map(categoria => {
        const fills = categories.filter(cat => cat.categoria_pare_id === categoria.id);
        const tenísFills = fills.length > 0;
        
        return `
            <div class="category-item ${tenísFills ? 'parent' : ''}" style="margin-left: ${level * 20}px">
                <div class="category-meta">
                    <div>
                        <strong>${categoria.nom}</strong>
                        ${categoria.descripcio ? `<div class="text-muted small">${categoria.descripcio}</div>` : ''}
                    </div>
                    <div class="category-stats">
                        ${categoria.entrades_count || 0} entrades
                    </div>
                    <div class="category-actions">
                        <button class="btn btn-sm btn-primary" onclick="editarCategoria(${categoria.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCategoria(${categoria.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${renderitzarArbreCategories(categories, categoria.id, level + 1)}
            </div>
        `;
    }).join('');
}

/**
 * Carrega la llista de comentaris
 */
async function carregarComentaris() {
    const container = document.getElementById('comentaris-list');
    if (!container) return;
    
    try {
        container.innerHTML = '<div class="text-center">Carregant comentaris...</div>';
        
        const estat = document.getElementById('filtro-estat-comentaris')?.value || 'pendent';
        const comentaris = await blogAPI.obtenirComentaris({ estat });
        
        if (comentaris.length === 0) {
            container.innerHTML = '<div class="text-center text-muted">No hi ha comentaris</div>';
            return;
        }
        
        container.innerHTML = comentaris.map(comentari => `
            <div class="comentari-item">
                <div class="comentari-header">
                    <div>
                        <span class="comentari-autor">${comentari.nom_autor}</span>
                        <span class="text-muted"> - ${comentari.entrada_titol}</span>
                    </div>
                    <div class="comentari-data">${formatarData(comentari.data_creacio)}</div>
                </div>
                <div class="comentari-contingut">${comentari.contingut}</div>
                <div class="comentari-actions">
                    <button class="btn btn-sm btn-success" onclick="aprovarComentari(${comentari.id})">
                        <i class="fas fa-check"></i> Aprovar
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="marcarSpam(${comentari.id})">
                        <i class="fas fa-exclamation-triangle"></i> Spam
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarComentari(${comentari.id})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        container.innerHTML = '<div class="text-center text-danger">Error carregant comentaris</div>';
    }
}

/**
 * Carrega la llista d'usuaris
 */
async function carregarUsuaris() {
    const tbody = document.querySelector('#taula-usuaris tbody');
    if (!tbody) return;
    
    try {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Carregant...</td></tr>';
        
        const rol = document.getElementById('filtro-rol-usuaris')?.value || '';
        const usuaris = await blogAPI.obtenirUsuaris({ rol });
        
        if (usuaris.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hi ha usuaris</td></tr>';
            return;
        }
        
        tbody.innerHTML = usuaris.map(usuari => `
            <tr>
                <td>
                    <img src="${usuari.avatar || '../img/avatar-default.png'}" 
                         alt="${usuari.nom}" width="40" height="40" 
                         style="border-radius: 50%;">
                </td>
                <td><strong>${usuari.nom}</strong></td>
                <td>${usuari.email}</td>
                <td><span class="rol ${usuari.rol}">${usuari.rol}</span></td>
                <td><span class="estat ${usuari.estat}">${usuari.estat}</span></td>
                <td>${usuari.entrades_count || 0}</td>
                <td>${formatarData(usuari.ultima_activitat)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="editarUsuari(${usuari.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarUsuari(${usuari.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error carregant usuaris</td></tr>';
    }
}

/**
 * Carrega la configuració del blog
 */
async function carregarConfiguracio() {
    // Implementar carrega de configuració
    console.log('Carregant configuració...');
}

/**
 * Filtres
 */
function filtrarEntrades() {
    carregarEntrades();
}

function filtrarCategories() {
    carregarCategories();
}

function filtrarComentaris() {
    carregarComentaris();
}

function filtrarUsuaris() {
    carregarUsuaris();
}

/**
 * Gestió de modals
 */
function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        
        // Focus al primer input
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function tancarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}

/**
 * Gestió de pestanyes del formulari
 */
function canviarFormTab(idioma) {
    // Actualitzar botons
    document.querySelectorAll('.form-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[onclick="canviarFormTab('${idioma}')"]`).classList.add('active');
    
    // Mostrar contingut
    document.querySelectorAll('.form-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`tab-form-${idioma}`).classList.add('active');
}

/**
 * Accions d'entrades
 */
async function guardarEntrada() {
    const form = document.getElementById('form-nova-entrada');
    const formData = new FormData(form);
    
    try {
        // Recollir dades multilingües
        const dades = {
            titols: {},
            slugs: {},
            extractes: {},
            continguts: {},
            estat: formData.get('estat'),
            format: formData.get('format'),
            destacat: formData.get('destacat') ? 1 : 0,
            comentaris_activats: formData.get('comentaris_activats') ? 1 : 0
        };
        
        // Processar dades per idioma
        const idiomes = ['ca', 'es', 'en']; // Ajustar segons idiomes actius
        idiomes.forEach(idioma => {
            dades.titols[idioma] = formData.get(`titol[${idioma}]`);
            dades.slugs[idioma] = formData.get(`slug[${idioma}]`);
            dades.extractes[idioma] = formData.get(`extracte[${idioma}]`);
            dades.continguts[idioma] = formData.get(`contingut[${idioma}]`);
        });
        
        await blogAPI.crearEntrada(dades);
        
        tancarModal('modal-nova-entrada');
        mostrarNotificacio('Entrada creada correctament', 'success');
        carregarEntrades();
        
    } catch (error) {
        mostrarNotificacio('Error creant l\'entrada', 'error');
    }
}

function editarEntrada(id) {
    // Implementar edició d'entrada
    console.log('Editant entrada:', id);
}

async function eliminarEntrada(id) {
    if (!confirm('Estàs segur que vols eliminar aquesta entrada?')) return;
    
    try {
        await blogAPI.eliminarEntrada(id);
        mostrarNotificacio('Entrada eliminada correctament', 'success');
        carregarEntrades();
    } catch (error) {
        mostrarNotificacio('Error eliminant l\'entrada', 'error');
    }
}

/**
 * Accions de comentaris
 */
async function aprovarComentari(id) {
    try {
        await blogAPI.moderarComentari(id, 'aprovar');
        mostrarNotificacio('Comentari aprovat', 'success');
        carregarComentaris();
    } catch (error) {
        mostrarNotificacio('Error aprovant el comentari', 'error');
    }
}

async function marcarSpam(id) {
    try {
        await blogAPI.moderarComentari(id, 'spam');
        mostrarNotificacio('Comentari marcat com spam', 'warning');
        carregarComentaris();
    } catch (error) {
        mostrarNotificacio('Error marcant com spam', 'error');
    }
}

async function eliminarComentari(id) {
    if (!confirm('Estàs segur que vols eliminar aquest comentari?')) return;
    
    try {
        await blogAPI.moderarComentari(id, 'eliminar');
        mostrarNotificacio('Comentari eliminat', 'success');
        carregarComentaris();
    } catch (error) {
        mostrarNotificacio('Error eliminant el comentari', 'error');
    }
}

/**
 * Accions en bloc per comentaris
 */
function aprovarSeleccionats() {
    const seleccionats = document.querySelectorAll('.comentari-checkbox:checked');
    // Implementar aprovació en bloc
    console.log('Aprovant comentaris seleccionats:', seleccionats.length);
}

function marcarSpamSeleccionats() {
    const seleccionats = document.querySelectorAll('.comentari-checkbox:checked');
    // Implementar marcatge com spam en bloc
    console.log('Marcant com spam comentaris seleccionats:', seleccionats.length);
}

/**
 * Inicialitza els editors de text
 */
function inicialitzarEditors() {
    // Inicialitzar editors rich text si s'utilitzen
    document.querySelectorAll('.editor').forEach(textarea => {
        // Aquí es pot integrar un editor com TinyMCE, CKEditor, etc.
        console.log('Inicialitzant editor per:', textarea.id);
    });
}

/**
 * Utilitats
 */
function formatarData(dataString) {
    if (!dataString) return '-';
    
    const data = new Date(dataString);
    return data.toLocaleDateString('ca-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function mostrarNotificacio(missatge, tipus = 'info') {
    // Crear notificació
    const notificacio = document.createElement('div');
    notificacio.className = `notificacio ${tipus}`;
    notificacio.innerHTML = `
        <div class="notificacio-content">
            <span class="notificacio-icon">
                ${tipus === 'success' ? '✓' : tipus === 'error' ? '✗' : tipus === 'warning' ? '⚠' : 'ℹ'}
            </span>
            <span class="notificacio-message">${missatge}</span>
            <button class="notificacio-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Afegir estils si no existeixen
    if (!document.getElementById('notificacions-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notificacions-styles';
        styles.textContent = `
            .notificacio {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                min-width: 300px;
                max-width: 500px;
                padding: 15px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease;
            }
            .notificacio.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
            .notificacio.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
            .notificacio.warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
            .notificacio.info { background: #cce5ff; color: #004085; border-left: 4px solid #007bff; }
            .notificacio-content { display: flex; align-items: center; gap: 10px; }
            .notificacio-close { 
                background: none; border: none; font-size: 18px; cursor: pointer; 
                margin-left: auto; opacity: 0.7;
            }
            .notificacio-close:hover { opacity: 1; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notificacio);
    
    // Auto-eliminar després de 5 segons
    setTimeout(() => {
        if (notificacio.parentElement) {
            notificacio.remove();
        }
    }, 5000);
}

// Generar slug automàticament des del títol
function generarSlug(titol) {
    return titol
        .toLowerCase()
        .replace(/[àáâãäå]/g, 'a')
        .replace(/[èéêë]/g, 'e')
        .replace(/[ìíîï]/g, 'i')
        .replace(/[òóôõö]/g, 'o')
        .replace(/[ùúûü]/g, 'u')
        .replace(/[ç]/g, 'c')
        .replace(/[ñ]/g, 'n')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
}

// Event listeners per generar slugs automàticament
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="titol-"]').forEach(input => {
        const idioma = input.id.split('-')[1];
        const slugInput = document.getElementById(`slug-${idioma}`);
        
        if (slugInput) {
            input.addEventListener('input', function() {
                if (!slugInput.value || slugInput.dataset.autoGenerated !== 'false') {
                    slugInput.value = generarSlug(this.value);
                    slugInput.dataset.autoGenerated = 'true';
                }
            });
            
            slugInput.addEventListener('input', function() {
                this.dataset.autoGenerated = 'false';
            });
        }
    });
});