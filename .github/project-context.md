# Project Context

## Purpose
This project is a professional Planning Poker web application designed to facilitate agile estimation sessions. The system includes real-time collaboration, user management, and a modern, responsive interface.

## Structure

### Agents
Agents are responsible for managing specific areas of the project. Each agent has a dedicated file in the `agents` folder:

1. **Backend Agent**: Manages server-side logic, including REST API, WebSockets, and database operations.
2. **Frontend Agent**: Handles the user interface, including Vue.js/React, Tailwind CSS, and responsive design.
3. **Database Agent**: Designs and manages database migrations, models, and relationships.
4. **Infrastructure Agent**: Configures and deploys the application on Google Cloud.
5. **Realtime Agent**: Implements real-time communication using Laravel WebSockets.
6. **UI Agent**: Designs and manages UI components such as voting cards and animations.

### Skills
Skills provide detailed instructions for specific tasks. Each skill is linked to an agent and is located in the `skills` folder:

1. **Backend Skill**: Instructions for backend development tasks.
2. **Frontend Skill**: Instructions for frontend development tasks.
3. **Database Skill**: Instructions for database design and management.
4. **Infrastructure Skill**: Instructions for infrastructure setup and deployment.
5. **Realtime Skill**: Instructions for real-time communication tasks.
6. **UI Skill**: Instructions for UI design and development.

## How to Use
- Refer to the `agents` folder for high-level responsibilities and tools.
- Use the `skills` folder for detailed task instructions.
- Follow the `README.md` for setup and deployment guidelines.