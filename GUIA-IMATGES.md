# Sistema de Gestió d'Imatges - Guia d'Ús

## 📁 Estructura Recomanada

```
img/
├── placeholder-project.jpg          # Imatge per defecte
└── Projects/                        # Carpeta única per totes les imatges
    ├── WebEmmaPortadaProjecte.png
    ├── nom-projecte-1.jpg
    ├── nom-projecte-2.png
    └── ...
```

## 🎯 Com Funciona

### Rutes Absolutes
- **✅ CORRECTE:** `/img/projectes/imatge.jpg`
- **❌ INCORRECTE:** `../img/projectes/imatge.jpg`

### En el Formulari
Al camp "Imatge de portada", pots posar:

1. **Només el nom del fitxer:** `imatge-projecte.jpg`
   - Sistema cerca automàticament a `/img/Projects/`
   
2. **Ruta completa:** `/img/Projects/imatge-projecte.jpg`
   - Sistema neteja la ruta i utilitza només el nom del fitxer

### Carpeta Única
- **`/img/Projects/`** - Totes les imatges dels projectes

## 🔧 Base URL Automàtic

El sistema detecta automàticament el subdirectori del projecte:

- **Desenvolupament WAMP:** `http://localhost/marcmataro.dev/img/Projects/imatge.jpg`
- **Producció:** `https://marcmataro.dev/img/Projects/imatge.jpg`

No cal canviar res en el codi quan migris a producció!

## 🔧 Fallback Automàtic
Si la imatge no es troba, es mostra automàticament el placeholder

## ✅ Millors Pràctiques

1. **Utilitza noms consistents:**
   - `projecte-portfolio.jpg`
   - `app-tasques.png`
   - `web-emma.jpg`

2. **Guarda només el nom del fitxer a la BD:**
   - ✅ `portfolio.jpg`
   - ❌ `/img/projectes/portfolio.jpg`

3. **Organitza les imatges:**
   - Imatges de projectes → `/img/Projects/`
   - Placeholder → `/img/placeholder-project.jpg`

## 🐛 Debugging
- Revisa els logs d'error de PHP per imatges no trobades
- Utilitza `test-imatges.php` per verificar l'estat de totes les imatges