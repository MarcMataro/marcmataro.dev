# 🧪 Exercicis Pràctics: API REST i AJAX

## 📋 Índex d'Exercicis

1. [Exercicis Bàsics - Primers Passos](#exercicis-bàsics)
2. [Exercicis Intermedis - Funcionalitats Completes](#exercicis-intermedis)
3. [Exercicis Avançats - Optimitzacions](#exercicis-avançats)
4. [Projectes Mini - Aplicacions Completes](#projectes-mini)
5. [Debugging i Resolució de Problemes](#debugging)
6. [Tests i Validacions](#tests-i-validacions)

---

## 🎯 Exercicis Bàsics

### Exercici 1: Primera Petició AJAX

**Objectiu**: Fer la teva primera petició AJAX per obtenir dades.

#### **Pas 1: Crear HTML**
Crea un fitxer `test-ajax.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX</title>
    <style>
        .container { max-width: 800px; margin: 50px auto; padding: 20px; }
        .result { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test AJAX Bàsic</h1>
        
        <button onclick="obtenirEntrades()">Obtenir Entrades</button>
        <button onclick="obtenirCategories()">Obtenir Categories</button>
        
        <div id="resultats" class="result">
            <p>Clica un botó per veure els resultats...</p>
        </div>
    </div>

    <script>
        // El teu codi JavaScript anirà aquí
        async function obtenirEntrades() {
            try {
                // TODO: Implementar petició AJAX
                document.getElementById('resultats').innerHTML = 
                    '<p>🔄 Carregant entrades...</p>';
                    
                // PISTA: Usa fetch('/api/blog/entrades')
                
            } catch (error) {
                document.getElementById('resultats').innerHTML = 
                    '<p>❌ Error: ' + error.message + '</p>';
            }
        }
        
        async function obtenirCategories() {
            // TODO: Implementar aquesta funció
        }
    </script>
</body>
</html>
```

#### **Solució Completa**

```javascript
async function obtenirEntrades() {
    try {
        document.getElementById('resultats').innerHTML = '<p>🔄 Carregant entrades...</p>';
        
        // Fer petició AJAX
        const response = await fetch('/api/blog/entrades');
        
        // Verificar si la resposta és correcta
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        // Convertir a JSON
        const entrades = await response.json();
        
        // Mostrar resultats
        let html = '<h3>📝 Entrades del Blog:</h3>';
        
        if (entrades.length === 0) {
            html += '<p>No hi ha entrades</p>';
        } else {
            html += '<ul>';
            entrades.forEach(entrada => {
                html += `<li><strong>${entrada.titol}</strong> - ${entrada.estat}</li>`;
            });
            html += '</ul>';
        }
        
        document.getElementById('resultats').innerHTML = html;
        
    } catch (error) {
        document.getElementById('resultats').innerHTML = 
            '<p>❌ Error: ' + error.message + '</p>';
    }
}

async function obtenirCategories() {
    try {
        document.getElementById('resultats').innerHTML = '<p>🔄 Carregant categories...</p>';
        
        const response = await fetch('/api/blog/categories');
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const categories = await response.json();
        
        let html = '<h3>📁 Categories:</h3>';
        
        if (categories.length === 0) {
            html += '<p>No hi ha categories</p>';
        } else {
            html += '<ul>';
            categories.forEach(categoria => {
                html += `<li><strong>${categoria.nom}</strong></li>`;
            });
            html += '</ul>';
        }
        
        document.getElementById('resultats').innerHTML = html;
        
    } catch (error) {
        document.getElementById('resultats').innerHTML = 
            '<p>❌ Error: ' + error.message + '</p>';
    }
}
```

### Exercici 2: Filtre Dinàmic

**Objectiu**: Crear un filtre que actualitzi els resultats automàticament.

#### **HTML Base**

```html
<div class="container">
    <h1>🔍 Filtre Dinàmic</h1>
    
    <div class="filtres">
        <label>Estat:</label>
        <select id="filtre-estat" onchange="aplicarFiltres()">
            <option value="">Tots</option>
            <option value="publicat">Publicats</option>
            <option value="esborrany">Esborranys</option>
            <option value="arxivat">Arxivats</option>
        </select>
        
        <label>Idioma:</label>
        <select id="filtre-idioma" onchange="aplicarFiltres()">
            <option value="">Tots</option>
            <option value="ca">Català</option>
            <option value="es">Espanyol</option>
            <option value="en">Anglès</option>
        </select>
    </div>
    
    <div id="resultats-filtre" class="result">
        <!-- Resultats aquí -->
    </div>
</div>
```

#### **JavaScript per Completar**

```javascript
async function aplicarFiltres() {
    try {
        // Mostrar loading
        document.getElementById('resultats-filtre').innerHTML = 
            '<p>🔄 Aplicant filtres...</p>';
        
        // Obtenir valors dels filtres
        const estat = document.getElementById('filtre-estat').value;
        const idioma = document.getElementById('filtre-idioma').value;
        
        // TODO: Construir URL amb paràmetres
        let url = '/api/blog/entrades';
        
        // PISTA: Usa URLSearchParams per construir la query string
        
        // TODO: Fer petició amb filtres
        
        // TODO: Processar i mostrar resultats
        
    } catch (error) {
        document.getElementById('resultats-filtre').innerHTML = 
            '<p>❌ Error aplicant filtres: ' + error.message + '</p>';
    }
}

// Carregar resultats inicials quan es carrega la pàgina
document.addEventListener('DOMContentLoaded', aplicarFiltres);
```

#### **Solució**

```javascript
async function aplicarFiltres() {
    try {
        document.getElementById('resultats-filtre').innerHTML = 
            '<p>🔄 Aplicant filtres...</p>';
        
        const estat = document.getElementById('filtre-estat').value;
        const idioma = document.getElementById('filtre-idioma').value;
        
        // Construir paràmetres
        const params = new URLSearchParams();
        if (estat) params.append('estat', estat);
        if (idioma) params.append('idioma', idioma);
        
        // Construir URL
        let url = '/api/blog/entrades';
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        // Fer petició
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const entrades = await response.json();
        
        // Mostrar resultats
        let html = `<h3>📊 Resultats (${entrades.length} entrades):</h3>`;
        
        if (entrades.length === 0) {
            html += '<p>No s\'han trobat entrades amb aquests filtres</p>';
        } else {
            html += '<table border="1" style="width: 100%; border-collapse: collapse;">';
            html += '<tr><th>Títol</th><th>Estat</th><th>Idioma</th><th>Data</th></tr>';
            
            entrades.forEach(entrada => {
                html += `
                    <tr>
                        <td>${entrada.titol}</td>
                        <td><span class="estat ${entrada.estat}">${entrada.estat}</span></td>
                        <td>${entrada.idioma}</td>
                        <td>${entrada.data_publicacio || 'N/A'}</td>
                    </tr>
                `;
            });
            
            html += '</table>';
        }
        
        document.getElementById('resultats-filtre').innerHTML = html;
        
    } catch (error) {
        document.getElementById('resultats-filtre').innerHTML = 
            '<p>❌ Error aplicant filtres: ' + error.message + '</p>';
    }
}

document.addEventListener('DOMContentLoaded', aplicarFiltres);
```

### Exercici 3: Crear Entrada Simple

**Objectiu**: Implementar un formulari per crear entrades via AJAX.

#### **HTML**

```html
<div class="container">
    <h1>✍️ Crear Entrada</h1>
    
    <form id="form-entrada" onsubmit="return false">
        <div>
            <label>Títol:</label>
            <input type="text" id="titol" required style="width: 100%; padding: 8px; margin: 5px 0;">
        </div>
        
        <div>
            <label>Contingut:</label>
            <textarea id="contingut" required style="width: 100%; height: 150px; padding: 8px; margin: 5px 0;"></textarea>
        </div>
        
        <div>
            <label>Estat:</label>
            <select id="estat" style="padding: 8px; margin: 5px 0;">
                <option value="esborrany">Esborrany</option>
                <option value="publicat">Publicat</option>
            </select>
        </div>
        
        <button type="button" onclick="crearEntrada()">💾 Guardar Entrada</button>
        <button type="button" onclick="netejarFormulari()">🗑️ Netejar</button>
    </form>
    
    <div id="missatge-entrada" class="result">
        <!-- Missatges aquí -->
    </div>
</div>
```

#### **JavaScript per Completar**

```javascript
async function crearEntrada() {
    try {
        // TODO: Validar formulari
        
        // TODO: Recollir dades del formulari
        
        // TODO: Fer petició POST amb les dades
        
        // TODO: Gestionar resposta i mostrar missatge
        
    } catch (error) {
        document.getElementById('missatge-entrada').innerHTML = 
            '<p>❌ Error creant entrada: ' + error.message + '</p>';
    }
}

function netejarFormulari() {
    // TODO: Netejar tots els camps del formulari
}
```

#### **Solució**

```javascript
async function crearEntrada() {
    try {
        // Mostrar loading
        document.getElementById('missatge-entrada').innerHTML = 
            '<p>🔄 Creant entrada...</p>';
        
        // Validar formulari
        const titol = document.getElementById('titol').value.trim();
        const contingut = document.getElementById('contingut').value.trim();
        const estat = document.getElementById('estat').value;
        
        if (!titol) {
            throw new Error('El títol és obligatori');
        }
        
        if (!contingut) {
            throw new Error('El contingut és obligatori');
        }
        
        // Preparar dades
        const dades = {
            titol: titol,
            contingut: contingut,
            estat: estat
        };
        
        // Fer petició POST
        const response = await fetch('/api/blog/entrades', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dades)
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Error HTTP: ${response.status}`);
        }
        
        const resultat = await response.json();
        
        if (resultat.success) {
            document.getElementById('missatge-entrada').innerHTML = 
                `<p>✅ Entrada creada correctament! ID: ${resultat.id}</p>`;
            
            // Netejar formulari
            netejarFormulari();
        } else {
            throw new Error(resultat.message || 'Error desconegut');
        }
        
    } catch (error) {
        document.getElementById('missatge-entrada').innerHTML = 
            '<p>❌ Error creant entrada: ' + error.message + '</p>';
    }
}

function netejarFormulari() {
    document.getElementById('titol').value = '';
    document.getElementById('contingut').value = '';
    document.getElementById('estat').value = 'esborrany';
    document.getElementById('missatge-entrada').innerHTML = '';
}
```

---

## 🎯 Exercicis Intermedis

### Exercici 4: Sistema de Comentaris

**Objectiu**: Implementar moderació de comentaris amb accions múltiples.

#### **Estructura HTML**

```html
<div class="container">
    <h1>💬 Moderació de Comentaris</h1>
    
    <div class="controls">
        <select id="filtre-estat-comentaris" onchange="carregarComentaris()">
            <option value="pendent">Pendents</option>
            <option value="publicat">Publicats</option>
            <option value="spam">Spam</option>
        </select>
        
        <button onclick="aprovarSeleccionats()">✅ Aprovar Seleccionats</button>
        <button onclick="marcarSpamSeleccionats()">⚠️ Marcar Spam</button>
    </div>
    
    <div id="llista-comentaris">
        <!-- Comentaris aquí -->
    </div>
</div>
```

#### **JavaScript Base**

```javascript
async function carregarComentaris() {
    try {
        const estat = document.getElementById('filtre-estat-comentaris').value;
        
        // TODO: Fer petició per obtenir comentaris amb filtre
        
        // TODO: Generar HTML per cada comentari amb checkbox
        
        // TODO: Actualitzar el DOM
        
    } catch (error) {
        console.error('Error carregant comentaris:', error);
    }
}

async function aprovarComentari(id) {
    try {
        // TODO: Fer petició PUT per aprovar comentari
        
        // TODO: Actualitzar llista
        
    } catch (error) {
        alert('Error aprovant comentari: ' + error.message);
    }
}

function aprovarSeleccionats() {
    // TODO: Obtenir IDs dels comentaris seleccionats
    // TODO: Aprovar cada un
}
```

#### **Solució Completa**

```javascript
async function carregarComentaris() {
    try {
        document.getElementById('llista-comentaris').innerHTML = 
            '<p>🔄 Carregant comentaris...</p>';
        
        const estat = document.getElementById('filtre-estat-comentaris').value;
        
        const response = await fetch(`/api/blog/comentaris?estat=${estat}`);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const comentaris = await response.json();
        
        if (comentaris.length === 0) {
            document.getElementById('llista-comentaris').innerHTML = 
                '<p>No hi ha comentaris amb aquest estat</p>';
            return;
        }
        
        let html = '';
        comentaris.forEach(comentari => {
            html += `
                <div class="comentari-item" style="border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <input type="checkbox" value="${comentari.id}" class="comentari-checkbox">
                            <strong>${comentari.nom_autor}</strong>
                            <span style="color: #666;"> - ${comentari.entrada_titol}</span>
                        </div>
                        <div>
                            <button onclick="aprovarComentari(${comentari.id})" style="margin: 0 5px;">✅</button>
                            <button onclick="marcarSpam(${comentari.id})" style="margin: 0 5px;">⚠️</button>
                            <button onclick="eliminarComentari(${comentari.id})" style="margin: 0 5px;">🗑️</button>
                        </div>
                    </div>
                    <div style="margin: 10px 0;">
                        ${comentari.contingut}
                    </div>
                    <div style="font-size: 0.9em; color: #888;">
                        ${formatarData(comentari.data_creacio)}
                    </div>
                </div>
            `;
        });
        
        document.getElementById('llista-comentaris').innerHTML = html;
        
    } catch (error) {
        document.getElementById('llista-comentaris').innerHTML = 
            '<p>❌ Error carregant comentaris: ' + error.message + '</p>';
    }
}

async function aprovarComentari(id) {
    try {
        const response = await fetch(`/api/blog/comentaris/${id}/aprovar`, {
            method: 'PUT'
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const resultat = await response.json();
        
        if (resultat.success) {
            alert('Comentari aprovat correctament');
            carregarComentaris(); // Recarregar llista
        } else {
            throw new Error(resultat.message);
        }
        
    } catch (error) {
        alert('Error aprovant comentari: ' + error.message);
    }
}

async function marcarSpam(id) {
    try {
        const response = await fetch(`/api/blog/comentaris/${id}/spam`, {
            method: 'PUT'
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const resultat = await response.json();
        
        if (resultat.success) {
            alert('Comentari marcat com spam');
            carregarComentaris();
        } else {
            throw new Error(resultat.message);
        }
        
    } catch (error) {
        alert('Error marcant com spam: ' + error.message);
    }
}

async function eliminarComentari(id) {
    if (!confirm('Estàs segur que vols eliminar aquest comentari?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/blog/comentaris/${id}/eliminar`, {
            method: 'PUT'
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const resultat = await response.json();
        
        if (resultat.success) {
            alert('Comentari eliminat');
            carregarComentaris();
        } else {
            throw new Error(resultat.message);
        }
        
    } catch (error) {
        alert('Error eliminant comentari: ' + error.message);
    }
}

function aprovarSeleccionats() {
    const checkboxes = document.querySelectorAll('.comentari-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Selecciona almenys un comentari');
        return;
    }
    
    if (!confirm(`Aprovar ${checkboxes.length} comentaris?`)) {
        return;
    }
    
    // Aprovar cada comentari seleccionat
    checkboxes.forEach(checkbox => {
        aprovarComentari(parseInt(checkbox.value));
    });
}

function marcarSpamSeleccionats() {
    const checkboxes = document.querySelectorAll('.comentari-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Selecciona almenys un comentari');
        return;
    }
    
    if (!confirm(`Marcar com spam ${checkboxes.length} comentaris?`)) {
        return;
    }
    
    checkboxes.forEach(checkbox => {
        marcarSpam(parseInt(checkbox.value));
    });
}

function formatarData(dataString) {
    const data = new Date(dataString);
    return data.toLocaleDateString('ca-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Carregar comentaris inicials
document.addEventListener('DOMContentLoaded', carregarComentaris);
```

### Exercici 5: Editor d'Entrades amb Autoguardat

**Objectiu**: Crear un editor que guardi automàticament els canvis.

#### **HTML**

```html
<div class="container">
    <h1>✏️ Editor d'Entrades</h1>
    
    <div class="editor-toolbar">
        <select id="entrada-selector" onchange="carregarEntrada()">
            <option value="">-- Selecciona entrada --</option>
        </select>
        <button onclick="novaEntrada()">📄 Nova</button>
        <button onclick="guardarManual()">💾 Guardar</button>
        <span id="estat-guardat">📝 No guardat</span>
    </div>
    
    <div class="editor-form">
        <input type="hidden" id="entrada-id">
        
        <div>
            <label>Títol:</label>
            <input type="text" id="editor-titol" oninput="marcarCanvisPendents()" style="width: 100%; padding: 8px;">
        </div>
        
        <div>
            <label>Contingut:</label>
            <textarea id="editor-contingut" oninput="marcarCanvisPendents()" style="width: 100%; height: 300px; padding: 8px;"></textarea>
        </div>
        
        <div>
            <label>Estat:</label>
            <select id="editor-estat" onchange="marcarCanvisPendents()">
                <option value="esborrany">Esborrany</option>
                <option value="publicat">Publicat</option>
                <option value="arxivat">Arxivat</option>
            </select>
        </div>
    </div>
</div>
```

#### **JavaScript per Implementar**

```javascript
let autoguardatInterval;
let canvisPendents = false;

async function carregarLlistaEntrades() {
    try {
        // TODO: Carregar entrades i omplir el selector
    } catch (error) {
        console.error('Error carregant llista:', error);
    }
}

async function carregarEntrada() {
    try {
        const entradaId = document.getElementById('entrada-selector').value;
        
        if (!entradaId) {
            netejarEditor();
            return;
        }
        
        // TODO: Carregar entrada específica i omplir formulari
        
    } catch (error) {
        alert('Error carregant entrada: ' + error.message);
    }
}

function marcarCanvisPendents() {
    canvisPendents = true;
    document.getElementById('estat-guardat').textContent = '📝 Canvis pendents';
    document.getElementById('estat-guardat').style.color = 'orange';
}

async function autoguardar() {
    if (!canvisPendents) return;
    
    try {
        // TODO: Implementar autoguardat
        
    } catch (error) {
        console.error('Error en autoguardat:', error);
    }
}

// Iniciar autoguardat cada 30 segons
document.addEventListener('DOMContentLoaded', function() {
    carregarLlistaEntrades();
    
    autoguardatInterval = setInterval(autoguardar, 30000); // 30 segons
});
```

#### **Solució**

```javascript
let autoguardatInterval;
let canvisPendents = false;

async function carregarLlistaEntrades() {
    try {
        const response = await fetch('/api/blog/entrades');
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const entrades = await response.json();
        
        const selector = document.getElementById('entrada-selector');
        selector.innerHTML = '<option value="">-- Selecciona entrada --</option>';
        
        entrades.forEach(entrada => {
            const option = document.createElement('option');
            option.value = entrada.id;
            option.textContent = entrada.titol;
            selector.appendChild(option);
        });
        
    } catch (error) {
        console.error('Error carregant llista:', error);
        alert('Error carregant llista d\'entrades');
    }
}

async function carregarEntrada() {
    try {
        const entradaId = document.getElementById('entrada-selector').value;
        
        if (!entradaId) {
            netejarEditor();
            return;
        }
        
        const response = await fetch(`/api/blog/entrades/${entradaId}`);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const entrada = await response.json();
        
        // Omplir formulari
        document.getElementById('entrada-id').value = entrada.id;
        document.getElementById('editor-titol').value = entrada.titol;
        document.getElementById('editor-contingut').value = entrada.contingut;
        document.getElementById('editor-estat').value = entrada.estat;
        
        // Marcar com guardat
        canvisPendents = false;
        document.getElementById('estat-guardat').textContent = '✅ Guardat';
        document.getElementById('estat-guardat').style.color = 'green';
        
    } catch (error) {
        alert('Error carregant entrada: ' + error.message);
    }
}

function netejarEditor() {
    document.getElementById('entrada-id').value = '';
    document.getElementById('editor-titol').value = '';
    document.getElementById('editor-contingut').value = '';
    document.getElementById('editor-estat').value = 'esborrany';
    
    canvisPendents = false;
    document.getElementById('estat-guardat').textContent = '';
}

function novaEntrada() {
    document.getElementById('entrada-selector').value = '';
    netejarEditor();
    document.getElementById('estat-guardat').textContent = '📄 Nova entrada';
    document.getElementById('estat-guardat').style.color = 'blue';
}

function marcarCanvisPendents() {
    canvisPendents = true;
    document.getElementById('estat-guardat').textContent = '📝 Canvis pendents';
    document.getElementById('estat-guardat').style.color = 'orange';
}

async function guardarManual() {
    await guardarEntrada();
}

async function guardarEntrada() {
    try {
        const entradaId = document.getElementById('entrada-id').value;
        const titol = document.getElementById('editor-titol').value.trim();
        const contingut = document.getElementById('editor-contingut').value.trim();
        const estat = document.getElementById('editor-estat').value;
        
        if (!titol || !contingut) {
            alert('Títol i contingut són obligatoris');
            return false;
        }
        
        const dades = {
            titol: titol,
            contingut: contingut,
            estat: estat
        };
        
        let response;
        
        if (entradaId) {
            // Actualitzar entrada existent
            response = await fetch(`/api/blog/entrades/${entradaId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dades)
            });
        } else {
            // Crear nova entrada
            response = await fetch('/api/blog/entrades', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dades)
            });
        }
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const resultat = await response.json();
        
        if (resultat.success) {
            // Si era nova entrada, guardar l'ID
            if (!entradaId && resultat.id) {
                document.getElementById('entrada-id').value = resultat.id;
                carregarLlistaEntrades(); // Actualitzar selector
            }
            
            canvisPendents = false;
            document.getElementById('estat-guardat').textContent = '✅ Guardat';
            document.getElementById('estat-guardat').style.color = 'green';
            
            return true;
        } else {
            throw new Error(resultat.message);
        }
        
    } catch (error) {
        document.getElementById('estat-guardat').textContent = '❌ Error guardant';
        document.getElementById('estat-guardat').style.color = 'red';
        console.error('Error guardant:', error);
        return false;
    }
}

async function autoguardar() {
    if (!canvisPendents) return;
    
    const titol = document.getElementById('editor-titol').value.trim();
    const contingut = document.getElementById('editor-contingut').value.trim();
    
    if (!titol || !contingut) return; // No autoguardar si falten dades essencials
    
    document.getElementById('estat-guardat').textContent = '🔄 Autoguardant...';
    document.getElementById('estat-guardat').style.color = 'blue';
    
    const guardat = await guardarEntrada();
    
    if (guardat) {
        console.log('Autoguardat completat');
    }
}

// Advertir abans de sortir si hi ha canvis pendents
window.addEventListener('beforeunload', function(e) {
    if (canvisPendents) {
        e.preventDefault();
        e.returnValue = '';
        return 'Tens canvis sense guardar. Vols sortir?';
    }
});

// Inicialització
document.addEventListener('DOMContentLoaded', function() {
    carregarLlistaEntrades();
    
    // Autoguardar cada 30 segons
    autoguardatInterval = setInterval(autoguardar, 30000);
});
```

---

## 🎯 Exercicis Avançats

### Exercici 6: Dashboard amb Estadístiques en Temps Real

**Objectiu**: Crear un dashboard que actualitzi estadístiques automàticament.

#### **HTML**

```html
<div class="container">
    <h1>📊 Dashboard en Temps Real</h1>
    
    <div class="controls">
        <button onclick="pausarActualitzacions()" id="btn-pausar">⏸️ Pausar</button>
        <button onclick="forcarActualitzacio()">🔄 Actualitzar</button>
        <span>Última actualització: <span id="ultima-actualitzacio">--</span></span>
    </div>
    
    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <!-- Estadístiques aquí -->
    </div>
    
    <div class="grafica-container">
        <h3>📈 Activitat Recent</h3>
        <canvas id="grafica-activitat" width="800" height="200"></canvas>
    </div>
</div>
```

#### **JavaScript**

```javascript
let actualitzacioInterval;
let actualitzacionsPausades = false;

class Dashboard {
    constructor() {
        this.ultimesEstadistiques = null;
        this.historialActivitat = [];
    }
    
    async carregarEstadistiques() {
        try {
            const response = await fetch('/api/blog/estadistiques');
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const estadistiques = await response.json();
            this.ultimesEstadistiques = estadistiques;
            
            this.actualitzarTauler(estadistiques);
            this.actualitzarGrafica();
            this.actualitzarTimestamp();
            
        } catch (error) {
            console.error('Error carregant estadístiques:', error);
            this.mostrarError('Error carregant estadístiques: ' + error.message);
        }
    }
    
    actualitzarTauler(estadistiques) {
        const grid = document.querySelector('.dashboard-grid');
        
        grid.innerHTML = `
            <div class="stat-card" style="background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #1976d2;">📝 Entrades</h3>
                <div style="font-size: 2em; font-weight: bold; margin: 10px 0;">${estadistiques.entrades?.total || 0}</div>
                <div style="color: #666;">
                    <div>${estadistiques.entrades?.publicades || 0} publicades</div>
                    <div>${estadistiques.entrades?.esborranys || 0} esborranys</div>
                </div>
            </div>
            
            <div class="stat-card" style="background: #fff3e0; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #f57c00;">💬 Comentaris</h3>
                <div style="font-size: 2em; font-weight: bold; margin: 10px 0;">${estadistiques.comentaris?.total || 0}</div>
                <div style="color: #666;">
                    <div>${estadistiques.comentaris?.pendents || 0} pendents</div>
                    <div>${estadistiques.comentaris?.spam || 0} spam</div>
                </div>
            </div>
            
            <div class="stat-card" style="background: #e8f5e8; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #388e3c;">👥 Usuaris</h3>
                <div style="font-size: 2em; font-weight: bold; margin: 10px 0;">${estadistiques.usuaris?.total || 0}</div>
                <div style="color: #666;">
                    <div>${estadistiques.usuaris?.actius || 0} actius</div>
                    <div>${estadistiques.usuaris?.nous_setmana || 0} nous aquesta setmana</div>
                </div>
            </div>
            
            <div class="stat-card" style="background: #fce4ec; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #c2185b;">📊 Activitat</h3>
                <div style="font-size: 2em; font-weight: bold; margin: 10px 0;">${estadistiques.activitat?.visites_avui || 0}</div>
                <div style="color: #666;">
                    <div>Visites avui</div>
                    <div>${estadistiques.activitat?.visites_setmana || 0} aquesta setmana</div>
                </div>
            </div>
        `;
    }
    
    actualitzarGrafica() {
        // Afegir punt a l'historial
        const ara = new Date();
        const punt = {
            temps: ara.toLocaleTimeString(),
            visites: this.ultimesEstadistiques?.activitat?.visites_avui || 0,
            comentaris: this.ultimesEstadistiques?.comentaris?.total || 0
        };
        
        this.historialActivitat.push(punt);
        
        // Mantenir només els últims 20 punts
        if (this.historialActivitat.length > 20) {
            this.historialActivitat.shift();
        }
        
        this.dibuixarGrafica();
    }
    
    dibuixarGrafica() {
        const canvas = document.getElementById('grafica-activitat');
        const ctx = canvas.getContext('2d');
        
        // Netejar canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (this.historialActivitat.length < 2) return;
        
        // Configuració
        const padding = 40;
        const width = canvas.width - 2 * padding;
        const height = canvas.height - 2 * padding;
        
        // Trobar valors màxims
        const maxVisites = Math.max(...this.historialActivitat.map(p => p.visites));
        const maxComentaris = Math.max(...this.historialActivitat.map(p => p.comentaris));
        
        // Dibuixar eixos
        ctx.strokeStyle = '#ddd';
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, canvas.height - padding);
        ctx.lineTo(canvas.width - padding, canvas.height - padding);
        ctx.stroke();
        
        // Dibuixar línia de visites
        ctx.strokeStyle = '#1976d2';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        this.historialActivitat.forEach((punt, index) => {
            const x = padding + (index / (this.historialActivitat.length - 1)) * width;
            const y = canvas.height - padding - (punt.visites / maxVisites) * height;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
        
        // Dibuixar línia de comentaris
        ctx.strokeStyle = '#f57c00';
        ctx.beginPath();
        
        this.historialActivitat.forEach((punt, index) => {
            const x = padding + (index / (this.historialActivitat.length - 1)) * width;
            const y = canvas.height - padding - (punt.comentaris / maxComentaris) * height;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
        
        // Llegenda
        ctx.fillStyle = '#1976d2';
        ctx.fillRect(10, 10, 20, 10);
        ctx.fillStyle = '#000';
        ctx.fillText('Visites', 35, 20);
        
        ctx.fillStyle = '#f57c00';
        ctx.fillRect(100, 10, 20, 10);
        ctx.fillStyle = '#000';
        ctx.fillText('Comentaris', 125, 20);
    }
    
    actualitzarTimestamp() {
        const ara = new Date();
        document.getElementById('ultima-actualitzacio').textContent = 
            ara.toLocaleTimeString();
    }
    
    mostrarError(missatge) {
        const grid = document.querySelector('.dashboard-grid');
        grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: red;">${missatge}</div>`;
    }
}

const dashboard = new Dashboard();

function pausarActualitzacions() {
    const btn = document.getElementById('btn-pausar');
    
    if (actualitzacionsPausades) {
        // Reprendre
        actualitzacionsPausades = false;
        btn.textContent = '⏸️ Pausar';
        actualitzacioInterval = setInterval(() => {
            if (!actualitzacionsPausades) {
                dashboard.carregarEstadistiques();
            }
        }, 10000); // Cada 10 segons
    } else {
        // Pausar
        actualitzacionsPausades = true;
        btn.textContent = '▶️ Reprendre';
        if (actualitzacioInterval) {
            clearInterval(actualitzacioInterval);
        }
    }
}

function forcarActualitzacio() {
    dashboard.carregarEstadistiques();
}

// Inicialització
document.addEventListener('DOMContentLoaded', function() {
    dashboard.carregarEstadistiques();
    
    // Actualitzar cada 10 segons
    actualitzacioInterval = setInterval(() => {
        if (!actualitzacionsPausades) {
            dashboard.carregarEstadistiques();
        }
    }, 10000);
});
```

---

## 🧪 Projectes Mini

### Projecte 1: Sistema de Tags Dinàmic

**Objectiu**: Crear un sistema de tags amb autocomplete i gestió visual.

#### **Funcionalitats**
- Input amb autocomplete
- Afegir/eliminar tags visualment
- Cerca per tags
- Popularitat de tags

#### **Estructura Base**

```html
<div class="container">
    <h1>🏷️ Gestió de Tags</h1>
    
    <div class="tag-input-section">
        <label>Afegir tag:</label>
        <input type="text" id="tag-input" placeholder="Escriu un tag..." oninput="mostrarSuggestions()">
        <div id="suggestions" class="suggestions"></div>
        <button onclick="afegirTag()">➕ Afegir</button>
    </div>
    
    <div class="tags-actuals">
        <h3>Tags seleccionats:</h3>
        <div id="tags-seleccionats" class="tag-cloud"></div>
    </div>
    
    <div class="tots-tags">
        <h3>Tots els tags:</h3>
        <div id="tots-tags" class="tag-cloud"></div>
    </div>
</div>

<style>
.tag-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 10px 0;
}

.tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 5px 10px;
    border-radius: 20px;
    border: 1px solid #1976d2;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.tag.seleccionat {
    background: #1976d2;
    color: white;
}

.tag .remove {
    cursor: pointer;
    font-weight: bold;
}

.suggestions {
    border: 1px solid #ddd;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    position: absolute;
    z-index: 100;
    display: none;
}

.suggestion {
    padding: 10px;
    cursor: pointer;
}

.suggestion:hover {
    background: #f0f0f0;
}
</style>
```

### Projecte 2: Sistema de Notificacions Push

**Objectiu**: Crear un sistema de notificacions en temps real.

#### **Funcionalitats**
- Notificacions toast
- Cua de notificacions
- Diferents tipus (info, success, warning, error)
- Autoclose configurable
- Accions dins les notificacions

### Projecte 3: Cache Intel·ligent

**Objectiu**: Implementar un sistema de cache avançat per a l'API.

#### **Funcionalitats**
- Cache amb TTL (Time To Live)
- Invalidació selectiva
- Compressió de dades
- Fallback automàtic

---

## 🐛 Debugging i Resolució de Problemes

### Errors Comuns i Solucions

#### **Error 1: "Failed to fetch"**

```javascript
// Problema habitual
fetch('/api/blog/entrades')
    .then(response => response.json()) // ❌ No verifica si response.ok
    .then(data => console.log(data));

// Solució correcta
async function obtenirEntrades() {
    try {
        const response = await fetch('/api/blog/entrades');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        if (error.name === 'TypeError') {
            // Problema de xarxa
            console.error('Error de xarxa:', error.message);
        } else {
            // Error HTTP
            console.error('Error HTTP:', error.message);
        }
        throw error;
    }
}
```

#### **Error 2: CORS Problems**

```php
// ❌ API sense headers CORS
<?php
echo json_encode($data);
?>

// ✅ API amb headers CORS correctes
<?php
// Headers CORS al principi de l'API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gestionar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode($data);
?>
```

#### **Error 3: JSON Malformat**

```php
// ❌ Output que trenca el JSON
<?php
echo "Debug: starting process\n"; // Això trenca el JSON
echo json_encode($data);
?>

// ✅ JSON net
<?php
// Tot el debug va a logs
error_log("Debug: starting process");

// Només JSON a l'output
header('Content-Type: application/json');
echo json_encode($data);
?>
```

### Eines de Debug

#### **Console Tricks**

```javascript
// Grup de logs per organitzar
console.group('API Call: obtenirEntrades');
console.log('Params:', filtres);
console.log('URL:', url);
console.time('Request time');

try {
    const response = await fetch(url);
    console.log('Response:', response);
    
    const data = await response.json();
    console.log('Data:', data);
    
    return data;
} finally {
    console.timeEnd('Request time');
    console.groupEnd();
}
```

#### **Network Tab Analysis**

```
THINGS TO CHECK:
1. Request URL - És correcta?
2. Request Method - GET/POST/PUT/DELETE correcte?
3. Request Headers - Content-Type correcte?
4. Request Payload - Les dades s'envien bé?
5. Response Status - 200 OK o error?
6. Response Headers - Content-Type: application/json?
7. Response Body - JSON vàlid?
```

---

## ✅ Tests i Validacions

### Test Suite Bàsic

#### **HTML de Testing**

```html
<!DOCTYPE html>
<html>
<head>
    <title>🧪 Test Suite API</title>
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background: #d4edda; color: #155724; }
        .test-error { background: #f8d7da; color: #721c24; }
        .test-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Suite - API REST</h1>
        
        <button onclick="executarTots()">▶️ Executar Tots els Tests</button>
        <button onclick="netejarResultats()">🗑️ Netejar</button>
        
        <div id="resultats-tests"></div>
    </div>

    <script>
        class TestSuite {
            constructor() {
                this.tests = [];
                this.resultats = [];
            }
            
            afegirTest(nom, testFunction) {
                this.tests.push({ nom, test: testFunction });
            }
            
            async executar() {
                this.resultats = [];
                const container = document.getElementById('resultats-tests');
                container.innerHTML = '';
                
                for (const { nom, test } of this.tests) {
                    try {
                        this.mostrarTestPendent(nom);
                        
                        const resultat = await test();
                        
                        this.resultats.push({ nom, status: 'success', resultat });
                        this.mostrarTestSuccess(nom, resultat);
                        
                    } catch (error) {
                        this.resultats.push({ nom, status: 'error', error: error.message });
                        this.mostrarTestError(nom, error.message);
                    }
                }
                
                this.mostrarResum();
            }
            
            mostrarTestPendent(nom) {
                const div = document.createElement('div');
                div.className = 'test-result test-pending';
                div.id = `test-${nom.replace(/\s+/g, '-')}`;
                div.innerHTML = `🔄 <strong>${nom}</strong> - Executant...`;
                document.getElementById('resultats-tests').appendChild(div);
            }
            
            mostrarTestSuccess(nom, resultat) {
                const div = document.getElementById(`test-${nom.replace(/\s+/g, '-')}`);
                div.className = 'test-result test-success';
                div.innerHTML = `✅ <strong>${nom}</strong> - Success<br><small>${JSON.stringify(resultat, null, 2)}</small>`;
            }
            
            mostrarTestError(nom, error) {
                const div = document.getElementById(`test-${nom.replace(/\s+/g, '-')}`);
                div.className = 'test-result test-error';
                div.innerHTML = `❌ <strong>${nom}</strong> - Error<br><small>${error}</small>`;
            }
            
            mostrarResum() {
                const total = this.resultats.length;
                const success = this.resultats.filter(r => r.status === 'success').length;
                const errors = total - success;
                
                const div = document.createElement('div');
                div.className = 'test-result';
                div.style.background = errors === 0 ? '#d4edda' : '#f8d7da';
                div.innerHTML = `
                    <h3>📊 Resum dels Tests</h3>
                    <p>Total: ${total} | Success: ${success} | Errors: ${errors}</p>
                `;
                document.getElementById('resultats-tests').appendChild(div);
            }
        }
        
        // Crear suite de tests
        const suite = new TestSuite();
        
        // Test 1: Obtenir entrades
        suite.afegirTest('Obtenir Entrades', async () => {
            const response = await fetch('/api/blog/entrades');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!Array.isArray(data)) {
                throw new Error('La resposta no és un array');
            }
            
            return `${data.length} entrades obtingudes`;
        });
        
        // Test 2: Crear entrada
        suite.afegirTest('Crear Entrada', async () => {
            const dades = {
                titol: 'Test Entrada ' + Date.now(),
                contingut: 'Contingut de test',
                estat: 'esborrany'
            };
            
            const response = await fetch('/api/blog/entrades', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dades)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const resultat = await response.json();
            
            if (!resultat.success || !resultat.id) {
                throw new Error('Resposta incorrecta: ' + JSON.stringify(resultat));
            }
            
            return `Entrada creada amb ID: ${resultat.id}`;
        });
        
        // Test 3: Filtrar entrades
        suite.afegirTest('Filtrar Entrades per Estat', async () => {
            const response = await fetch('/api/blog/entrades?estat=publicat');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            // Verificar que totes les entrades són publicades
            const noPublicades = data.filter(entrada => entrada.estat !== 'publicat');
            if (noPublicades.length > 0) {
                throw new Error(`Trobades ${noPublicades.length} entrades no publicades`);
            }
            
            return `${data.length} entrades publicades`;
        });
        
        // Test 4: Gestió d'errors
        suite.afegirTest('Gestió Error 404', async () => {
            const response = await fetch('/api/blog/entrades/999999');
            
            if (response.status !== 404) {
                throw new Error(`Esperava 404, rebut ${response.status}`);
            }
            
            return 'Error 404 gestionat correctament';
        });
        
        // Funcions globals
        async function executarTots() {
            await suite.executar();
        }
        
        function netejarResultats() {
            document.getElementById('resultats-tests').innerHTML = '';
        }
        
        // Executar automàticament en carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Test suite carregat. Clica "Executar Tots els Tests" per començar.');
        });
    </script>
</body>
</html>
```

### Validació de Dades

#### **Validador Frontend**

```javascript
class ValidadorEntrada {
    static validar(dades) {
        const errors = [];
        
        // Títol obligatori
        if (!dades.titol || dades.titol.trim().length === 0) {
            errors.push('El títol és obligatori');
        } else if (dades.titol.length > 200) {
            errors.push('El títol no pot superar els 200 caràcters');
        }
        
        // Contingut obligatori
        if (!dades.contingut || dades.contingut.trim().length === 0) {
            errors.push('El contingut és obligatori');
        } else if (dades.contingut.length < 10) {
            errors.push('El contingut ha de tenir almenys 10 caràcters');
        }
        
        // Estat vàlid
        const estatsValids = ['esborrany', 'publicat', 'arxivat'];
        if (!estatsValids.includes(dades.estat)) {
            errors.push('Estat no vàlid');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
}

// Ús
const dades = {
    titol: document.getElementById('titol').value,
    contingut: document.getElementById('contingut').value,
    estat: document.getElementById('estat').value
};

const validacio = ValidadorEntrada.validar(dades);

if (!validacio.valid) {
    alert('Errors de validació:\n' + validacio.errors.join('\n'));
    return;
}
```

---

## 🎓 Certificat de Completició

### Checklist d'Exercicis

```
□ Exercici 1: Primera Petició AJAX
□ Exercici 2: Filtre Dinàmic  
□ Exercici 3: Crear Entrada Simple
□ Exercici 4: Sistema de Comentaris
□ Exercici 5: Editor amb Autoguardat
□ Exercici 6: Dashboard en Temps Real

PROJECTES MINI:
□ Sistema de Tags Dinàmic
□ Notificacions Push
□ Cache Intel·ligent

DEBUGGING:
□ Identificar errors CORS
□ Resoldre problemes JSON
□ Usar Developer Tools efectivament

TESTING:
□ Crear tests automàtics
□ Validar dades frontend
□ Gestionar errors correctament
```

### Nivells de Competència

#### **🥉 Nivell Bàsic**
- Entens què és AJAX i API REST
- Pots fer peticions GET simples
- Saps gestionar errors bàsics

#### **🥈 Nivell Intermedi**
- Implementes CRUD complet
- Uses filtres i paràmetres
- Gestiones estat de l'aplicació

#### **🥇 Nivell Avançat**
- Optimitzes rendiment
- Implementes cache
- Crees sistemes en temps real

---

*Document d'Exercicis creat per Marc Mataró - Setembre 2025*
*Complementa les Guies d'API REST i AJAX*