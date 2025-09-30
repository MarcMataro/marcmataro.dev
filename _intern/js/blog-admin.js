// --- Codi JS funcional per a la gestió de formularis i TinyMCE ---
function mostrarFormulari(tipus) {
    const formulari = document.getElementById('formulari-idioma');
    const title = document.getElementById('form-title');
    const accio = document.getElementById('form-accio');
    const btnText = document.getElementById('btn-text');
    document.querySelector('#formulari-idioma form').reset();
    document.getElementById('form-id').value = '';
    document.getElementById('form-codi-hidden').value = '';
    if (tipus === 'nou') {
        title.textContent = 'Nou Idioma';
        accio.value = 'crear';
        btnText.textContent = 'Crear Idioma';
        document.getElementById('form-codi').disabled = false;
    }
    formulari.style.display = 'block';
    document.getElementById('form-codi').focus();
}
function editarIdioma(idioma) {
    const formulari = document.getElementById('formulari-idioma');
    const title = document.getElementById('form-title');
    const accio = document.getElementById('form-accio');
    const btnText = document.getElementById('btn-text');
    document.getElementById('form-id').value = idioma.id;
    document.getElementById('form-codi').value = idioma.codi;
    document.getElementById('form-codi-hidden').value = idioma.codi;
    document.getElementById('form-nom').value = idioma.nom;
    document.getElementById('form-nom-natiu').value = idioma.nom_natiu;
    document.getElementById('form-estat').value = idioma.estat;
    document.getElementById('form-ordre').value = idioma.ordre;
    document.getElementById('form-bandera').value = idioma.bandera_url || '';
    title.textContent = 'Editar Idioma';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Idioma';
    document.getElementById('form-codi').disabled = (idioma.codi === 'ca');
    formulari.style.display = 'block';
    document.getElementById('form-nom').focus();
}
function tancarFormulari() {
    document.getElementById('formulari-idioma').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarFormulari();
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
function mostrarFormulariCategoria(tipus) {
    const formulari = document.getElementById('formulari-categoria');
    const title = document.getElementById('form-title-categoria');
    const accio = document.getElementById('form-accio-categoria');
    const btnText = document.getElementById('btn-text-categoria');
    document.querySelector('#formulari-categoria form').reset();
    document.getElementById('form-id-categoria').value = '';
    if (tipus === 'nou') {
        title.textContent = 'Nova Categoria';
        accio.value = 'crear';
        btnText.textContent = 'Crear Categoria';
    }
    formulari.style.display = 'block';
    document.getElementById('form-slug-base').focus();
}
function editarCategoria(categoria) {
    const formulari = document.getElementById('formulari-categoria');
    const title = document.getElementById('form-title-categoria');
    const accio = document.getElementById('form-accio-categoria');
    const btnText = document.getElementById('btn-text-categoria');
    document.getElementById('form-id-categoria').value = categoria.id;
    document.getElementById('form-slug-base').value = categoria.slug_base;
    document.getElementById('form-nom-ca').value = categoria.traduccions?.ca?.nom || categoria.nom || '';
    document.getElementById('form-slug-ca').value = categoria.traduccions?.ca?.slug || '';
    document.getElementById('form-descripcio-ca').value = categoria.traduccions?.ca?.descripcio || '';
    document.getElementById('form-nom-es').value = categoria.traduccions?.es?.nom || '';
    document.getElementById('form-slug-es').value = categoria.traduccions?.es?.slug || '';
    document.getElementById('form-descripcio-es').value = categoria.traduccions?.es?.descripcio || '';
    document.getElementById('form-nom-en').value = categoria.traduccions?.en?.nom || '';
    document.getElementById('form-slug-en').value = categoria.traduccions?.en?.slug || '';
    document.getElementById('form-descripcio-en').value = categoria.traduccions?.en?.descripcio || '';
    document.getElementById('form-ordre-categoria').value = categoria.ordre || 0;
    if (categoria.categoria_pare_id) {
        document.getElementById('form-categoria-pare').value = categoria.categoria_pare_id;
    }
    title.textContent = 'Editar Categoria';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Categoria';
    formulari.style.display = 'block';
}
function tancarFormulariCategoria() {
    document.getElementById('formulari-categoria').style.display = 'none';
}
function mostrarFormulariTag(tipus) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    document.querySelector('#formulari-tag form').reset();
    document.getElementById('form-id-tag').value = '';
    if (tipus === 'nou') {
        title.textContent = 'Nou Tag';
        accio.value = 'crear';
        btnText.textContent = 'Crear Tag';
    }
    formulari.style.display = 'block';
    document.getElementById('form-slug-base-tag').focus();
}
function editarTag(tag) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    document.getElementById('form-id-tag').value = tag.id;
    document.getElementById('form-slug-base-tag').value = tag.slug_base;
    document.getElementById('form-nom-ca-tag').value = tag.traduccions?.ca?.nom || tag.nom || '';
    document.getElementById('form-slug-ca-tag').value = tag.traduccions?.ca?.slug || '';
    document.getElementById('form-descripcio-ca-tag').value = tag.traduccions?.ca?.descripcio || '';
    document.getElementById('form-nom-es-tag').value = tag.traduccions?.es?.nom || '';
    document.getElementById('form-slug-es-tag').value = tag.traduccions?.es?.slug || '';
    document.getElementById('form-descripcio-es-tag').value = tag.traduccions?.es?.descripcio || '';
    document.getElementById('form-nom-en-tag').value = tag.traduccions?.en?.nom || '';
    document.getElementById('form-slug-en-tag').value = tag.traduccions?.en?.slug || '';
    document.getElementById('form-descripcio-en-tag').value = tag.traduccions?.en?.descripcio || '';
    title.textContent = 'Editar Tag';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Tag';
    formulari.style.display = 'block';
}
function tancarFormulariTag() {
    document.getElementById('formulari-tag').style.display = 'none';
}
function mostrarFormulariEntrada(tipus) {
    const formulari = document.getElementById('formulari-entrada');
    const title = document.getElementById('form-title-entrada');
    const accio = document.getElementById('form-accio-entrada');
    const btnText = document.getElementById('btn-text-entrada');
    document.querySelector('#formulari-entrada form').reset();
    document.getElementById('form-id-entrada').value = '';
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.querySelector('.lang-tab[data-lang="ca"]').classList.add('active');
    document.querySelector('.lang-content[data-lang="ca"]').classList.add('active');
    if (tipus === 'nou') {
        title.textContent = 'Nova Entrada';
        accio.value = 'crear';
        btnText.textContent = 'Crear Entrada';
    }
    formulari.style.display = 'block';
    document.getElementById('form-titol-ca').focus();
}
function editarEntrada(entrada) {
    const formulari = document.getElementById('formulari-entrada');
    const title = document.getElementById('form-title-entrada');
    const accio = document.getElementById('form-accio-entrada');
    const btnText = document.getElementById('btn-text-entrada');
    document.getElementById('form-id-entrada').value = entrada.id;
    document.getElementById('form-idioma-original').value = entrada.idioma_original || 'ca';
    document.getElementById('form-estat').value = entrada.estat;
    document.getElementById('form-format').value = entrada.format;
    document.getElementById('form-comentaris').checked = entrada.comentaris_activats == 1;
    document.getElementById('form-destacat').checked = entrada.destacat == 1;
    if (entrada.traduccions) {
        if (entrada.traduccions.ca) {
            document.getElementById('form-titol-ca').value = entrada.traduccions.ca.titol || '';
            document.getElementById('form-resum-ca').value = entrada.traduccions.ca.resum || '';
            document.getElementById('form-contingut-ca').value = entrada.traduccions.ca.contingut || '';
        }
        if (entrada.traduccions.es) {
            document.getElementById('form-titol-es').value = entrada.traduccions.es.titol || '';
            document.getElementById('form-resum-es').value = entrada.traduccions.es.resum || '';
            document.getElementById('form-contingut-es').value = entrada.traduccions.es.contingut || '';
        }
        if (entrada.traduccions.en) {
            document.getElementById('form-titol-en').value = entrada.traduccions.en.titol || '';
            document.getElementById('form-resum-en').value = entrada.traduccions.en.resum || '';
            document.getElementById('form-contingut-en').value = entrada.traduccions.en.contingut || '';
        }
    }
    title.textContent = 'Editar Entrada';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Entrada';
    formulari.style.display = 'block';
}
function tancarFormulariEntrada() {
    ['form-contingut-ca', 'form-contingut-es', 'form-contingut-en'].forEach(id => {
        const textarea = document.getElementById(id);
        if (textarea) textarea.value = '';
    });
    const inputs = document.querySelectorAll('#formulari-entrada input, #formulari-entrada textarea');
    inputs.forEach(input => {
        if (input.type !== 'hidden') {
            input.value = '';
            if (input.type === 'checkbox') input.checked = false;
        }
    });
    const formulari = document.getElementById('formulari-entrada');
    if (formulari) {
        formulari.style.display = 'none';
    }
}
function canviarEstatEntrada(id, nouEstat) {
    if (confirm(`Segur que vols canviar l'estat de l'entrada a "${nouEstat}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accio" value="canviar_estat">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="nou_estat" value="${nouEstat}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
function eliminarEntrada(id, titol) {
    if (confirm(`Segur que vols eliminar l'entrada "${titol}"?\n\nAquesta acció no es pot desfer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accio" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const btnClose = document.querySelector('#formulari-entrada .btn-close');
    const btnCancel = document.querySelector('#formulari-entrada .btn-secondary');
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            tancarFormulariEntrada();
        });
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            tancarFormulariEntrada();
        });
    }
    const modalContainer = document.getElementById('formulari-entrada');
    if (modalContainer) {
        modalContainer.addEventListener('click', function(e) {
            if (e.target === modalContainer) {
                tancarFormulariEntrada();
            }
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const formulari = document.getElementById('formulari-entrada');
            if (formulari && formulari.style.display !== 'none') {
                tancarFormulariEntrada();
            }
        }
    });
    const langTabs = document.querySelectorAll('.lang-tab');
    const langContents = document.querySelectorAll('.lang-content');
    langTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.dataset.lang;
            langTabs.forEach(t => t.classList.remove('active'));
            langContents.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.querySelector(`.lang-content[data-lang="${lang}"]`).classList.add('active');
        });
    });
    if (typeof tinymce === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.tiny.cloud/1/ds0tgp458zh4vbyxcyhq2bgbf9wnk8sj1k8874ohwvqpmn39/tinymce/5/tinymce.min.js';
        script.referrerPolicy = 'origin';
        script.onload = function() {
            inicialitzarTiny();
        };
        document.head.appendChild(script);
    } else {
        inicialitzarTiny();
    }
    function inicialitzarTiny() {
        document.querySelectorAll('textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]').forEach(function(textarea) {
            textarea.removeAttribute('disabled');
            textarea.removeAttribute('readonly');
        });
        tinymce.init({
            selector: 'textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]',
            height: 400,
            menubar: true,
            statusbar: false,
            skin: 'oxide',
            content_css: false,
            content_style: `
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; margin: 10px; }
                img { max-width: 100%; height: auto; border-radius: 4px; }
                blockquote { border-left: 4px solid #3498db; padding-left: 1rem; margin: 1rem 0; background: #f8f9fa; padding: 1rem; border-radius: 4px; }
            `,
            convert_urls: true,
            relative_urls: false,
            remove_script_host: false,
            document_base_url: window.location.origin,
            branding: false,
            promotion: false,
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            image_class_list: [
                {title: 'Responsive', value: 'img-fluid'}
            ],
            image_list: [
                // Aquesta llista s'ha d'omplir des de PHP, així que la deixem buida aquí
            ],
            paste_as_text: false,
            paste_auto_cleanup_on_paste: true,
            paste_remove_styles: false,
            paste_remove_styles_if_webkit: false,
            table_toolbar: "tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",
            forced_root_block: false,
            readonly: false,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.setMode('design');
                    const textareaName = editor.getElement().name;
                    if (textareaName.includes('_es')) {
                        editor.getDoc().documentElement.lang = 'es';
                    } else if (textareaName.includes('_en')) {
                        editor.getDoc().documentElement.lang = 'en';
                    } else {
                        editor.getDoc().documentElement.lang = 'ca';
                    }
                });
            }
        });
        setTimeout(function() {
            document.querySelectorAll('textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]').forEach(function(textarea) {
                textarea.removeAttribute('disabled');
                textarea.removeAttribute('readonly');
            });
            tinymce.editors.forEach(function(editor) {
                if (editor.mode !== 'design') editor.setMode('design');
            });
        }, 500);
    }
});
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