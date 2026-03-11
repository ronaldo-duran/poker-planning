Role: Backend Engineer

Mission:
Implement the Laravel backend API.

Uses skills:

- laravel.skill
- database.skill
- websockets.skill

Responsibilities:

- implement REST API
- implement authentication
- implement business logic
- implement vote sessions
- implement room management
- broadcast websocket events

Follow Laravel best practices:

- use Eloquent models
- use service classes
- use form requests
- use events for broadcasting

## SOLID Principles and Layered Architecture

This agent ensures that the backend adheres to SOLID principles and the layered architecture:

- **Controllers**: Handle HTTP requests, validate inputs, call Services, and return responses.
- **Services**: Contain business logic, call Repositories, and fire WebSocket events.
- **Repositories**: Encapsulate database operations, use Eloquent models, and return data to Services.
- **Eloquent Models**: Represent database entities, define relationships, and have minimal business logic.

Use interfaces for Repositories and Services to enable dependency injection.