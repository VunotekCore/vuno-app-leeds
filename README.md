# vuno · leed

Manual Lead Scraping & Management System (Agile CRM).

## Requisitos

- PHP 8.1+
- MySQL 8.0+
- Node.js 20+
- Composer (opcional)

## Variables de entorno

| Variable     | Descripción           | Obligatorio |
|-------------|-----------------------|-------------|
| `DB_HOST`   | Host MySQL            | No (default localhost) |
| `DB_PORT`   | Puerto MySQL          | No (default 3306) |
| `DB_NAME`   | Base de datos         | No (default vuno_app_leed) |
| `DB_USER`   | Usuario MySQL         | No (default dail) |
| `DB_PASS`   | Contraseña MySQL      | **Sí** |
| `DB_SOCKET` | Socket MySQL (opcional) | No |
| `JWT_SECRET`| Secreto para firmar JWT | **Sí** |

Copia `backend/.env.example` y completa los valores:

```bash
cp backend/.env.example backend/.env
# Editar .env con tus valores
export $(grep -v '^#' backend/.env | xargs)
```

## Setup rápido

### 1. Base de datos

**Opción A** — MySQL independiente (recomendado para desarrollo):

```bash
./start-mysql.sh
```

**Opción B** — MySQL systemd ya instalado:

```bash
mysql -u root -p < database/schema.sql
```

Esto crea la base de datos `vuno_app_leed` con las tablas `users`, `leads`, `templates` y el usuario admin por defecto.

### 2. Backend + Frontend (producción)

```bash
cd backend
export JWT_SECRET=tu_secreto_aqui
export DB_PASS=tu_password
php -S localhost:8000 -t public/ public/index.php
```

Esto sirve tanto la API como el frontend build en un solo puerto.

### 3. Frontend (desarrollo)

```bash
cd frontend
npm install
npm run dev
```

## Login

| Usuario | Contraseña  |
|---------|-------------|
| dail    | DB_PASS_PLACEHOLDER   |

## Estructura del proyecto

```
vuno-app-leed/
├── database/
│   └── schema.sql          # DDL completo con constraints y seed
├── backend/
│   ├── config/              # database.php, app.php (JWT secret)
│   ├── Core/                # Database, Router, Request, Response, JWT
│   ├── Models/              # User, Lead, Template
│   ├── Controllers/         # Auth, Lead, Template, Dashboard
│   ├── public/index.php     # Entry point
│   └── helpers.php          # uuid_v7(), sanitize_phone(), render_template()
├── frontend/
│   ├── src/
│   │   ├── views/           # Login, Dashboard, Leads, Templates
│   │   ├── components/      # AppNav, LeadForm, LeadTable, etc.
│   │   ├── stores/          # Pinia auth store
│   │   ├── services/        # Axios + JWT interceptor
│   │   └── utils/           # phone.js sanitization
│   ├── package.json
│   └── vite.config.js
└── README.md
```

## API endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/login` | Login (JWT) |
| GET | `/api/dashboard` | Métricas + follow-up alerts |
| GET | `/api/leads` | Lista leads (filtrable) |
| POST | `/api/leads` | Crear lead |
| GET | `/api/leads/check-duplicate` | Validar duplicado |
| GET | `/api/leads/{id}` | Detalle lead |
| PUT | `/api/leads/{id}` | Actualizar lead |
| DELETE | `/api/leads/{id}` | Eliminar lead |
| POST | `/api/leads/{id}/send` | Avanzar contacto + WhatsApp |
| GET | `/api/templates` | Listar templates |
| POST | `/api/templates` | Crear template |
| PUT | `/api/templates/{id}` | Actualizar template |
| DELETE | `/api/templates/{id}` | Eliminar template |

## Funcionalidades clave

- **Phone sanitization**: Normalización automática a formato WhatsApp (`+505 8888-8888` → `50588888888`)
- **Duplicados**: `UNIQUE(phone, profile_url)` en BD + validación en tiempo real vía API
- **Follow-up alerts**: Leads con status `First Contact` y ≥2 días sin respuesta
- **Mensajes**: Templates con tags `[StoreName]` y `[TierPrice]` reemplazables
- **WhatsApp**: Botón que abre `wa.me/{phone}?text={encoded_message}` y actualiza status
- **IDs**: UUID v7 (ordenables cronológicamente) almacenados como `BINARY(16)`
