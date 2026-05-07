# Murmullo — Backend API REST

Backend de **Murmullo**, una red social desarrollada como Trabajo de Fin de Grado (DAW). Construido con Laravel, expone una API REST completa que consume el frontend en React.

> 🔗 **Frontend:** [github.com/Jorgemairena13/murmullo_front](https://github.com/Jorgemairena13/murmullo_front) · 🌐 **Demo en producción:** [jorgefernandez.vercel.app](https://jorgefernandez.vercel.app)

---

## Stack tecnológico

- **Framework:** Laravel 11
- **Autenticación:** Laravel Sanctum (tokens)
- **Base de datos:** MySQL
- **Arquitectura:** API REST desacoplada
- **Lenguaje:** PHP 8+

---

## Funcionalidades principales

- Registro e inicio de sesión con tokens Sanctum
- Gestión de perfil de usuario (ver, editar, eliminar cuenta)
- CRUD completo de publicaciones
- Sistema de likes (dar y quitar)
- Sistema de comentarios (crear y eliminar)
- Sistema de seguimiento entre usuarios (follow / unfollow)
- Feed personalizado con posts de usuarios seguidos
- Rutas protegidas por middleware de autenticación

---

## Estructura de la API

### Autenticación (pública)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/register` | Registrar nuevo usuario |
| POST | `/api/login` | Iniciar sesión y obtener token |

### Autenticación (protegida)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Obtener usuario autenticado |

### Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/users/{id}` | Ver perfil de un usuario |
| PUT | `/api/users/{id}` | Actualizar perfil propio |
| DELETE | `/api/users/{id}` | Eliminar cuenta |

### Publicaciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/posts` | Listar todas las publicaciones |
| POST | `/api/posts` | Crear publicación |
| GET | `/api/posts/{id}` | Ver publicación |
| PUT | `/api/posts/{id}` | Editar publicación |
| DELETE | `/api/posts/{id}` | Eliminar publicación |
| GET | `/api/users/{user}/posts` | Posts de un usuario concreto |
| GET | `/api/feed` | Feed personalizado del usuario autenticado |

### Likes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/posts/{post}/like` | Dar like a una publicación |
| DELETE | `/api/posts/{post}/like` | Quitar like a una publicación |

### Comentarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/posts/{post}/comment` | Comentar una publicación |
| DELETE | `/api/comment/{id}` | Eliminar un comentario |

### Seguimiento

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/users/{user}/follow` | Seguir a un usuario |
| DELETE | `/api/users/{user}/follow` | Dejar de seguir a un usuario |

---

## Instalación local

### Requisitos previos

- PHP 8.1+
- Composer
- MySQL

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/Jorgemairena13/Murmullo.git
cd Murmullo

# 2. Instalar dependencias
composer install

# 3. Copiar el fichero de entorno
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Configurar la base de datos en .env
DB_DATABASE=murmullo
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# 6. Ejecutar migraciones
php artisan migrate

# 7. (Opcional) Poblar la base de datos con datos de prueba
php artisan db:seed

# 8. Levantar el servidor
php artisan serve
```

La API estará disponible en `http://localhost:8000/api`

---

## Autenticación

La API usa **Laravel Sanctum**. Para acceder a los endpoints protegidos incluye el token en cada petición:

```
Authorization: Bearer {token}
```

El token se obtiene al hacer login o registro satisfactorio.

---

## Arquitectura

Este backend actúa exclusivamente como proveedor de datos vía API REST. No genera vistas HTML — toda la interfaz es responsabilidad del frontend en React.

```
Frontend React  ←──── HTTP/JSON ────→  Backend Laravel API  ←──→  MySQL
```

La separación permite que ambas capas se desplieguen y escalen de forma independiente.

---

## Autor

**Jorge Enrique Fernández**
[linkedin.com/in/jorge-fernandez-dev](https://linkedin.com/in/jorge-fernandez-dev) · [jorgefernandez.vercel.app](https://jorgefernandez.vercel.app) · [github.com/Jorgemairena13](https://github.com/Jorgemairena13)
