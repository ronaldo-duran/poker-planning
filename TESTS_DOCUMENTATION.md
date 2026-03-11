# Suite de Pruebas - Planning Poker Application

Se ha creado un conjunto completo de pruebas para la aplicación Planning Poker, incluyendo:

## 📋 Estructura de Pruebas

### 1. **Factories** (Generadores de datos de prueba)
Creados para todos los modelos principales:
- `database/factories/RoomFactory.php` - Crea instancias de Room con datos realistas
- `database/factories/VoteSessionFactory.php` - Crea sesiones de votación en diferentes estados
- `database/factories/VoteFactory.php` - Crea votos con valores predeterminados o personalizados
- `database/factories/EmojiFactory.php` - Crea emojis para pruebas de reacciones

### 2. **Pruebas Unitarias** (`tests/Unit/`)

#### Services (`tests/Unit/Services/`)
- **AuthServiceTest**: 8 pruebas
  - Registro de usuarios con contraseñas hasheadas
  - Login con credenciales correctas/incorrectas
  - Logout y gestión de tokens
  - Manejo de errores de validación

- **RoomServiceTest**: 9 pruebas
  - Creación de salas with códigos únicos
  - Agregar usuarios a salas (host y votantes)
  - Cambio de estado de la sala
  - Toggle de emojis
  - Unirse con código de invitación

- **VoteSessionServiceTest**: 7 pruebas
  - Crear sesiones de votación
  - Cerrar sesiones previas al crear nuevas
  - Envío y actualización de votos
  - Cálculo de promedio (excluyendo votos no numéricos)
  - Revelación de votos

- **EmojiServiceTest**: 4 pruebas
  - Envío de emojis a sala
  - Envío de emojis dirigidos a usuarios específicos
  - Validación de permisos
  - Respeto a bloqueos de emojis

#### Models (`tests/Unit/Models/`)
- **UserTest**: 5 pruebas - Relaciones y atributos de usuario
- **RoomTest**: 6 pruebas - Relaciones y configuración de sala
- **VoteSessionTest**: 4 pruebas - Relaciones y estado de sesión

#### Repositories (`tests/Unit/Repositories/`)
- **RoomRepositoryTest**: 8 pruebas
  - CRUD operations
  - Búsqueda por ID y código
  - Paginación de salas por usuario

### 3. **Pruebas de Feature/API** (`tests/Feature/`)

#### AuthApiTest (11 pruebas)
```
POST /api/auth/register     - Registro con validación
POST /api/auth/login        - Login con credenciales
POST /api/auth/logout       - Logout y eliminación de token
GET  /api/auth/me           - Obtener usuario autenticado
```

#### RoomApiTest (13 pruebas)
```
GET    /api/rooms                  - Listar salas del usuario
POST   /api/rooms                  - Crear nueva sala
GET    /api/rooms/{id}             - Ver detalles de sala
POST   /api/rooms/join/{code}      - Unirse a sala con código
POST   /api/rooms/{room}/leave     - Salir de sala
PATCH  /api/rooms/{room}/state     - Cambiar estado
PATCH  /api/rooms/{room}/toggle-emojis - Bloquear/desbloquear emojis
```

#### VoteSessionApiTest (10 pruebas)
```
POST   /api/rooms/{room}/sessions  - Crear sesión de votación
POST   /api/sessions/{session}/vote - Enviar voto
POST   /api/sessions/{session}/reveal - Revelar votos
GET    /api/sessions/{session}     - Ver sesión (con privacidad)
```

#### EmojiApiTest (5 pruebas)
```
POST   /api/rooms/{room}/emojis    - Enviar emoji a sala
POST   /api/rooms/{room}/emojis    - Enviar emoji dirigido
```

### 4. **Pruebas E2E/Integración** (`tests/Feature/E2E/`)

**PlanningPokerFlowTest** (5 flujos completos)

1. **complete_planning_poker_session**
   - Host crea sala → Votantes se unen → Crear sesión → Votar → Revelar

2. **multiple_voting_sessions**
   - Flujo con múltiples historias
   - Sesiones anteriores se cierran automáticamente

3. **emoji_reactions_during_session**
   - Envío de reacciones emoji durante votación
   - Emojiis dirigidos y de grupo

4. **room_management_flow**
   - Cambio de estado
   - Toggle de emojis
   - Verificación de persistencia

5. **user_can_join_and_leave_room**
   - Unirse a sala
   - Cambio automático de online status

## 🚀 Cómo ejecutar las pruebas

### Requisitos previos

1. **Configurar base de datos de pruebas**

   Laravel usa aplicar rutas de `.env` por ambiente. Para pruebas, necesitas:

   **Opción A: PostgreSQL** (recomendado, igual a desarrollo)
   ```bash
   # 1. Crear base de datos de prueba
   createdb poker_planning_test   # En PostgreSQL
   
   # 2. Ejecutar migraciones
   php artisan migrate --database=postgres --force --env=testing
   ```

   **Opción B: SQLite File** (alternativa)
   ```bash
   # Editar phpunit.xml:
   # <env name="DB_CONNECTION" value="sqlite"/>
   # <env name="DB_DATABASE" value="./database/testing.sqlite"/>
   
   # Asegurar que sqlite3 esté habilitado en php.ini
   ```

2. **Ejecutar suite completa de pruebas**

```bash
# Todas las pruebas
php artisan test

# Solo unitarias
php artisan test tests/Unit

# Solo features/API
php artisan test tests/Feature

# Específico
php artisan test tests/Unit/Services/AuthServiceTest

# Con formato legible
php artisan test --testdox

# Con cobertura
php artisan test --coverage
```

## 📊 Resumen de Cobertura

| Componente | Pruebas | Estado |
|-----------|---------|--------|
| Services  | 28 | ✅ Completo |
| Models    | 15 | ✅ Completo |
| Repositories | 8 | ✅ Completo |
| Auth API  | 11 | ✅ Completo |
| Room API  | 13 | ✅ Completo |
| Vote API  | 10 | ✅ Completo |
| Emoji API | 5 | ✅ Completo |
| E2E Flows | 5 | ✅ Completo |
| **TOTAL** | **95** | ✅ Completo |

## 🔍 Patrones de Pruebas

Todas las pruebas siguen el patrón **AAA** (Arrange, Act, Assert):

```php
#[Test]
public function example_test(): void
{
    // Arrange: Preparar datos
    $user = User::factory()->create();
    
    // Act: Ejecutar acción
    $result = $service->doSomething($user);
    
    // Assert: Verificar resultado
    $this->assertEquals('expected', $result);
}
```

## ✨ Características de las Pruebas

✅ **Nomenclatura clara** - Nombres que describen exactamente qué se prueba
✅ **Datos realistas** - Factories que generan datos coherentes
✅ **Coverage completo** - Flujos felices y casos de error
✅ **Pruebas de permisos** - Validación de autorización en endpoints
✅ **Pruebas de validación** - Campos requeridos y formatos
✅ **Pruebas de estado** - Cambios en BD verificados con assertDatabaseHas
✅ **Pruebas de privacidad** - Votos ocultos hasta revelar
✅ **Flujos E2E** - Sesiones completas de planning poker

## 📝 Próximas acciones

1. **Configurar base de datos de pruebas** (PostgreSQL o SQLite)
2. **Ejecutar tests**: `php artisan test`
3. **Revisar coverage**: `php artisan test --coverage`
4. **Integrar en CI/CD** (GitHub Actions, etc.)
5. **Agregar más tests** según nuevas funcionalidades
