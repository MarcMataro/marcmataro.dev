# 🎨 Guia Visual: Diagrames i Esquemes API REST + AJAX

## 📊 Diagrames de Flux del Sistema

### 1. Arquitectura General del Sistema

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              NAVEGADOR (CLIENT)                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────────────────────┐  │
│  │    HTML     │    │     CSS     │    │          JavaScript             │  │
│  │             │    │             │    │                                 │  │
│  │ blog.php    │    │blog-admin.css│    │        blog-admin.js            │  │
│  │             │    │             │    │                                 │  │
│  │ ┌─────────┐ │    │ ┌─────────┐ │    │  ┌─────────────────────────────┐ │  │
│  │ │Formulari│ │    │ │ Estils  │ │    │  │    class BlogAPI {          │ │  │
│  │ │Taules   │ │    │ │ Animac. │ │    │  │      async request() {      │ │  │
│  │ │Botons   │ │    │ │ Responsive│   │  │        return fetch(...)    │ │  │
│  │ │Modals   │ │    │ │ Colors  │ │    │  │      }                      │ │  │
│  │ └─────────┘ │    │ └─────────┘ │    │  │    }                        │ │  │
│  └─────────────┘    └─────────────┘    │  └─────────────────────────────┘ │  │
│                                        └─────────────────────────────────┘  │
└─────────────────────────┬───────────────────────────────────────────────────┘
                          │
                          │ HTTP Request (AJAX)
                          │ GET /api/blog/entrades
                          │ Content-Type: application/json
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              SERVIDOR (PHP)                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │                        API REST                                     │    │
│  │                   /api/blog/index.php                               │    │
│  │                                                                     │    │
│  │  ┌─────────────────────────────────────────────────────────────┐    │    │
│  │  │              ROUTING                                        │    │    │
│  │  │                                                             │    │    │
│  │  │  GET    /entrades     → handleEntrades()                   │    │    │
│  │  │  POST   /entrades     → handleEntrades()                   │    │    │
│  │  │  GET    /categories   → handleCategories()                 │    │    │
│  │  │  GET    /comentaris   → handleComentaris()                 │    │    │
│  │  │  GET    /usuaris      → handleUsuaris()                    │    │    │
│  │  │                                                             │    │    │
│  │  └─────────────────────────────────────────────────────────────┘    │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                        │                                     │
│                                        │ $blog->llistarEntrades()            │
│                                        ▼                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │                     CLASSES PHP                                     │    │
│  │                                                                     │    │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌───────────┐  │    │
│  │  │   Blog.php  │  │ Entrades    │  │ Categories  │  │ Comentaris│  │    │
│  │  │             │  │ Blog.php    │  │ Blog.php    │  │ Blog.php  │  │    │
│  │  │ Main Class  │  │             │  │             │  │           │  │    │
│  │  │ Controller  │  │ CRUD        │  │ Hierarchy   │  │ Moderation│  │    │
│  │  │             │  │ Operations  │  │ Management  │  │ System    │  │    │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └───────────┘  │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                        │                                     │
│                                        │ SQL Queries                         │
│                                        ▼                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │                    BASE DE DADES                                    │    │
│  │                                                                     │    │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌───────────┐  │    │
│  │  │   entrades  │  │ categories  │  │ comentaris  │  │  usuaris  │  │    │
│  │  │   _blog     │  │   _blog     │  │   _blog     │  │   _blog   │  │    │
│  │  │             │  │             │  │             │  │           │  │    │
│  │  │ id          │  │ id          │  │ id          │  │ id        │  │    │
│  │  │ titol       │  │ nom         │  │ contingut   │  │ nom       │  │    │
│  │  │ contingut   │  │ slug        │  │ estat       │  │ email     │  │    │
│  │  │ estat       │  │ pare_id     │  │ entrada_id  │  │ rol       │  │    │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └───────────┘  │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2. Flux de Petició AJAX Detallat

```
FRONTEND                           API REST                        BACKEND
    │                                 │                              │
    │ 1. Event: onclick              │                              │
    │ ───────────────────────────────►│                              │
    │                                 │                              │
    │ 2. Preparar dades              │                              │
    │    const filtres = {           │                              │
    │      estat: 'publicat'         │                              │
    │    }                           │                              │
    │                                 │                              │
    │ 3. fetch('/api/blog/entrades') │                              │
    │ ───────────────────────────────►│ 4. Rebre petició HTTP       │
    │                                 │    GET /api/blog/entrades   │
    │                                 │    ?estat=publicat          │
    │                                 │                              │
    │                                 │ 5. Parse URL i paràmetres   │
    │                                 │    $resource = 'entrades'   │
    │                                 │    $method = 'GET'          │
    │                                 │    $filtres = ['estat'=>...] │
    │                                 │                              │
    │                                 │ 6. handleEntrades()         │
    │                                 │ ────────────────────────────►│
    │                                 │                              │ 7. $blog->llistarEntrades()
    │                                 │                              │
    │                                 │                              │ 8. Construir SQL
    │                                 │                              │    SELECT * FROM entrades
    │                                 │                              │    WHERE estat = 'publicat'
    │                                 │                              │
    │                                 │                              │ 9. Executar consulta
    │                                 │                              │    $stmt->execute()
    │                                 │                              │
    │                                 │ 10. Return resultats        │
    │                                 │ ◄────────────────────────────│
    │                                 │     Array d'entrades        │
    │                                 │                              │
    │ 11. Resposta JSON              │ 12. json_encode($entrades)   │
    │ ◄───────────────────────────────│     echo JSON response      │
    │     [{"id":1,"titol":"..."}]   │                              │
    │                                 │                              │
    │ 13. actualitzarTaula(entrades) │                              │
    │     foreach entrada:           │                              │
    │       crear <tr>               │                              │
    │       actualitzar DOM          │                              │
    │                                 │                              │
    │ 14. Usuari veu resultats       │                              │
```

### 3. Estructura de Dades JSON

```
Petició AJAX:
┌─────────────────────────────────────────┐
│ GET /api/blog/entrades?estat=publicat   │
│ Headers:                                │
│   Accept: application/json              │
│   Content-Type: application/json        │
└─────────────────────────────────────────┘
                    │
                    ▼
Resposta JSON:
┌─────────────────────────────────────────┐
│ [                                       │
│   {                                     │
│     "id": 1,                           │
│     "titol": "Primera entrada",        │
│     "contingut": "Aquest és el...",    │
│     "estat": "publicat",               │
│     "data_publicacio": "2025-09-29",   │
│     "autor_nom": "Marc Mataró",        │
│     "categoria": "Tecnologia",         │
│     "tags": ["php", "web", "api"],     │
│     "comentaris_count": 5,             │
│     "visites": 150                     │
│   },                                   │
│   {                                     │
│     "id": 2,                           │
│     "titol": "Segona entrada",         │
│     "contingut": "Una altra entrada...", │
│     "estat": "publicat",               │
│     "data_publicacio": "2025-09-28",   │
│     "autor_nom": "Marc Mataró",        │
│     "categoria": "Personal",           │
│     "tags": ["vida", "reflexions"],    │
│     "comentaris_count": 3,             │
│     "visites": 89                      │
│   }                                     │
│ ]                                       │
└─────────────────────────────────────────┘
```

## 🔄 Comparació Visual: Abans vs Després

### Mètode Tradicional (Sense AJAX)

```
USUARI CLICA FILTRE
        │
        ▼
┌─────────────────────┐
│ 🔄 RECARREGANT...   │  ← Usuari veu pantalla blanca
│                     │
│ ████████████████    │  ← Loading de tota la pàgina
│                     │
│ ⏳ Esperant...      │  ← No pot fer res més
└─────────────────────┘
        │
        ▼ (2-3 segons)
┌─────────────────────┐
│ ✅ Pàgina nova      │  ← Es perd scroll, formularis
│                     │
│ 📄 Tot recarregat   │  ← HTML+CSS+JS de nou
│                     │
│ 🎯 Resultats nous   │  ← Finalment veu els resultats
└─────────────────────┘

PROBLEMES:
❌ Lent (2-3 segons)
❌ Es perd el context
❌ Experiència trencada
❌ Més consum de dades
❌ Servidor més carregat
```

### Mètode Modern (Amb AJAX)

```
USUARI CLICA FILTRE
        │
        ▼
┌─────────────────────┐
│ ✅ Pàgina activa    │  ← Usuari segueix veient tot
│                     │
│ 📊 Taula: Loading...│  ← Només la taula mostra loading
│                     │
│ 👆 Pot seguir naveg.│  ← Pot fer altres coses
└─────────────────────┘
        │
        ▼ (0.5 segons)
┌─────────────────────┐
│ ✅ Pàgina activa    │  ← Tot igual
│                     │
│ 📊 Taula actualitzada│ ← Només canvia la taula
│                     │
│ 🎯 Resultats nous   │  ← Canvi imperceptible
└─────────────────────┘

AVANTATGES:
✅ Ràpid (0.5 segons)
✅ Conserva el context
✅ Experiència fluida
✅ Menys dades
✅ Servidor més eficient
```

## 📊 Mètodes HTTP Explicats Visualment

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              MÈTODES HTTP                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  GET - "Vull informació"                                               │
│  ┌─────────────────┐    REQUEST     ┌─────────────────┐                │
│  │   FRONTEND      │ ──────────────►│   API REST      │                │
│  │                 │  GET /entrades │                 │                │
│  │ "Dona'm         │                │ "Aquí tens      │                │
│  │  les entrades"  │◄──────────────  │  les entrades"  │                │
│  └─────────────────┘   RESPONSE     └─────────────────┘                │
│                         JSON data                                       │
│                                                                         │
│  POST - "Vull crear alguna cosa nova"                                  │
│  ┌─────────────────┐    REQUEST     ┌─────────────────┐                │
│  │   FRONTEND      │ ──────────────►│   API REST      │                │
│  │                 │ POST /entrades │                 │                │
│  │ "Crea aquesta   │ + JSON data    │ "Entrada creada │                │
│  │  nova entrada"  │◄──────────────  │  amb ID: 123"   │                │
│  └─────────────────┘   RESPONSE     └─────────────────┘                │
│                        Success msg                                      │
│                                                                         │
│  PUT - "Vull actualitzar alguna cosa existent"                         │
│  ┌─────────────────┐    REQUEST     ┌─────────────────┐                │
│  │   FRONTEND      │ ──────────────►│   API REST      │                │
│  │                 │PUT /entrades/123│                │                │
│  │ "Actualitza     │ + JSON data    │ "Entrada 123    │                │
│  │  l'entrada 123" │◄──────────────  │  actualitzada"  │                │
│  └─────────────────┘   RESPONSE     └─────────────────┘                │
│                        Success msg                                      │
│                                                                         │
│  DELETE - "Vull eliminar alguna cosa"                                  │
│  ┌─────────────────┐    REQUEST     ┌─────────────────┐                │
│  │   FRONTEND      │ ──────────────►│   API REST      │                │
│  │                 │DELETE /entr/123│                 │                │
│  │ "Elimina        │                │ "Entrada 123    │                │
│  │  l'entrada 123" │◄──────────────  │  eliminada"     │                │
│  └─────────────────┘   RESPONSE     └─────────────────┘                │
│                        Success msg                                      │
└─────────────────────────────────────────────────────────────────────────┘
```

## 🎭 Exemples de Casos d'Ús

### Cas 1: Filtrar Entrades per Estat

```
INTERFÍCIE D'USUARI:
┌─────────────────────────────────────────────────────────────┐
│ Filtra per estat: [Tots ▼] [Publicat] [Esborrany] [Arxivat]│
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 📝 LLISTA D'ENTRADES:                                      │
│                                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 📄 "Com crear una API REST" - Publicat - 2025-09-29   │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ 📄 "Guia d'AJAX per principiants" - Publicat - 2025-09│ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ 📄 "Tutorial de PHP avançat" - Esborrany - 2025-09-27 │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘

USUARI SELECCIONA "Publicat":
        │
        ▼ onclick="filtrarEntrades()"
        
JAVASCRIPT EXECUTA:
async function filtrarEntrades() {
    const estat = 'publicat';  // Valor seleccionat
    const entrades = await blogAPI.obtenirEntrades({ estat });
    actualitzarTaula(entrades);
}
        │
        ▼ fetch('/api/blog/entrades?estat=publicat')
        
API PROCESSA:
$filtres = ['estat' => 'publicat'];
$entrades = $blog->llistarEntrades($filtres);
        │
        ▼ SQL: SELECT * FROM entrades WHERE estat = 'publicat'
        
RESULTAT:
┌─────────────────────────────────────────────────────────────┐
│ Filtra per estat: [Tots ▼] [Publicat✓] [Esborrany] [Arxiv]│
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 📝 LLISTA D'ENTRADES (FILTRADES):                         │
│                                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 📄 "Com crear una API REST" - Publicat - 2025-09-29   │ │ ← Només les
│ ├─────────────────────────────────────────────────────────┤ │   publicades
│ │ 📄 "Guia d'AJAX per principiants" - Publicat - 2025-09│ │   es mostren
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Cas 2: Crear Nova Entrada

```
MODAL DE CREACIÓ:
┌───────────────────────────────────────────────────────────────┐
│ ✨ NOVA ENTRADA                                        [✖]   │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│ Títol: [Com configurar AJAX                             ]     │
│                                                               │
│ Contingut:                                                    │
│ ┌───────────────────────────────────────────────────────────┐ │
│ │ En aquest tutorial aprendràs a configurar AJAX...        │ │
│ │                                                           │ │
│ │                                                           │ │
│ └───────────────────────────────────────────────────────────┘ │
│                                                               │
│ Estat: [Esborrany ▼]                                        │
│                                                               │
│ [Cancel·lar]                              [Guardar Entrada] │
└───────────────────────────────────────────────────────────────┘

USUARI CLICA "Guardar Entrada":
        │
        ▼ onclick="guardarEntrada()"
        
JAVASCRIPT RECULL DADES:
const dades = {
    titol: "Com configurar AJAX",
    contingut: "En aquest tutorial...",
    estat: "esborrany"
};
        │
        ▼ fetch('/api/blog/entrades', { method: 'POST', body: JSON.stringify(dades) })
        
API PROCESSA:
$input = json_decode(file_get_contents('php://input'), true);
$entradaId = $blog->crearEntrada($input);
        │
        ▼ SQL: INSERT INTO entrades (titol, contingut, estat) VALUES (...)
        
RESPOSTA:
{
    "success": true,
    "id": 124,
    "message": "Entrada creada correctament"
}
        │
        ▼ JavaScript rep resposta
        
RESULTAT:
┌─────────────────────────────────────────────────────────────┐
│ 🎉 Entrada creada correctament!                           │
└─────────────────────────────────────────────────────────────┘
        │
        ▼ Modal es tanca + llista s'actualitza automàticament
        
LLISTA ACTUALITZADA:
┌─────────────────────────────────────────────────────────────┐
│ 📝 LLISTA D'ENTRADES:                                      │
│                                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 📄 "Com configurar AJAX" - Esborrany - 2025-09-29  🆕 │ │ ← Nova entrada
│ ├─────────────────────────────────────────────────────────┤ │   apareix aquí
│ │ 📄 "Com crear una API REST" - Publicat - 2025-09-29   │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 Debug i Solució de Problemes

### Eines de Desenvolupador del Navegador

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           DEVELOPER TOOLS                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 🌐 NETWORK TAB:                                                        │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ Name             Method  Status  Type    Size    Time               │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ entrades         GET     200     XHR     2.1KB   156ms             │ │ ← Petició AJAX
│ │ categories       GET     200     XHR     0.8KB   89ms              │ │
│ │ comentaris       GET     404     XHR     0.2KB   234ms  ❌         │ │ ← Error!
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│ 🖥️ CONSOLE TAB:                                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ > blogAPI.obtenirEntrades()                                        │ │ ← Provar API
│ │ < Promise {<resolved>: Array(5)}                                    │ │
│ │                                                                     │ │
│ │ > fetch('/api/blog/entrades').then(r => r.json())                 │ │
│ │ < [{id: 1, titol: "Primera entrada"}, ...]                        │ │
│ │                                                                     │ │
│ │ ❌ Error: Failed to fetch /api/blog/comentaris                     │ │ ← Error visible
│ └─────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

### Errors Comuns i Solucions

```
❌ ERROR: 404 Not Found
┌─────────────────────────────────────────────────────────────┐
│ PROBLEMA: L'endpoint de l'API no existeix                  │
│                                                             │
│ URL: GET /api/blog/comentaris                              │
│ ❌ Retorna: 404 Not Found                                  │
│                                                             │
│ CAUSES POSSIBLES:                                           │
│ • URL mal escrita (comentaris vs comentarios)              │
│ • Endpoint no implementat a l'API                          │
│ • Problema amb .htaccess routing                           │
│                                                             │
│ SOLUCIÓ:                                                    │
│ 1. Verificar URL: /api/blog/comentaris                     │
│ 2. Comprovar que existeix case 'comentaris' a l'API        │
│ 3. Revisar .htaccess per routing                           │
└─────────────────────────────────────────────────────────────┘

❌ ERROR: 500 Internal Server Error
┌─────────────────────────────────────────────────────────────┐
│ PROBLEMA: Error en el codi PHP                             │
│                                                             │
│ URL: POST /api/blog/entrades                               │
│ ❌ Retorna: 500 Internal Server Error                      │
│                                                             │
│ CAUSES POSSIBLES:                                           │
│ • Error de sintaxi PHP                                      │
│ • Variable no definida                                      │
│ • Error de connexió a base de dades                        │
│ • Falta require_once d'alguna classe                       │
│                                                             │
│ SOLUCIÓ:                                                    │
│ 1. Revisar logs d'error del servidor                       │
│ 2. Afegir try-catch amb logging                            │
│ 3. Verificar includes i requires                           │
│ 4. Provar consultes SQL per separat                        │
└─────────────────────────────────────────────────────────────┘

❌ ERROR: CORS (Cross-Origin)
┌─────────────────────────────────────────────────────────────┐
│ PROBLEMA: Navegador bloqueja petició per CORS              │
│                                                             │
│ Error: "Access-Control-Allow-Origin missing"               │
│                                                             │
│ CAUSA:                                                      │
│ • Falta headers CORS a l'API                               │
│                                                             │
│ SOLUCIÓ:                                                    │
│ Afegir a l'API:                                            │
│ header('Access-Control-Allow-Origin: *');                  │
│ header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');│
│ header('Access-Control-Allow-Headers: Content-Type');      │
└─────────────────────────────────────────────────────────────┘
```

## 📈 Optimitzacions i Millores

### Indicadors de Càrrega

```
ABANS (Sense feedback):
┌─────────────────────────────────────┐
│ [Filtrar ▼]                        │
│                                     │ ← Usuari clica, no passa res visible
│ Entrades:                          │   Sembla que no funciona
│ (                                  │
│  • Primera entrada                  │
│  • Segona entrada                   │ ← Apareixen de cop després d'uns segons
│ )                                   │
└─────────────────────────────────────┘

DESPRÉS (Amb loading):
┌─────────────────────────────────────┐
│ [Filtrar ▼]                        │
│                                     │
│ Entrades:                          │ ← Usuari clica...
│ 🔄 Carregant...                    │ ← Feedback immediat
│                                     │
└─────────────────────────────────────┘
        │ (0.5 segons després)
        ▼
┌─────────────────────────────────────┐
│ [Filtrar ▼]                        │
│                                     │
│ Entrades:                          │
│  • Entrada filtrada 1              │ ← Apareixen amb suavitat
│  • Entrada filtrada 2              │
└─────────────────────────────────────┘

CODI:
// Mostrar loading
tbody.innerHTML = '<tr><td colspan="8">🔄 Carregant...</td></tr>';

// Fer petició
const entrades = await blogAPI.obtenirEntrades(filtres);

// Actualitzar amb resultats
actualitzarTaula(entrades);
```

### Cache de Dades

```
SENSE CACHE:
Cada click = Nova petició al servidor
┌─────────┐  Request   ┌─────────┐  Query   ┌─────────────┐
│Frontend │ ────────► │   API   │ ───────► │ Base Dades  │
│         │ ◄──────── │         │ ◄─────── │             │
└─────────┘  Response  └─────────┘  Results └─────────────┘
   0.5 seg      0.3 seg      0.2 seg

AMB CACHE:
Primera petició: Normal
Segones peticions: Instantànies
┌─────────┐           ┌─────────────┐
│Frontend │           │ Cache Local │
│         │ ◄──────── │ (Instant)   │
└─────────┘   0.01seg  └─────────────┘

IMPLEMENTACIÓ:
class BlogAPI {
    constructor() {
        this.cache = new Map();
    }
    
    async obtenirEntrades(filtres = {}) {
        const cacheKey = JSON.stringify(filtres);
        
        // Comprovar cache
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }
        
        // Si no està en cache, fer petició
        const entrades = await this.request('entrades', 'GET', filtres);
        
        // Guardar en cache
        this.cache.set(cacheKey, entrades);
        
        return entrades;
    }
}
```

## 🎯 Patrons de Disseny Aplicats

### MVC (Model-View-Controller)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              PATRÓ MVC                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 🎨 VIEW (Vista)                                                        │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ blog.php + blog-admin.css                                           │ │
│ │                                                                     │ │
│ │ • HTML: Estructura i formularis                                     │ │
│ │ • CSS: Estils i presentació visual                                  │ │
│ │ • Només presentació, NO lògica de negoci                           │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                        │                                │
│                                        │ Events (clicks, canvis)        │
│                                        ▼                                │
│ ⚡ CONTROLLER (Controlador)                                            │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ blog-admin.js + api/blog/index.php                                  │ │
│ │                                                                     │ │
│ │ • JavaScript: Gestiona events i interaccions                       │ │
│ │ • API REST: Processa peticions i respostes                         │ │
│ │ • Coordina entre Vista i Model                                      │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                        │                                │
│                                        │ Cridades a mètodes             │
│                                        ▼                                │
│ 🧠 MODEL (Model)                                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ Classes PHP: Blog.php, EntradesBlog.php, etc.                      │ │
│ │                                                                     │ │
│ │ • Lògica de negoci i regles                                         │ │
│ │ • Accés a base de dades                                             │ │
│ │ • Validacions i processament                                        │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘

EXEMPLE PRÀCTIC:
1. USUARI clica botó "Filtrar" (VIEW)
   ↓
2. JavaScript detecta click i recull dades (CONTROLLER)
   ↓
3. API rep petició i crida classe PHP (CONTROLLER)
   ↓
4. Classe PHP consulta base dades i aplica lògica (MODEL)
   ↓
5. Model retorna dades processades (MODEL → CONTROLLER)
   ↓
6. API retorna JSON (CONTROLLER)
   ↓
7. JavaScript actualitza interfície (CONTROLLER → VIEW)
```

### API RESTful (Recursos i Operacions)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         RECURSOS i OPERACIONS                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📝 RECURS: Entrades                                                    │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ GET    /api/blog/entrades          → Llistar entrades              │ │
│ │ GET    /api/blog/entrades/123      → Obtenir entrada específica    │ │
│ │ POST   /api/blog/entrades          → Crear nova entrada            │ │
│ │ PUT    /api/blog/entrades/123      → Actualitzar entrada completa  │ │
│ │ PATCH  /api/blog/entrades/123      → Actualitzar entrada parcial   │ │
│ │ DELETE /api/blog/entrades/123      → Eliminar entrada              │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│ 📁 RECURS: Categories                                                  │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ GET    /api/blog/categories        → Llistar categories            │ │
│ │ GET    /api/blog/categories/456    → Obtenir categoria específica  │ │
│ │ POST   /api/blog/categories        → Crear nova categoria          │ │
│ │ PUT    /api/blog/categories/456    → Actualitzar categoria         │ │
│ │ DELETE /api/blog/categories/456    → Eliminar categoria            │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│ 💬 RECURS: Comentaris                                                 │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ GET    /api/blog/comentaris        → Llistar comentaris           │ │
│ │ POST   /api/blog/comentaris        → Crear nou comentari          │ │
│ │ PUT    /api/blog/comentaris/789/aprovar → Aprovar comentari       │ │
│ │ PUT    /api/blog/comentaris/789/rebutjar → Rebutjar comentari     │ │
│ │ DELETE /api/blog/comentaris/789    → Eliminar comentari           │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│ AVANTATGES D'AQUESTA ESTRUCTURA:                                       │
│ ✅ URLs predictibles i intuïtives                                      │
│ ✅ Separació clara de responsabilitats                                 │
│ ✅ Fàcil d'entendre i documentar                                       │
│ ✅ Estàndard de la indústria                                           │
│ ✅ Reutilitzable per altres clients (mòbil, etc.)                     │
└─────────────────────────────────────────────────────────────────────────┘
```

## 🚀 Evolució Future

### Possibles Millores

```
NIVELL 1 - Millores Bàsiques:
┌─────────────────────────────────────────────────────────────┐
│ • Paginació de resultats                                   │
│ • Cerca en temps real                                       │
│ • Ordenació per columnes                                    │
│ • Selecció múltiple per accions en bloc                    │
│ • Previsualització d'entrades                              │
└─────────────────────────────────────────────────────────────┘

NIVELL 2 - Funcionalitats Avançades:
┌─────────────────────────────────────────────────────────────┐
│ • WebSockets per actualitzacions en temps real             │
│ • Service Workers per funcionalitat offline                │
│ • Push notifications per nous comentaris                   │
│ • Drag & drop per reordenar elements                       │
│ • Editor de text ric (TinyMCE/CKEditor)                    │
└─────────────────────────────────────────────────────────────┘

NIVELL 3 - Tecnologies Emergents:
┌─────────────────────────────────────────────────────────────┐
│ • Progressive Web App (PWA)                                │
│ • GraphQL per consultes complexes                          │
│ • Realitat augmentada per previsualitzacions               │
│ • Intel·ligència artificial per suggeriments               │
│ • Blockchain per autenticitat del contingut                │
└─────────────────────────────────────────────────────────────┘
```

### Migració a Frameworks Moderns

```
OPCIÓ 1 - Vue.js Frontend:
┌─────────────────────────────────────────────────────────────┐
│ Frontend: Vue.js (Single Page Application)                 │
│ Backend: Mantenir API PHP actual                           │
│ Avantatges: Component-based, reactive, fàcil migració      │
└─────────────────────────────────────────────────────────────┘

OPCIÓ 2 - React + Node.js:
┌─────────────────────────────────────────────────────────────┐
│ Frontend: React                                             │
│ Backend: Node.js + Express                                  │
│ Avantatges: Ecosistema JavaScript complet                  │
└─────────────────────────────────────────────────────────────┘

OPCIÓ 3 - Laravel + Vue:
┌─────────────────────────────────────────────────────────────┐
│ Frontend: Vue.js                                            │
│ Backend: Laravel (PHP framework modern)                    │
│ Avantatges: Mantenir PHP, afegir estructura moderna        │
└─────────────────────────────────────────────────────────────┘
```

---

*Document Visual creat per Marc Mataró - Setembre 2025*
*Complementa la Guia Principal d'API REST i AJAX*