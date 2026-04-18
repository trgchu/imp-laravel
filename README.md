# EN REVISIÓN AÚN, NO JALA AL 100%

# HoshiChat

HoshiChat es una aplicación de chat en tiempo real desarrollada con Laravel y Vue. Permite a los usuarios comunicarse en conversaciones dinámicas con actualización instantánea de mensajes sin recargar la página.

## Tecnologías utilizadas

- Backend: Laravel (API REST)
- Frontend: Vue.js
- Tiempo real: Laravel Echo + Pusher
- Autenticación: Sanctum (Bearer Token)
- Base de datos: SQLite

## Características

- Chat en tiempo real
- Gestión de conversaciones
- Soporte para múltiples usuarios
- Bot integrado (Selene) que responde sobre la Luna
- Actualización automática de mensajes sin recarga

## Bot Selene

Selene es un bot que detecta palabras clave relacionadas con la Luna y responde automáticamente con información como:

- Fases lunares  
- Misiones Apolo  
- Cráteres  
- Temperatura  
- Datos curiosos  

## Estructura del proyecto

### Frontend (Vue)

- ChatPage.vue: Vista principal  
- useConversations.js: Manejo de conversaciones  
- useMessages.js: Manejo de mensajes  
- echo.js: Configuración de WebSockets  

### Backend (Laravel)

- ConversationController: Crear y listar conversaciones  
- MessageController: Envío y almacenamiento de mensajes  
- Evento MessageSent: Comunicación en tiempo real  

## Base de datos

Tablas principales:

- users  
- conversations  
- messages  
- conversation_user (tabla pivote)  

Incluye datos de prueba:

- 3 usuarios  
- Bot Selene  
- Conversaciones temáticas sobre la Luna  

## Instalación

```bash
# Clonar repositorio
git clone https://github.com/trgchu/imp-laravel.git

# Entrar al proyecto
cd imp-laravel

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migraciones
php artisan migrate --seed

# Ejecutar servidor
php artisan serve

# Frontend
npm run dev
```

## Tiempo real

El sistema usa eventos para actualizar mensajes en vivo:

- El backend emite MessageSent  
- Pusher transmite el evento  
- El frontend escucha con Laravel Echo  

## Notas

- Configurar correctamente Pusher en el archivo .env  
- Sanctum debe estar configurado para autenticación por token  

## Autor

Proyecto desarrollado como práctica de chat en tiempo real con Laravel y Vue.
