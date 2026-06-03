# Murmullo — Backend API REST

Backend de **Murmullo**, una red social desarrollada como Trabajo de Fin de Grado (DAW). Construido con Laravel, expone una API REST completa que consume un frontend en React.

> **Frontend:** [github.com/Jorgemairena13/murmullo_front](https://github.com/Jorgemairena13/murmullo_front) ·
> **Demo:** [murmullo-front.vercel.app](https://murmullo-front.vercel.app/) ·
> **Repositorio:** [github.com/Jorgemairena13/Murmullo](https://github.com/Jorgemairena13/Murmullo)

---

## Stack tecnológico

- **Framework:** Laravel 12
- **Autenticación:** Laravel Sanctum (tokens Bearer)
- **Base de datos:** MySQL
- **Tests:** Pest (SQLite in-memory)
- **Almacenamiento:** Cloudinary
- **IA:** OpenAI Vision (GPT-4o-mini)
- **Contenedores:** Docker + docker-compose
- **PHP:** 8.3+ (producción), 8.5 (local)

---

## Funcionalidades principales

- Registro e inicio de sesión con tokens Sanctum (rate limited)
- Perfiles públicos / privados con sistema de solicitudes de seguimiento
- CRUD completo de publicaciones con subida de imágenes a Cloudinary
- Generación automática de texto desde imagen con IA Vision
- Sistema de likes y comentarios
- Sistema de seguimiento entre usuarios (follow / unfollow / solicitudes)
- Notificaciones en tiempo real (like, comentario, follow, solicitud aceptada)
- Feed personalizado con posts de usuarios seguidos
- Explora posts de cuentas públicas
- Búsqueda de usuarios por nombre
- 78 tests automatizados (242 assertions)

---

## API Endpoints

### Autenticación (pública) — rate limited: 5 intentos/min

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/register` | Registrar nuevo usuario |
| POST | `/api/login` | Iniciar sesión y obtener token |

### Autenticación (protegida)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Obtener usuario autenticado |
| PUT | `/api/users/{id}` | Actualizar perfil propio |
| DELETE | `/api/users/{id}` | Eliminar cuenta |

### Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/users/{id}` | Ver perfil de un usuario |
| GET | `/api/search?q=` | Buscar usuarios por nombre |

### Publicaciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/posts` | Crear publicación |
| GET | `/api/posts/{id}` | Ver publicación |
| PUT | `/api/posts/{id}` | Editar publicación |
| DELETE | `/api/posts/{id}` | Eliminar publicación |
| POST | `/api/posts/generate-text` | Generar texto desde imagen (IA Vision) |
| GET | `/api/users/{user}/posts` | Posts de un usuario concreto |
| GET | `/api/feed` | Feed personalizado del usuario autenticado |
| GET | `/api/explorar` | Posts de cuentas públicas |

### Likes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/posts/{post}/like` | Dar like a una publicación |
| DELETE | `/api/posts/{post}/like` | Quitar like a una publicación |

### Comentarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/posts/{post}/comments` | Ver comentarios de una publicación |
| POST | `/api/posts/{post}/comment` | Comentar una publicación |
| DELETE | `/api/comment/{id}` | Eliminar un comentario |

### Seguimiento

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/users/{user}/follow` | Seguir a un usuario |
| DELETE | `/api/users/{user}/follow` | Dejar de seguir a un usuario |

### Solicitudes de seguimiento

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/follow-requests` | Solicitudes recibidas pendientes |
| POST | `/api/follow-requests/{id}/accept` | Aceptar solicitud |
| DELETE | `/api/follow-requests/{id}/reject` | Rechazar solicitud |

### Notificaciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/notifications` | Listar notificaciones (paginadas) |
| POST | `/api/notifications/read-all` | Marcar todas como leídas |

### Upload

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/upload` | Subir imagen a Cloudinary |

---

## Instalación

### Con Docker (recomendado)

```bash
git clone https://github.com/Jorgemairena13/Murmullo.git
cd Murmullo

cp .env.example .env
# Configurar CLOUDINARY_URL y OPENAI_API_KEY en .env

docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

### Local

```bash
git clone https://github.com/Jorgemairena13/Murmullo.git
cd Murmullo

composer install
cp .env.example .env
php artisan key:generate
# Configurar DB, CLOUDINARY_URL y OPENAI_API_KEY en .env

php artisan migrate
php artisan db:seed
php artisan serve
```

API disponible en `http://localhost:8000/api`

---

## Tests

```bash
php artisan test
```

78 tests — 242 assertions. Base de datos SQLite in-memory, Cloudinary mockeado.

---

## Autenticación

La API usa **Laravel Sanctum** con tokens Bearer que expiran a los 7 días:

```
Authorization: Bearer {token}
```

El token se obtiene al hacer login o registro.

---

## Arquitectura

```
Frontend React (Vercel)  ←── HTTP/JSON ──→  Backend Laravel API  ←──→ MySQL
                                                    │
                                          Cloudinary (imágenes)
                                          OpenAI (visión IA)
```

Backend puramente API REST — sin vistas HTML. Frontend y backend se despliegan independientemente.

---

## Autor

**Jorge Enrique Fernández**
[linkedin.com/in/jorge-fernandez-dev](https://linkedin.com/in/jorge-fernandez-dev) ·
[jorgefernandez.vercel.app](https://jorgefernandez.vercel.app) ·
[github.com/Jorgemairena13](https://github.com/Jorgemairena13)
