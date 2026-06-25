# VUNO LEED — Referencias del proyecto

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Frontend | Vue 3 (Composition API + `<script setup>`) | ^3.5 |
| Build | Vite | ^6.3 |
| CSS | Tailwind CSS (v4, config vía `@theme` en CSS) | ^4.1 |
| Estado | Pinia | ^3.0 |
| Ruteo | Vue Router 4 (history mode) | ^4.5 |
| HTTP | Axios | ^1.7 |
| Iconos | Lucide Vue | ^1.21 |
| PWA | vite-plugin-pwa + Workbox | ^1.3 |
| Backend | PHP (micro-framework propio) | 8.x |
| BD | SQLite (desarrollo) / MySQL (producción) | — |
| Package manager | pnpm | 10.20 |

## Estructura del monorepo

```
/
├── frontend/                    # Vue 3 SPA
│   ├── src/
│   │   ├── components/          # Componentes reutilizables
│   │   ├── views/               # Vistas (lazy-loaded por router)
│   │   ├── router/index.js      # Configuración de rutas + guards
│   │   ├── stores/auth.js       # Pinia store de autenticación
│   │   ├── services/api.js      # Cliente Axios con interceptors
│   │   ├── utils/               # Utilidades (phone, etc.)
│   │   ├── composables/         # Composables Vue (vacío actualmente)
│   │   ├── App.vue              # Layout raíz (sidebar + header móvil)
│   │   ├── main.js              # Entry point
│   │   └── style.css            # Tema Tailwind + estilos globales
│   ├── public/logo.webp
│   └── vite.config.js
├── backend/                     # PHP REST API
│   ├── Controllers/             # Auth, Lead, Product, Category, etc.
│   ├── Core/                    # Router, Request, Response, Database, JwtMiddleware
│   ├── Models/                  # Modelos de datos
│   ├── config/                  # app.php (JWT, CORS), database.php
│   ├── public/index.php         # Entry point (frontend + API routing)
│   ├── helpers.php
│   └── deploy.sh
├── database/schema.sql          # Schema MySQL con seed data
├── AGENTS.md                    # Este archivo
└── start-mysql.sh
```

## Rutas del frontend

| Ruta | Nombre | Vista | Auth requerida |
|---|---|---|---|
| `/login` | Login | `LoginView.vue` | No (solo guest) |
| `/` | Dashboard | `DashboardView.vue` | Sí |
| `/leads` | Leads | `LeadsView.vue` | Sí |
| `/templates` | Templates | `TemplatesView.vue` | Sí |
| `/tiers` | Tiers | `TiersView.vue` | Sí |
| `/products` | Products | `ProductsView.vue` | Sí |
| `/categories` | Categories | `CategoriesView.vue` | Sí |
| `/users` | Users | `UsersView.vue` | Sí |

## Componentes del frontend

| Componente | Propósito |
|---|---|
| `AppSidebar.vue` | Sidebar de navegación (drawer en móvil, fijo en desktop) |
| `DashboardCards.vue` | Cards de métricas en dashboard |
| `FollowUpAlerts.vue` | Alertas de seguimiento en dashboard |
| `LeadContactModal.vue` | Modal para contacto de lead |
| `LeadForm.vue` | Formulario de lead (crear/editar) |
| `LeadKanban.vue` | Vista kanban de leads |
| `LeadNotesModal.vue` | Modal de notas de lead |
| `LeadTable.vue` | Tabla de leads |
| `MonetaryProjection.vue` | Proyección monetaria en dashboard |

## API endpoints (backend PHP)

### Auth
- `POST /api/auth/login` — Login (público)
- `GET /api/auth/me` — Obtener usuario actual

### Dashboard
- `GET /api/dashboard` — Métricas del dashboard

### Leads
- `GET /api/leads` — Listar leads
- `POST /api/leads` — Crear lead
- `GET /api/leads/page-data` — Datos para formularios (products, categories, tiers)
- `GET /api/leads/check-duplicate` — Verificar duplicados (query: `phone`, `profile_url`)
- `GET /api/leads/{id}` — Obtener lead
- `PUT /api/leads/{id}` — Actualizar lead
- `DELETE /api/leads/{id}` — Eliminar lead
- `POST /api/leads/{id}/send` — Enviar mensaje
- `GET /api/leads/{id}/notes` — Notas del lead
- `POST /api/leads/{id}/notes` — Crear nota
- `DELETE /api/leads/{id}/notes/{noteId}` — Eliminar nota

### Templates
- `GET /api/templates` — Listar templates
- `POST /api/templates` — Crear template
- `GET /api/templates/page-data` — Datos para formularios
- `GET /api/templates/{id}` — Obtener template
- `PUT /api/templates/{id}` — Actualizar template
- `DELETE /api/templates/{id}` — Eliminar template

### Tiers
- `GET /api/tiers` — Listar tiers
- `POST /api/tiers` — Crear tier
- `GET /api/tiers/{id}` — Obtener tier
- `PUT /api/tiers/{id}` — Actualizar tier
- `DELETE /api/tiers/{id}` — Eliminar tier

### Products
- `GET /api/products` — Listar productos
- `POST /api/products` — Crear producto
- `GET /api/products/{id}` — Obtener producto
- `PUT /api/products/{id}` — Actualizar producto
- `DELETE /api/products/{id}` — Eliminar producto

### Categories
- `GET /api/categories` — Listar categorías
- `POST /api/categories` — Crear categoría
- `GET /api/categories/{id}` — Obtener categoría
- `PUT /api/categories/{id}` — Actualizar categoría
- `DELETE /api/categories/{id}` — Eliminar categoría

### Users
- `GET /api/users` — Listar usuarios
- `POST /api/users` — Crear usuario
- `GET /api/users/{id}` — Obtener usuario
- `PUT /api/users/{id}` — Actualizar usuario
- `DELETE /api/users/{id}` — Eliminar usuario
- `PUT /api/users/{id}/apikey` — Actualizar API key de WhatsApp

## Tema / Diseño (Tailwind v4 `@theme`)

Definido en `frontend/src/style.css`:

| Token | Valor | Uso |
|---|---|---|
| `--font-display` | Hanken Grotesk | Títulos, logo |
| `--font-body` | Inter | Texto general |
| `--font-mono` | JetBrains Mono | Código |
| `--color-surface` | `#0b1326` | Fondo principal |
| `--color-surface-charcoal` | `#1e293b` | Superficies secundarias |
| `--color-on-surface` | `#dae2fd` | Texto principal |
| `--color-slate-text` | `#94a3b8` | Texto secundario |
| `--color-vue-green` | `#42b883` | Color de acento (verde) |
| `--color-outline` | `#89938d` | Bordes |
| `--color-outline-variant` | `#404944` | Bordes secundarios |
| `--color-error` | `#ffb4ab` | Errores |

Breakpoints por defecto de Tailwind v4:
| Prefijo | Ancho mínimo |
|---|---|
| `sm` | 640px |
| `md` | 768px |
| `lg` | 1024px |
| `xl` | 1280px |
| `2xl` | 1536px |

## Responsive — Navegación sidebar

La sidebar usa el breakpoint `md` (768px) como punto de quiebre:

| Rango | Comportamiento |
|---|---|
| `< 768px` | Drawer oculto, header móvil fijo con hamburguesa, overlay oscuro al abrir |
| `>= 768px` | Sidebar fija visible (w-64), header móvil oculto, contenido con `md:ml-64` |

Clases Tailwind clave para el responsive del nav:
- `App.vue`: `md:hidden` en header, `md:pt-0 md:ml-64` en main, `overflow-x-hidden max-w-full` en contenedor flex, `min-w-0` en hijos flex
- `AppSidebar.vue`: `md:hidden` en backdrop y botón cerrar, `-translate-x-full md:translate-x-0` en aside
- `style.css`: `body { overflow-x: hidden }` previene scroll horizontal en 320px+

## Vistas — Breakpoints tabla/cards

Todas las vistas sincronizadas con el breakpoint `md` de la sidebar:

| Vista | Tabla (≥768px) | Cards (<768px) |
|---|---|---|
| `LeadTable.vue` | `hidden md:block` | `md:hidden` |
| `ProductsView.vue` | `hidden md:block` | `md:hidden` |
| `CategoriesView.vue` | `hidden md:block` | `md:hidden` |
| `UsersView.vue` | `hidden md:block` | `md:hidden` |

## Seguridad contra overflow en móvil

- `body { overflow-x: hidden }` en `style.css`
- `overflow-x-hidden max-w-full` en contenedor flex raíz (`App.vue`)
- `min-w-0` en hijos flex (`App.vue`) — permite que los flex-items encojan por debajo de su contenido mínimo

## Scripts disponibles

```bash
cd frontend
pnpm dev          # Inicia servidor de desarrollo (puerto 5173, proxy /api → :8000)
pnpm build        # Build para producción
pnpm preview      # Previsualiza build
```

Backend PHP se sirve via `php -S localhost:8000 -t backend/public` o Apache/Nginx.

## Convenciones de código

- **Vue**: Composition API con `<script setup>` + TypeScript donde aplique
- **CSS**: Tailwind utility classes, sin `@apply` ni CSS modules
- **Iconos**: Lucide Vue (`@lucide/vue`) — import named
- **Estado**: Pinia con Composition API (`defineStore` + `ref`/`computed`)
- **API**: Axios instance con interceptors (Bearer token + redirect 401)
- **Ruteo**: lazy-loading (`() => import(...)`), guards `requiresAuth` / `guest`
- **Vite proxy**: `/api` → `http://localhost:8000` en desarrollo
- **PHP Backend**: micro-framework propio (Router, Request, Response, Database, JWT)
- **Base de datos**: SQLite en desarrollo (`leeds.db`), MySQL en producción (schema en `database/schema.sql`)
