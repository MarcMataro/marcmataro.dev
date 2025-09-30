/**
 * Gestió d'Idiomes - JavaScript per la interfície de gestió d'idiomes del blog
 * 
 * Funcionalitats:
 * - Llistar idiomes amb drag & drop per reordenar
 * - Crear, editar i eliminar idiomes
 * - Canviar estat (actiu/inactiu)
 * - Validació de formularis
 */

class GestioIdiomes {
    constructor() {
        this.apiUrl = 'api/idiomes.php';
        this.idiomes = [];
        this.init();
    }
    
    init() {
        this.carregarIdiomes();
        this.configurarEventListeners();
    }
    
    /**
     * Carregar tots els idiomes des de l'API
     */
    async carregarIdiomes() {
        try {
            const response = await fetch(this.apiUrl);
            const data = await response.json();
            
            if (data.success) {
                this.idiomes = data.data;
                this.renderitzarIdiomes();
            } else {
                this.mostrarError('Error carregant idiomes');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de connexió');
        }
    }
    
    /**
     * Renderitzar la llista d'idiomes
     */
    renderitzarIdiomes() {
        const container = document.getElementById('idiomes-list');
        if (!container) return;
        
        container.innerHTML = this.idiomes.map(idioma => `
            <div class="idioma-item" data-id="${idioma.id}" draggable="true">
                <div class="idioma-info">
                    <div class="idioma-header">
                        <img src="${idioma.bandera_url || '/img/default-flag.png'}" 
                             alt="${idioma.nom}" class="bandera">
                        <div class="idioma-noms">
                            <strong>${idioma.nom}</strong>
                            <small>(${idioma.nom_natiu})</small>
                        </div>
                        <span class="idioma-codi">${idioma.codi.toUpperCase()}</span>
                    </div>
                </div>
                <div class="idioma-accions">
                    <label class="switch">
                        <input type="checkbox" ${idioma.estat === 'actiu' ? 'checked' : ''} 
                               onchange="gestioIdiomes.canviarEstat(${idioma.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                    <button class="btn btn-sm btn-outline-primary" 
                            onclick="gestioIdiomes.editarIdioma(${idioma.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="gestioIdiomes.eliminarIdioma(${idioma.id})"
                            ${idioma.codi === 'ca' ? 'disabled title="No es pot eliminar l\'idioma per defecte"' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="drag-handle">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.configurarDragAndDrop();
    }
    
    /**
     * Configurar drag and drop per reordenar
     */
    configurarDragAndDrop() {
        const items = document.querySelectorAll('.idioma-item');
        let draggedItem = null;
        
        items.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggedItem = item;
                item.classList.add('dragging');
            });
            
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                draggedItem = null;
            });
            
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                const afterElement = this.getDragAfterElement(item.parentNode, e.clientY);
                const container = item.parentNode;
                
                if (afterElement == null) {
                    container.appendChild(draggedItem);
                } else {
                    container.insertBefore(draggedItem, afterElement);
                }
            });
        });
        
        // Guardar nou ordre quan acabi el drag
        document.getElementById('idiomes-list').addEventListener('dragend', () => {
            this.guardarNouOrdre();
        });
    }
    
    /**
     * Obtenir element després del qual inserir
     */
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.idioma-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    /**
     * Guardar nou ordre dels idiomes
     */
    async guardarNouOrdre() {
        const items = document.querySelectorAll('.idioma-item');
        const ordres = {};
        
        items.forEach((item, index) => {
            const id = item.dataset.id;
            ordres[id] = index + 1;
        });
        
        try {
            const response = await fetch(`${this.apiUrl}?action=reorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ordres })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarExits('Ordre actualitzat correctament');
            } else {
                this.mostrarError('Error guardant l\'ordre');
                this.carregarIdiomes(); // Recarregar per restaurar ordre original
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de connexió');
            this.carregarIdiomes();
        }
    }
    
    /**
     * Canviar estat d'un idioma
     */
    async canviarEstat(id, actiu) {
        const estat = actiu ? 'actiu' : 'inactiu';
        
        try {
            const response = await fetch(`${this.apiUrl}?id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ estat })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualitzar idioma local
                const idioma = this.idiomes.find(i => i.id == id);
                if (idioma) {
                    idioma.estat = estat;
                }
                this.mostrarExits(`Idioma ${actiu ? 'activat' : 'desactivat'} correctament`);
            } else {
                this.mostrarError('Error canviant l\'estat');
                this.carregarIdiomes(); // Recarregar per restaurar estat original
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de connexió');
            this.carregarIdiomes();
        }
    }
    
    /**
     * Mostrar formulari per crear nou idioma
     */
    nouIdioma() {
        const modal = document.getElementById('idioma-modal');
        const form = document.getElementById('idioma-form');
        
        // Netejar formulari
        form.reset();
        document.getElementById('idioma-id').value = '';
        document.getElementById('modal-title').textContent = 'Nou Idioma';
        
        // Mostrar modal
        modal.style.display = 'block';
    }
    
    /**
     * Editar idioma existent
     */
    editarIdioma(id) {
        const idioma = this.idiomes.find(i => i.id == id);
        if (!idioma) return;
        
        const modal = document.getElementById('idioma-modal');
        const form = document.getElementById('idioma-form');
        
        // Omplir formulari
        document.getElementById('idioma-id').value = idioma.id;
        document.getElementById('idioma-codi').value = idioma.codi;
        document.getElementById('idioma-nom').value = idioma.nom;
        document.getElementById('idioma-nom-natiu').value = idioma.nom_natiu;
        document.getElementById('idioma-bandera').value = idioma.bandera_url || '';
        document.getElementById('modal-title').textContent = 'Editar Idioma';
        
        // Deshabilitar codi si és idioma per defecte
        document.getElementById('idioma-codi').disabled = (idioma.codi === 'ca');
        
        // Mostrar modal
        modal.style.display = 'block';
    }
    
    /**
     * Guardar idioma (crear o actualitzar)
     */
    async guardarIdioma(event) {
        event.preventDefault();
        
        const form = document.getElementById('idioma-form');
        const formData = new FormData(form);
        const id = formData.get('idioma-id');
        
        const dades = {
            codi: formData.get('codi'),
            nom: formData.get('nom'),
            nom_natiu: formData.get('nom_natiu'),
            bandera_url: formData.get('bandera_url'),
            estat: 'actiu'
        };
        
        try {
            const url = id ? `${this.apiUrl}?id=${id}` : this.apiUrl;
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dades)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.tancarModal();
                this.carregarIdiomes();
                this.mostrarExits(id ? 'Idioma actualitzat correctament' : 'Idioma creat correctament');
            } else {
                this.mostrarErrors(data.errors || ['Error guardant l\'idioma']);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de connexió');
        }
    }
    
    /**
     * Eliminar idioma
     */
    async eliminarIdioma(id) {
        const idioma = this.idiomes.find(i => i.id == id);
        if (!idioma) return;
        
        if (idioma.codi === 'ca') {
            this.mostrarError('No es pot eliminar l\'idioma per defecte');
            return;
        }
        
        if (!confirm(`Estàs segur que vols eliminar l'idioma "${idioma.nom}"?`)) {
            return;
        }
        
        try {
            const response = await fetch(`${this.apiUrl}?id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.carregarIdiomes();
                this.mostrarExits('Idioma eliminat correctament');
            } else {
                this.mostrarError('Error eliminant l\'idioma');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de connexió');
        }
    }
    
    /**
     * Configurar event listeners
     */
    configurarEventListeners() {
        // Modal
        document.getElementById('tancar-modal').addEventListener('click', () => this.tancarModal());
        document.getElementById('idioma-form').addEventListener('submit', (e) => this.guardarIdioma(e));
        
        // Tancar modal clicant fora
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('idioma-modal');
            if (e.target === modal) {
                this.tancarModal();
            }
        });
    }
    
    /**
     * Tancar modal
     */
    tancarModal() {
        document.getElementById('idioma-modal').style.display = 'none';
        document.getElementById('idioma-codi').disabled = false;
    }
    
    /**
     * Mostrar missatge d'èxit
     */
    mostrarExits(missatge) {
        // Implementar segons el sistema de notificacions del blog
        console.log('Èxit:', missatge);
        // Pots utilitzar el sistema de notificacions existent del blog
    }
    
    /**
     * Mostrar error
     */
    mostrarError(missatge) {
        console.error('Error:', missatge);
        // Implementar segons el sistema de notificacions del blog
    }
    
    /**
     * Mostrar múltiples errors
     */
    mostrarErrors(errors) {
        errors.forEach(error => this.mostrarError(error));
    }
}

// Inicialitzar quan es carregui la pàgina
let gestioIdiomes;
document.addEventListener('DOMContentLoaded', () => {
    gestioIdiomes = new GestioIdiomes();
});