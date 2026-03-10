# Development Rules

This document defines the global development rules that all agents must follow when working on this repository.

Agents must always respect these rules to maintain a clean, scalable and maintainable architecture.

---

# General Principles

1. Follow clean and maintainable architecture.
2. Avoid duplicating logic.
3. Keep responsibilities separated between backend, frontend and infrastructure.
4. Prefer simple and readable solutions.
5. Follow the project technology stack defined in `project-context.md`.

---

# Backend Rules

Backend development uses **Laravel**.

Agents must:

- Follow Laravel conventions
- Use Eloquent ORM for database interactions
- Use migrations for database schema changes
- Use controllers only for request handling
- Place business logic in **Service classes**

Recommended structure:

app/
Models  
Http/Controllers  
Services  
Events  
Listeners  

Controllers should remain thin and delegate logic to services.

---
# Architecture Layer Rules

The backend must follow a layered architecture:

Controller → Service → Repository → Eloquent

- Controllers:
  - Handle HTTP requests
  - Validate inputs
  - Call Service layer
  - Return responses

- Services:
  - Contain business logic
  - Call Repositories for data persistence
  - Fire events for WebSockets

- Repositories:
  - Encapsulate all database operations
  - Return data to Services
  - Should use Eloquent models internally

- Eloquent Models:
  - Represent database entities
  - Define relationships
  - Should have minimal business logic

- Use interfaces for Repositories and Services to allow dependency injection


# API Design Rules

The backend exposes a **REST API**.

Guidelines:

- Use clear and consistent endpoints
- Use JSON responses
- Return appropriate HTTP status codes

Examples:

GET /api/rooms  
POST /api/rooms  
POST /api/rooms/{id}/join  
POST /api/votes  

Avoid embedding complex business logic directly in controllers.

---

# Database Rules

Database changes must always be implemented using **Laravel migrations**.

Guidelines:

- Use proper foreign keys
- Use indexes for frequently queried fields
- Maintain normalized tables
- Avoid storing redundant data

All relationships must be defined in Eloquent models.

---

# Realtime Rules

Realtime communication is implemented using **Laravel WebSockets**.

Guidelines:

- Use Laravel broadcasting events
- Prefer event-driven architecture
- Use presence channels for room participants

Typical flow:

User action  
→ Backend event  
→ Broadcast  
→ WebSocket server  
→ Frontend UI update

Do not place business logic inside WebSocket handlers.

---

# Frontend Rules

Frontend uses **Vue 3 with Tailwind CSS**.

Guidelines:

- Use the Composition API
- Create reusable components
- Separate UI components from data logic
- Use service files for API calls

Recommended structure:

src/
components  
views  
stores  
services  

---

# UI Design Rules

The interface must be:

- modern
- responsive
- clean
- accessible

Use **Tailwind CSS** utilities.

The interface must support:

- dark mode
- light mode
- mobile responsiveness

Animations should be lightweight and smooth.

---

# Realtime UI Behavior

Frontend must update UI in response to WebSocket events.

Examples:

- show user join/leave
- update vote status
- trigger reveal animations
- display emoji reactions

Do not rely only on API polling.

---

# Storage Rules

Images must be stored in:

/storage

Database records should only store the **file path**.

External storage services such as **Amazon S3 are not allowed**.

---

# Infrastructure Rules

Infrastructure must be compatible with **Google Cloud**.

Supported services:

- Cloud Run
- Compute Engine
- Cloud SQL

Agents may provide:

- Docker configurations
- Nginx configurations
- deployment scripts

---

# Code Quality

Agents should produce:

- readable code
- well structured files
- consistent naming conventions

Avoid:

- overly complex logic
- duplicated code
- mixing responsibilities between layers

---

# Documentation

When implementing major features, agents should also generate documentation in the `docs` folder.

Examples:

docs/api-spec.md  
docs/architecture.md  
docs/database.md  

---

# Final Rule

Agents must always consult:

1. `project-context.md`
2. their assigned agent file
3. the corresponding skill files
4. this `development-rules.md`

before generating or modifying code.