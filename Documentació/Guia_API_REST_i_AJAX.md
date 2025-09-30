# 📖 Guia Completa: API REST i AJAX per a Principiants

## 📋 Índex
1. [Què és una API REST?](#què-és-una-api-rest)
2. [Què és AJAX?](#què-és-ajax)
3. [Per què els necessitem?](#per-què-els-necessitem)
4. [Com funciona en el nostre projecte](#com-funciona-en-el-nostre-projecte)
5. [Exemples pràctics pas a pas](#exemples-pràctics-pas-a-pas)
6. [Estructura de fitxers](#estructura-de-fitxers)
7. [Fluxos de treball complets](#fluxos-de-treball-complets)
8. [Glossari de termes](#glossari-de-termes)

---

## 🤔 Què és una API REST?

### **Analogia Simple: El Cambrer d'un Restaurant**

Imagina't que vas a un restaurant:
- **Tu** ets l'aplicació web (frontend)
- **La cuina** és la base de dades
- **El cambrer** és l'API REST

Tu no pots anar directament a la cuina a buscar el menjar. Has de parlar amb el cambrer:
1. **Demanes** el que vols (petició)
2. **El cambrer** va a la cuina (processament)
3. **El cambrer** et porta** el menjar (resposta)

### **Definició Tècnica**

**API** = Application Programming Interface (Interfície de Programació d'Aplicacions)
**REST** = Representational State Transfer (Transferència d'Estat Representacional)

És un conjunt de regles i protocols que permeten que diferents aplicacions es comuniquin entre elles.

### **Característiques de REST**

1. **Sense Estat (Stateless)**: Cada petició és independent
2. **URLs Predictibles**: Estructura clara i consistent
3. **Mètodes HTTP**: GET, POST, PUT, DELETE
4. **Format JSON**: Dades en format llegible

### **Exemple Visual de REST**

```
📱 FRONTEND                  🔗 API REST                  🗄️ BACKEND
┌─────────────┐             ┌─────────────┐             ┌─────────────┐
│  Botó       │   REQUEST   │   Endpoint  │   QUERY     │   Base de   │
│ "Carregar   │ ─────────►  │   /entrades │ ─────────►  │    Dades    │
│  Entrades"  │             │             │             │             │
│             │  RESPONSE   │   JSON      │   RESULTS   │             │
│  Taula      │ ◄───────────│   Dades     │ ◄───────────│             │
│  Actualitzada│            │             │             │             │
└─────────────┘             └─────────────┘             └─────────────┘
```

---

## ⚡ Què és AJAX?

### **Analogia Simple: El WhatsApp**

AJAX és com enviar un WhatsApp:
- **Sense AJAX**: És com enviar una carta. Has d'esperar dies per la resposta i mentre esperes no pots fer res més.
- **Amb AJAX**: És com WhatsApp. Envies el missatge, continues fent altres coses, i quan arriba la resposta, la veus immediatament.

### **Definició Tècnica**

**AJAX** = Asynchronous JavaScript and XML (JavaScript i XML Asíncron)

Permet enviar i rebre dades del servidor sense recarregar la pàgina completa.

### **Evolució d'AJAX**

#### **Abans (Pàgines Tradicionals)**
```
Usuari clica botó → Recarrega TOTA la pàgina → Mostra noves dades
```

#### **Ara (AJAX Modern)**
```
Usuari clica botó → Petició en segon pla → Actualitza NOMÉS la part necessària
```

### **Exemple Visual**

```
SENSE AJAX:
┌─────────────────────────────────────┐
│ 🔄 Recarregant tota la pàgina...    │
│                                     │
│ [████████████████████████████████]  │
│                                     │
│ ⏳ Usuari espera sense poder fer res │
└─────────────────────────────────────┘

AMB AJAX:
┌─────────────────────────────────────┐
│ ✅ Pàgina segueix funcionant         │
│                                     │
│ 📊 Només actualitza la taula: [██]  │
│                                     │
│ 👆 Usuari pot seguir interactuant   │
└─────────────────────────────────────┘
```

---

## 🎯 Per què els necessitem?

### **Problemes que Resolem**

#### **❌ Sense API REST i AJAX**

1. **Experiència d'Usuari Pobra**
   ```php
   // Cada filtro recarrega la pàgina completa
   if ($_GET['filtro'] == 'publicat') {
       // Recarregar tota la pàgina
       header('Location: blog.php?filtro=publicat');
   }
   ```

2. **Lentitud**
   - Recarregar HTML, CSS, JS cada vegada
   - Perdre scroll, formularis omplerts, etc.

3. **Complexitat**
   - Barrejar lògica de presentació amb dades
   - Codi difícil de mantenir

#### **✅ Amb API REST i AJAX**

1. **Experiència Fluida**
   ```javascript
   // Canvi instantani sense recarregar
   async function filtrarEntrades() {
       const entrades = await fetch('/api/blog/entrades?estat=publicat');
       actualitzarTaula(entrades);
   }
   ```

2. **Rapidesa**
   - Només es descarreguen les dades necessàries
   - La interfície es manté

3. **Organització**
   - Frontend i Backend separats
   - Codi modular i reutilitzable

### **Exemple Comparatiu Real**

**Escenari**: Filtrar entrades del blog per estat

#### **Mètode Tradicional**
```html
<!-- Cada canvi recarrega la pàgina -->
<form method="GET" action="blog.php">
    <select name="estat" onchange="this.form.submit()">
        <option value="tots">Tots</option>
        <option value="publicat">Publicats</option>
    </select>
</form>

<?php
// blog.php - Barreja HTML amb lògica
$estat = $_GET['estat'] ?? 'tots';
$entrades = obtenirEntrades($estat);
foreach ($entrades as $entrada) {
    echo "<tr>...</tr>";
}
?>
```

#### **Mètode Modern (API + AJAX)**
```html
<!-- No recarrega, canvi instantani -->
<select id="filtro-estat" onchange="filtrarEntrades()">
    <option value="tots">Tots</option>
    <option value="publicat">Publicats</option>
</select>
```

```javascript
// JavaScript separat
async function filtrarEntrades() {
    const estat = document.getElementById('filtro-estat').value;
    const entrades = await blogAPI.obtenirEntrades({ estat });
    actualitzarTaulaEntrades(entrades);
}
```

```php
// API separada - només dades
<?php
// /api/blog/entrades
$estat = $_GET['estat'] ?? 'tots';
$entrades = obtenirEntrades($estat);
echo json_encode($entrades);
?>
```

---

## 🏗️ Com Funciona en el Nostre Projecte

### **Arquitectura General**

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   HTML      │  │     CSS     │  │      JavaScript         │  │
│  │ (Estructura)│  │   (Estils)  │  │   (Interactivitat)      │  │
│  │             │  │             │  │                         │  │
│  │ blog.php    │  │blog-admin.css│  │   blog-admin.js        │  │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘  │
└─────────────────────────┬───────────────────────────────────────┘
                          │ AJAX (Fetch API)
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API REST                                   │
│             /api/blog/index.php                                 │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │   Endpoints:                                            │    │
│  │   GET    /entrades     → Llistar entrades             │    │
│  │   POST   /entrades     → Crear entrada                │    │
│  │   PUT    /entrades/123 → Actualitzar entrada          │    │
│  │   DELETE /entrades/123 → Eliminar entrada             │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────┬───────────────────────────────────────┘
                          │ Classes PHP
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                       BACKEND                                   │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   Classes   │  │  Connexió   │  │      Base de Dades      │  │
│  │    PHP      │  │     DB      │  │                         │  │
│  │             │  │             │  │   ┌─────┐ ┌─────────┐   │  │
│  │ Blog.php    │  │connexio.php │  │   │Users│ │Entrades │   │  │
│  │entrades.php │  │             │  │   └─────┘ └─────────┘   │  │
│  │comentaris..│  │             │  │   ┌─────────┐ ┌─────┐   │  │
│  └─────────────┘  └─────────────┘  │   │Categories│ │Tags │   │  │
│                                    │   └─────────┘ └─────┘   │  │
└─────────────────────────────────────────────────────────────────┘
```

### **Flux de Comunicació**

#### **1. Usuari Interactua**
```
Usuari clica "Filtrar per estat: Publicat"
         ↓
JavaScript detecta l'event
         ↓
Prepara la petició AJAX
```

#### **2. Petició AJAX**
```javascript
// blog-admin.js
async function filtrarEntrades() {
    // Preparar dades
    const filtres = {
        estat: document.getElementById('filtro-estat').value
    };
    
    // Enviar petició
    const response = await fetch('/api/blog/entrades?' + new URLSearchParams(filtres));
    const entrades = await response.json();
    
    // Actualitzar interfície
    actualitzarTaula(entrades);
}
```

#### **3. API Processa**
```php
// /api/blog/index.php
switch ($resource) {
    case 'entrades':
        if ($method === 'GET') {
            $filtres = [
                'estat' => $_GET['estat'] ?? null
            ];
            $entrades = $blog->llistarEntrades($filtres);
            echo json_encode($entrades);
        }
        break;
}
```

#### **4. Classes PHP Treballem**
```php
// EntradesBlog.php
public function llistarEntrades($filtres = []) {
    $sql = "SELECT * FROM entrades_blog WHERE 1=1";
    
    if ($filtres['estat']) {
        $sql .= " AND estat = :estat";
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($filtres);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

#### **5. Resposta i Actualització**
```
Base de dades retorna resultats
         ↓
Classe PHP processa i retorna array
         ↓
API converteix a JSON
         ↓
JavaScript rep les dades
         ↓
Actualitza només la taula (sense recarregar)
```

---

## 📝 Exemples Pràctics Pas a Pas

### **Exemple 1: Carregar Llista d'Entrades**

#### **Pas 1: HTML (Estructura)**
```html
<!-- blog.php -->
<div class="section-actions">
    <select id="filtro-estat-entrades" onchange="filtrarEntrades()">
        <option value="">Tots els estats</option>
        <option value="esborrany">Esborranys</option>
        <option value="publicat">Publicats</option>
    </select>
</div>

<table id="taula-entrades">
    <thead>
        <tr>
            <th>Títol</th>
            <th>Autor</th>
            <th>Estat</th>
        </tr>
    </thead>
    <tbody>
        <!-- Aquí es carregaran les dades via AJAX -->
    </tbody>
</table>
```

#### **Pas 2: JavaScript (Interactivitat)**
```javascript
// blog-admin.js

// Classe per gestionar l'API
class BlogAPI {
    constructor() {
        this.baseURL = window.location.origin + '/api/blog/';
    }
    
    async obtenirEntrades(filtres = {}) {
        // Construir URL amb paràmetres
        const url = this.baseURL + 'entrades?' + new URLSearchParams(filtres);
        
        // Fer petició
        const response = await fetch(url);
        
        // Verificar si la resposta és correcta
        if (!response.ok) {
            throw new Error('Error carregant entrades');
        }
        
        // Convertir resposta a JSON
        return await response.json();
    }
}

// Instància global de l'API
const blogAPI = new BlogAPI();

// Funció que s'executa quan canviem el filtre
async function filtrarEntrades() {
    try {
        // Mostrar indicador de càrrega
        document.querySelector('#taula-entrades tbody').innerHTML = 
            '<tr><td colspan="3">Carregant...</td></tr>';
        
        // Obtenir valor del filtre
        const estat = document.getElementById('filtro-estat-entrades').value;
        
        // Fer petició a l'API
        const entrades = await blogAPI.obtenirEntrades({ estat });
        
        // Actualitzar la taula
        actualitzarTaulaEntrades(entrades);
        
    } catch (error) {
        console.error('Error:', error);
        document.querySelector('#taula-entrades tbody').innerHTML = 
            '<tr><td colspan="3">Error carregant entrades</td></tr>';
    }
}

// Funció per actualitzar la taula
function actualitzarTaulaEntrades(entrades) {
    const tbody = document.querySelector('#taula-entrades tbody');
    
    if (entrades.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3">No hi ha entrades</td></tr>';
        return;
    }
    
    // Generar HTML per cada entrada
    const html = entrades.map(entrada => `
        <tr>
            <td>${entrada.titol}</td>
            <td>${entrada.autor_nom}</td>
            <td><span class="estat ${entrada.estat}">${entrada.estat}</span></td>
        </tr>
    `).join('');
    
    tbody.innerHTML = html;
}

// Carregar entrades quan es carrega la pàgina
document.addEventListener('DOMContentLoaded', function() {
    filtrarEntrades();
});
```

#### **Pas 3: API REST (Backend)**
```php
// /api/blog/index.php

<?php
// Headers per API REST
header('Content-Type: application/json');

// Obtenir el recurs sol·licitat
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

$resource = $pathParts[2] ?? ''; // 'entrades'
$method = $_SERVER['REQUEST_METHOD']; // 'GET'

// Inicialitzar connexions
require_once '../../_classes/connexio.php';
require_once '../../_classes/blog.php';

$db = Connexio::getInstance();
$blog = new Blog($db->getConnexio());

// Processar petició segons el recurs
switch ($resource) {
    case 'entrades':
        if ($method === 'GET') {
            // Obtenir filtres dels paràmetres GET
            $filtres = [
                'estat' => $_GET['estat'] ?? null,
                'idioma' => $_GET['idioma'] ?? null,
                'limit' => intval($_GET['limit'] ?? 50)
            ];
            
            // Cridar la classe PHP per obtenir dades
            $entrades = $blog->llistarEntrades($filtres);
            
            // Retornar resposta JSON
            echo json_encode($entrades);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Recurs no trobat']);
}
?>
```

#### **Pas 4: Classes PHP (Lògica de Negoci)**
```php
// Blog.php
class Blog {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function llistarEntrades($filtres = []) {
        // Construir consulta SQL dinàmica
        $sql = "
            SELECT 
                e.id,
                e.titol,
                e.estat,
                e.data_publicacio,
                u.nom as autor_nom
            FROM entrades_blog e
            LEFT JOIN usuaris_blog u ON e.autor_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Afegir filtres si existeixen
        if (!empty($filtres['estat'])) {
            $sql .= " AND e.estat = :estat";
            $params['estat'] = $filtres['estat'];
        }
        
        if (!empty($filtres['idioma'])) {
            $sql .= " AND e.idioma = :idioma";
            $params['idioma'] = $filtres['idioma'];
        }
        
        // Ordre i límit
        $sql .= " ORDER BY e.data_creacio DESC";
        
        if (!empty($filtres['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = $filtres['limit'];
        }
        
        // Executar consulta
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### **Exemple 2: Crear Nova Entrada**

#### **HTML (Formulari)**
```html
<form id="form-nova-entrada">
    <input type="text" id="titol" name="titol" placeholder="Títol de l'entrada" required>
    <textarea id="contingut" name="contingut" placeholder="Contingut" required></textarea>
    <select id="estat" name="estat">
        <option value="esborrany">Esborrany</option>
        <option value="publicat">Publicat</option>
    </select>
    <button type="button" onclick="guardarEntrada()">Guardar</button>
</form>
```

#### **JavaScript**
```javascript
async function guardarEntrada() {
    try {
        // Obtenir dades del formulari
        const formData = {
            titol: document.getElementById('titol').value,
            contingut: document.getElementById('contingut').value,
            estat: document.getElementById('estat').value
        };
        
        // Validar dades
        if (!formData.titol || !formData.contingut) {
            alert('Tots els camps són obligatoris');
            return;
        }
        
        // Enviar a l'API
        const response = await fetch('/api/blog/entrades', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Entrada creada correctament!');
            // Netejar formulari
            document.getElementById('form-nova-entrada').reset();
            // Recarregar llista
            filtrarEntrades();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error creant l\'entrada');
    }
}
```

#### **API (Crear)**
```php
case 'entrades':
    if ($method === 'POST') {
        // Obtenir dades del cos de la petició
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar dades obligatòries
        if (empty($input['titol']) || empty($input['contingut'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falten camps obligatoris']);
            return;
        }
        
        // Crear entrada
        $entradaId = $blog->crearEntrada($input);
        
        if ($entradaId) {
            echo json_encode([
                'success' => true,
                'id' => $entradaId,
                'message' => 'Entrada creada correctament'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error creant l\'entrada'
            ]);
        }
    }
    break;
```

---

## 📁 Estructura de Fitxers

### **Organització del Projecte**

```
marcmataro.dev/
├── _intern/                          # Zona d'administració
│   ├── blog.php                      # 🎨 Pàgina principal (HTML)
│   ├── css/
│   │   └── blog-admin.css             # 🎨 Estils visuals
│   ├── js/
│   │   └── blog-admin.js              # ⚡ Lògica frontend (AJAX)
│   └── api/
│       └── blog/
│           ├── index.php              # 🔗 API REST endpoint
│           └── .htaccess              # ⚙️ Configuració rutes
├── _classes/                          # Classes PHP (Backend)
│   ├── connexio.php                   # 🔌 Connexió base de dades
│   ├── blog.php                       # 🧠 Classe principal
│   ├── entrades-blog.php              # 📝 Gestió d'entrades
│   ├── categories-blog.php            # 📁 Gestió de categories
│   ├── comentaris-blog.php            # 💬 Gestió de comentaris
│   └── usuaris-blog.php               # 👥 Gestió d'usuaris
└── Documentació/                      # 📚 Documentació
    └── Guia_API_REST_i_AJAX.md        # 📖 Aquest document
```

### **Rols de Cada Fitxer**

#### **🎨 Frontend (Interfície d'Usuari)**

**`blog.php`**
- **Què fa**: Estructura HTML de la pàgina
- **Conté**: Formularis, taules, botons, modals
- **No conté**: Lògica de dades (això ho fa l'API)

**`blog-admin.css`**
- **Què fa**: Estils visuals i disseny responsive
- **Conté**: Colors, fonts, animacions, layouts
- **No conté**: Funcionalitat (això ho fa JavaScript)

**`blog-admin.js`**
- **Què fa**: Interactivitat i comunicació amb l'API
- **Conté**: Events, peticions AJAX, actualitzacions DOM
- **No conté**: Accés directe a base de dades (això ho fa l'API)

#### **🔗 API REST (Pont de Comunicació)**

**`api/blog/index.php`**
- **Què fa**: Rep peticions HTTP i les processa
- **Conté**: Routing, validació, cridades a classes PHP
- **No conté**: HTML ni lògica de negoci complexa

#### **🧠 Backend (Lògica de Negoci)**

**Classes PHP**
- **Què fan**: Gestionen dades i lògica de negoci
- **Contenen**: Consultes SQL, validacions, processament
- **No contenen**: HTML ni JavaScript

### **Flux de Dades Visual**

```
👤 USUARI
    │ Clica botó
    ▼
🎨 blog.php (HTML)
    │ Event detectat
    ▼
⚡ blog-admin.js (JavaScript)
    │ fetch('/api/blog/entrades')
    ▼
🔗 api/blog/index.php (API REST)
    │ $blog->llistarEntrades()
    ▼
🧠 Blog.php (Classe PHP)
    │ SELECT * FROM entrades...
    ▼
🗄️ BASE DE DADES
    │ Retorna resultats
    ▼
🧠 Blog.php
    │ return $entrades;
    ▼
🔗 api/blog/index.php
    │ echo json_encode($entrades);
    ▼
⚡ blog-admin.js
    │ actualitzarTaula(entrades);
    ▼
🎨 blog.php
    │ Taula actualitzada
    ▼
👤 USUARI veu els resultats
```

---

## 🔄 Fluxos de Treball Complets

### **Flux 1: Carregar Pàgina Inicial**

#### **Seqüència d'Events**

1. **Usuari accedeix a `blog.php`**
   ```
   Navegador → Servidor → blog.php
   ```

2. **HTML es carrega**
   ```html
   <!-- Estructura bàsica carregada -->
   <table id="taula-entrades">
       <tbody>
           <!-- Encara buit -->
       </tbody>
   </table>
   ```

3. **CSS s'aplica**
   ```css
   /* Estils visuals carregats */
   .data-table { ... }
   ```

4. **JavaScript s'executa**
   ```javascript
   // Event quan la pàgina està llesta
   document.addEventListener('DOMContentLoaded', function() {
       carregarDadesInicials(); // ← AJAX es dispara aquí
   });
   ```

5. **AJAX fa primera petició**
   ```javascript
   async function carregarDadesInicials() {
       const entrades = await blogAPI.obtenirEntrades();
       actualitzarTaula(entrades);
   }
   ```

6. **API processa i respon**
   ```php
   // Retorna JSON amb les entrades
   echo json_encode($entrades);
   ```

7. **JavaScript actualitza interfície**
   ```javascript
   // Omple la taula amb dades reals
   tbody.innerHTML = entrades.map(entrada => `<tr>...</tr>`).join('');
   ```

### **Flux 2: Filtrar Entrades**

#### **Seqüència d'Events**

1. **Usuari canvia filtre**
   ```html
   <select onchange="filtrarEntrades()">
       <option value="publicat">Publicats</option> <!-- Usuari tria això -->
   </select>
   ```

2. **JavaScript detecta canvi**
   ```javascript
   function filtrarEntrades() {
       // Obtenir valor seleccionat
       const estat = document.getElementById('filtro-estat').value; // 'publicat'
   }
   ```

3. **Prepara petició AJAX**
   ```javascript
   const filtres = { estat: 'publicat' };
   const entrades = await blogAPI.obtenirEntrades(filtres);
   ```

4. **Construeix URL amb paràmetres**
   ```javascript
   // URL final: /api/blog/entrades?estat=publicat
   const url = this.baseURL + 'entrades?' + new URLSearchParams(filtres);
   ```

5. **Envia petició HTTP**
   ```
   GET /api/blog/entrades?estat=publicat HTTP/1.1
   Accept: application/json
   ```

6. **API rep i processa**
   ```php
   $filtres = ['estat' => $_GET['estat']]; // 'publicat'
   $entrades = $blog->llistarEntrades($filtres);
   ```

7. **Classe PHP consulta BD**
   ```php
   $sql = "SELECT * FROM entrades WHERE estat = :estat";
   $stmt->execute(['estat' => 'publicat']);
   ```

8. **Retorna resposta JSON**
   ```json
   [
     {
       "id": 1,
       "titol": "Primera entrada",
       "estat": "publicat",
       "autor_nom": "Marc"
     }
   ]
   ```

9. **JavaScript rep i actualitza**
   ```javascript
   // Substitueix contingut de la taula
   actualitzarTaula(entrades);
   ```

### **Flux 3: Crear Nova Entrada**

#### **Seqüència d'Events**

1. **Usuari omple formulari**
   ```html
   <input id="titol" value="Nova entrada del blog">
   <textarea id="contingut">Contingut de l'entrada...</textarea>
   ```

2. **Usuari clica "Guardar"**
   ```html
   <button onclick="guardarEntrada()">Guardar</button>
   ```

3. **JavaScript recull dades**
   ```javascript
   const dades = {
       titol: document.getElementById('titol').value,
       contingut: document.getElementById('contingut').value
   };
   ```

4. **Valida dades**
   ```javascript
   if (!dades.titol || !dades.contingut) {
       alert('Tots els camps són obligatoris');
       return;
   }
   ```

5. **Envia petició POST**
   ```javascript
   const response = await fetch('/api/blog/entrades', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify(dades)
   });
   ```

6. **API valida i processa**
   ```php
   $input = json_decode(file_get_contents('php://input'), true);
   
   if (empty($input['titol'])) {
       http_response_code(400);
       echo json_encode(['error' => 'Títol obligatori']);
       return;
   }
   ```

7. **Classe PHP insereix a BD**
   ```php
   $sql = "INSERT INTO entrades (titol, contingut) VALUES (:titol, :contingut)";
   $stmt->execute($input);
   $entradaId = $this->db->lastInsertId();
   ```

8. **Confirma èxit**
   ```php
   echo json_encode([
       'success' => true,
       'id' => $entradaId,
       'message' => 'Entrada creada'
   ]);
   ```

9. **JavaScript gestiona resposta**
   ```javascript
   if (result.success) {
       alert('Entrada creada!');
       document.getElementById('form-nova-entrada').reset();
       filtrarEntrades(); // Actualitza llista
   }
   ```

---

## 🔧 Conceptes Tècnics Explicats

### **Mètodes HTTP**

Els mètodes HTTP són com "verbs" que indiquen què volem fer:

#### **GET - "Donar-me informació"**
```javascript
// Equivalent a: "Dona'm totes les entrades publicades"
fetch('/api/blog/entrades?estat=publicat')
```

#### **POST - "Crear alguna cosa nova"**
```javascript
// Equivalent a: "Crea una nova entrada amb aquestes dades"
fetch('/api/blog/entrades', {
    method: 'POST',
    body: JSON.stringify({ titol: 'Nova entrada' })
})
```

#### **PUT - "Actualitzar alguna cosa existent"**
```javascript
// Equivalent a: "Actualitza l'entrada 123 amb aquestes noves dades"
fetch('/api/blog/entrades/123', {
    method: 'PUT',
    body: JSON.stringify({ titol: 'Títol actualitzat' })
})
```

#### **DELETE - "Eliminar alguna cosa"**
```javascript
// Equivalent a: "Elimina l'entrada 123"
fetch('/api/blog/entrades/123', { method: 'DELETE' })
```

### **Format JSON**

JSON (JavaScript Object Notation) és el format estàndard per intercanviar dades:

#### **Exemple de Dades**
```javascript
// Objecte JavaScript
const entrada = {
    id: 1,
    titol: "Primera entrada",
    contingut: "Aquest és el contingut...",
    estat: "publicat",
    tags: ["web", "php", "tutorial"]
};

// Convertir a JSON per enviar
const jsonString = JSON.stringify(entrada);
// Resultat: '{"id":1,"titol":"Primera entrada",...}'

// Convertir de JSON quan rebem
const objetoRecibido = JSON.parse(jsonString);
```

### **Endpoints d'API**

Un endpoint és una URL específica que fa una acció concreta:

```
GET    /api/blog/entrades           → Llistar totes les entrades
GET    /api/blog/entrades/123       → Obtenir entrada específica
POST   /api/blog/entrades           → Crear nova entrada
PUT    /api/blog/entrades/123       → Actualitzar entrada 123
DELETE /api/blog/entrades/123       → Eliminar entrada 123

GET    /api/blog/categories         → Llistar categories
POST   /api/blog/categories         → Crear categoria

GET    /api/blog/comentaris         → Llistar comentaris
PUT    /api/blog/comentaris/456/aprovar → Aprovar comentari 456
```

### **Headers HTTP**

Els headers són metadades de la petició:

```javascript
fetch('/api/blog/entrades', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',  // Indiquem que enviem JSON
        'Accept': 'application/json',        // Indiquem que esperem JSON
        'Authorization': 'Bearer token123'   // Autenticació (si cal)
    },
    body: JSON.stringify(dades)
})
```

### **Codis d'Estat HTTP**

Els codis indiquen el resultat de la petició:

```
200 OK           → Tot correcte
201 Created      → Recurs creat correctament
400 Bad Request  → Petició incorrecta (dades errònies)
401 Unauthorized → No autoritzat
404 Not Found    → Recurs no trobat
500 Server Error → Error del servidor
```

### **Asincronía (async/await)**

Permet executar codi sense bloquejar la interfície:

#### **Abans (Síncrono - BLOQUEJA)**
```javascript
// Això bloquejaria la interfície
const dades = obtenirDadesBlocant(); // ⏳ Usuari no pot fer res
console.log(dades);
```

#### **Ara (Asíncrono - NO BLOQUEJA)**
```javascript
// Això no bloqueja
const dades = await obtenirDadesAsync(); // ✅ Usuari pot seguir interactuant
console.log(dades);
```

---

## 💡 Avantatges i Inconvenients

### **✅ Avantatges d'API REST + AJAX**

#### **Per l'Usuari**
- **Rapidesa**: No es recarrega la pàgina completa
- **Fluidesa**: Interacció natural i responsiva
- **Millor UX**: No es perd el context (scroll, formularis)

#### **Per al Desenvolupador**
- **Separació de concerns**: Frontend i Backend independents
- **Reutilització**: La mateixa API pot servir web, mòbil, etc.
- **Mantenibilitat**: Codi més organitzat i modular
- **Escalabilitat**: Fàcil afegir noves funcionalitats

#### **Per al Projecte**
- **Performance**: Menys dades transferides
- **SEO**: Possibilitat d'aplicacions híbrides
- **Flexibilitat**: Canviar frontend sense tocar backend

### **❌ Inconvenients**

#### **Complexitat Inicial**
- Més fitxers i estructura
- Corba d'aprenentatge més elevada
- Necessitat d'entendre conceptes nous

#### **Debugging**
- Errors poden estar en múltiples capes
- Necessitat d'eines de desenvolupador
- Més punts de fallada

#### **SEO Tradicional**
- Contingut carregat via AJAX pot ser invisible per crawlers
- Necessitat de tècniques SSR o pre-renderitzat

### **⚖️ Quan Usar Cada Aproximació**

#### **API REST + AJAX Recomanat Per:**
- Aplicacions dinàmiques i interactives
- Panels d'administració
- Dashboards amb filtres i actualitzacions freqüents
- Aplicacions que han de ser rapides i fluides

#### **Aproximació Tradicional Recomanada Per:**
- Webs de contingut estàtic
- Blogs simples
- Pàgines amb poc canvi de dades
- Projectes amb requisits SEO estrictes

---

## 🛠️ Eines de Desenvolupament

### **Navegador (Developer Tools)**

#### **Network Tab**
```
Permet veure totes les peticions AJAX:
1. Obre Developer Tools (F12)
2. Vés a la pestanya "Network"
3. Filter per "XHR" o "Fetch"
4. Veuràs totes les peticions a l'API
```

#### **Console Tab**
```javascript
// Proves ràpides de l'API
fetch('/api/blog/entrades')
    .then(response => response.json())
    .then(data => console.log(data));
```

### **Eines de Testing d'API**

#### **Postman**
Eina per provar APIs sense interfície:
```
GET http://localhost/api/blog/entrades
Headers: Content-Type: application/json
```

#### **curl (Terminal)**
```bash
# Obtenir entrades
curl -X GET "http://localhost/api/blog/entrades"

# Crear entrada
curl -X POST "http://localhost/api/blog/entrades" \
     -H "Content-Type: application/json" \
     -d '{"titol":"Test","contingut":"Prova"}'
```

### **Extensions de VS Code**

- **REST Client**: Fer peticions des de VS Code
- **JSON Viewer**: Formatjar JSON de manera llegible
- **PHP Intelephense**: Autocompletat PHP

---

## 📚 Glossari de Termes

### **API (Application Programming Interface)**
Conjunt de regles que permeten que diferents aplicacions es comuniquin.

**Analogia**: Com un menú de restaurant. Et diu què pots demanar i com.

### **REST (Representational State Transfer)**
Estil arquitectural per APIs que utilitza HTTP de manera estàndard.

**Characteristics**:
- URLs predictibles
- Mètodes HTTP estàndard
- Sense estat (stateless)

### **AJAX (Asynchronous JavaScript and XML)**
Tècnica per fer peticions al servidor sense recarregar la pàgina.

**Nota**: Malgrat el nom tingui "XML", ara s'usa principalment JSON.

### **JSON (JavaScript Object Notation)**
Format de dades lleuger i llegible per humans.

**Exemple**:
```json
{
    "nom": "Marc",
    "edat": 25,
    "actiu": true
}
```

### **Endpoint**
URL específica d'una API que realitza una acció concreta.

**Exemple**: `/api/blog/entrades` és un endpoint.

### **HTTP Method**
Verb que indica què volem fer amb un recurs.

- **GET**: Obtenir
- **POST**: Crear
- **PUT**: Actualitzar
- **DELETE**: Eliminar

### **Asynchronous (Asíncron)**
Codi que no bloqueja l'execució mentre espera una resposta.

**Exemple**: Mentre es carreguen dades, l'usuari pot seguir interactuant.

### **Promise**
Objecte JavaScript que representa el resultat eventual d'una operació asíncrona.

### **async/await**
Sintaxi moderna per treballar amb Promises de manera llegible.

### **Fetch API**
API moderna del navegador per fer peticions HTTP.

**Substitueix**: XMLHttpRequest (AJAX tradicional)

### **DOM (Document Object Model)**
Representació de l'HTML que JavaScript pot modificar.

### **Event Listener**
Funció que s'executa quan passa un event (click, canvi, etc.).

### **Callback**
Funció que s'executa quan una altra funció acaba.

### **CORS (Cross-Origin Resource Sharing)**
Mecanisme que permet peticions entre dominis diferents.

### **Middleware**
Codi que s'executa entre la petició i la resposta.

### **Routing**
Sistema per dirigir peticions a la funció correcta segons la URL.

### **Status Code**
Número que indica el resultat d'una petició HTTP.

**Exemples**: 200 (OK), 404 (No trobat), 500 (Error servidor)

---

## 🎓 Exercicis Pràctics

### **Exercici 1: Crear un Filtre Simple**

**Objectiu**: Afegir un filtre per idioma a la llista d'entrades.

#### **Tasques**:
1. Afegir un `<select>` amb opcions d'idiomes
2. Crear funció JavaScript `filtrarPerIdioma()`
3. Modificar l'endpoint de l'API per acceptar paràmetre `idioma`
4. Actualitzar la classe PHP per filtrar per idioma

#### **Solució**:

**HTML**:
```html
<select id="filtro-idioma" onchange="filtrarPerIdioma()">
    <option value="">Tots els idiomes</option>
    <option value="ca">Català</option>
    <option value="es">Espanyol</option>
    <option value="en">Anglès</option>
</select>
```

**JavaScript**:
```javascript
async function filtrarPerIdioma() {
    const idioma = document.getElementById('filtro-idioma').value;
    const entrades = await blogAPI.obtenirEntrades({ idioma });
    actualitzarTaula(entrades);
}
```

**PHP (API)**:
```php
$filtres = [
    'estat' => $_GET['estat'] ?? null,
    'idioma' => $_GET['idioma'] ?? null  // ← Nou filtre
];
```

**PHP (Classe)**:
```php
if (!empty($filtres['idioma'])) {
    $sql .= " AND e.idioma = :idioma";
    $params['idioma'] = $filtres['idioma'];
}
```

### **Exercici 2: Afegir Comptador d'Entrades**

**Objectiu**: Mostrar el número total d'entrades filtrades.

#### **Solució**:

**JavaScript**:
```javascript
function actualitzarTaula(entrades) {
    // Actualitzar taula
    const tbody = document.querySelector('#taula-entrades tbody');
    tbody.innerHTML = entrades.map(entrada => `...`).join('');
    
    // Actualitzar comptador ← NOU
    document.getElementById('total-entrades').textContent = 
        `Total: ${entrades.length} entrades`;
}
```

---

## 🔮 Pròxims Passos i Evolució

### **Millores Immediates**

1. **Paginació**
   ```javascript
   const entrades = await blogAPI.obtenirEntrades({
       page: 1,
       per_page: 10
   });
   ```

2. **Cerca per Text**
   ```javascript
   const entrades = await blogAPI.obtenirEntrades({
       search: 'paraula clau'
   });
   ```

3. **Ordenació Dinàmica**
   ```javascript
   const entrades = await blogAPI.obtenirEntrades({
       order_by: 'data_creacio',
       order_direction: 'DESC'
   });
   ```

### **Funcionalitats Avançades**

1. **WebSockets per Updates en Temps Real**
2. **Service Workers per Funcionalitat Offline**
3. **Progressive Web App (PWA)**
4. **API GraphQL per Consultes Complexes**

### **Optimitzacions**

1. **Caching de Respostes**
2. **Compressió de Dades**
3. **Lazy Loading d'Imatges**
4. **Virtual Scrolling per Llistes Llargues**

---

## 📞 Suport i Recursos

### **Documentació Oficial**

- [MDN - Fetch API](https://developer.mozilla.org/docs/Web/API/Fetch_API)
- [MDN - JSON](https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/JSON)
- [PHP Manual](https://www.php.net/manual/)

### **Tutorials Recomanats**

- [freeCodeCamp - APIs for Beginners](https://www.freecodecamp.org/news/apis-for-beginners/)
- [JavaScript.info - Fetch](https://javascript.info/fetch)

### **Eines Útils**

- [JSON Formatter](https://jsonformatter.org/)
- [Postman](https://www.postman.com/)
- [HTTP Status Codes](https://httpstatuses.com/)

---

## 🎯 Resum Final

Has après que:

1. **API REST** és com un cambrer que porta missatges entre frontend i backend
2. **AJAX** permet actualitzar parts de la pàgina sense recarregar-la completa
3. **JSON** és el format estàndard per intercanviar dades
4. **Separation of Concerns** millora l'organització i mantenibilitat
5. **L'experiència d'usuari** millora dramàticament amb aquestes tècniques

### **El Més Important**

No és necessari entendre tots els detalls tècnics de cop. El més important és entendre **el concepte** i **per què ho fem**:

- **Millor experiència d'usuari**
- **Codi més organitzat**
- **Aplicacions més ràpides i fluides**

Amb el temps, els detalls tècnics es van aprenent de manera natural! 🚀

---

*Document creat per Marc Mataró - Setembre 2025*
*Versió 1.0 - Guia completa per a principiants*