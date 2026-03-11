Skill: Laravel Development

Best practices:

- follow Laravel conventions
- use Eloquent ORM
- use migrations
- use service layer for business logic
- use events for broadcasting
- use policies for authorization

Recommended structure:

app/
Models
Http/Controllers
Services
Events
Listeners

## SOLID and Layered Architecture in Laravel

When developing in Laravel, follow these guidelines:

1. **Controllers**:
   - Validate inputs.
   - Call the Service layer.
   - Return JSON responses.

2. **Services**:
   - Implement business logic.
   - Call Repositories for data operations.
   - Fire events for WebSockets.

3. **Repositories**:
   - Use Eloquent models for database operations.
   - Return data to Services.
   - Implement interfaces for dependency injection.

4. **Eloquent Models**:
   - Define relationships.
   - Avoid business logic.

Always ensure that each layer has a single responsibility.