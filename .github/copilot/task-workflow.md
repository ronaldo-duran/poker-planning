# Task Workflow

This document defines the collaboration workflow between agents.

Agents must follow this sequence when implementing new features to ensure consistency and avoid architectural conflicts.

---

# Development Pipeline

The development process follows this order:

1. Architect Agent
2. Database Agent
3. Backend Agent
4. Realtime Agent
5. Frontend Agent
6. UI Agent
7. Infrastructure Agent

Each stage depends on the output of the previous stage.

Agents should not skip stages unless explicitly instructed.

---

# Stage 1 — Architecture

Agent responsible:
Architect Agent

Responsibilities:

- Define system architecture
- Define application layers
- Define API structure
- Define event-driven architecture
- Define folder structures

Outputs:

docs/architecture.md

This document must describe:

- system components
- data flow
- interaction between frontend, backend and WebSockets

---

# Stage 2 — Database Design

Agent responsible:
Database Agent

Responsibilities:

- Design relational schema
- Define entity relationships
- Generate Laravel migrations

Entities include:

Users  
Rooms  
RoomUsers  
VoteSessions  
Votes  
Emojis  
RoomStates  

Outputs:

database migrations  
docs/database.md

---

# Stage 3 — Backend Implementation

Agent responsible:
Backend Agent

Responsibilities:

- Create Eloquent models
- Implement REST API endpoints
- Implement services
- Implement authentication
- Implement room logic
- Implement vote session logic

Outputs:

Laravel models  
controllers  
service classes  
API endpoints

API documentation should be updated in:

docs/api-spec.md

---

# Stage 4 — Realtime System

Agent responsible:
Realtime Agent

Responsibilities:

- Implement WebSocket broadcasting
- Create events and listeners
- Implement presence channels
- Synchronize voting state

Events include:

user_joined  
user_left  
vote_submitted  
reveal_started  
emoji_sent  
room_state_changed  

Outputs:

Laravel Events  
Broadcast configuration  
WebSocket event handlers

docs/websocket-events.md

---

# Stage 5 — Frontend Application

Agent responsible:
Frontend Agent

Responsibilities:

- Create Vue application structure
- Implement API communication
- Handle WebSocket connections
- Manage application state

Features include:

room interface  
vote submission  
vote reveal updates  
emoji reactions  

Outputs:

Vue components  
API services  
WebSocket client logic

---

# Stage 6 — UI / UX Implementation

Agent responsible:
UI Agent

Responsibilities:

- Implement responsive layout
- Create voting cards
- Implement reveal animations
- Implement emoji animations
- Implement dark/light mode

The UI must support:

desktop  
tablet  
mobile  

Outputs:

UI components  
animations  
layout improvements

---

# Stage 7 — Infrastructure and Deployment

Agent responsible:
Infrastructure Agent

Responsibilities:

- Prepare Docker configuration
- Configure Nginx
- Prepare deployment configuration
- Configure environment variables
- Configure WebSocket server

Target platform:

Google Cloud

Services may include:

Cloud Run  
Compute Engine  
Cloud SQL  

Outputs:

Dockerfile  
deployment configuration  
docs/deployment-gcp.md

---

# Feature Development Workflow

When implementing a new feature, agents must follow this sequence:

Architecture → Database → Backend → Realtime → Frontend → UI → Infrastructure

Example:

New Feature: "Custom Card Decks"

1. Architect Agent defines feature architecture.
2. Database Agent modifies schema.
3. Backend Agent implements API.
4. Realtime Agent synchronizes updates.
5. Frontend Agent integrates feature.
6. UI Agent improves interaction and animations.
7. Infrastructure Agent updates deployment if necessary.

---

# Code Modification Rules

If a change affects multiple layers:

Database change  
→ Backend update  
→ Realtime update  
→ Frontend update

Agents must respect this dependency order.

---

# Documentation Requirements

Agents must update documentation when adding major functionality.

Relevant documents include:

docs/architecture.md  
docs/database.md  
docs/api-spec.md  
docs/websocket-events.md  
docs/deployment-gcp.md

---

# Final Rule

Before implementing any task, agents must review:

project-context.md  
development-rules.md  
their agent definition  
their corresponding skills  
this workflow document