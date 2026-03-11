# Planning Poker

A professional Planning Poker web application built with **Laravel 12**, **Vue.js 3**, **Tailwind CSS**, and **Laravel Reverb** for real-time WebSocket communication.

## Features

- 🃏 **Configurable card decks** (Fibonacci by default: 0, 1, 2, 3, 5, 8, 13, 21, ?)
- ⚡ **Real-time voting** via WebSockets (Laravel Reverb)
- 🔒 **Vote reveal** with animated countdown
- 👥 **Presence channels** — see who's online
- 😊 **Animated emoji reactions**
- 🌙 **Dark / Light mode** toggle
- 🌍 **i18n** — English and Spanish
- 👤 **Profile management** with avatar upload
- 📱 **Fully responsive** — desktop, tablet, mobile

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.3, Laravel 12 |
| Auth | Laravel Sanctum (token-based) |
| WebSockets | Laravel Reverb |
| Frontend | Vue.js 3 + Vite |
| Styling | Tailwind CSS v4 |
| State | Pinia |
| Routing | Vue Router 4 |
| i18n | vue-i18n v9 |
| Database | PostgreSQL |
| Infrastructure | Google Cloud (Cloud Run + Cloud SQL) |

## Architecture

```
Controller → Service → Repository → Eloquent Model
```

All dependencies are injected via interfaces following SOLID principles.

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 16+

### Setup

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install --legacy-peer-deps

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database (create DB first, then run migrations)
php artisan migrate

# Build frontend assets
npm run build

# Create storage symlink
php artisan storage:link
```

### Development

```bash
# Run all services concurrently
composer dev
```

This starts:
- PHP development server (`php artisan serve`)
- Vite dev server (`npm run dev`)
- Queue worker
- Log watcher

### Docker

```bash
# Build and run with Docker Compose
docker compose up -d

# Run migrations inside container
docker compose exec app php artisan migrate
```

## API Reference

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout (auth required) |
| GET | `/api/me` | Get current user |

### Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/profile` | Get profile |
| POST | `/api/profile` | Update profile (with avatar upload) |

### Rooms
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/rooms` | List user's rooms |
| POST | `/api/rooms` | Create room |
| GET | `/api/rooms/{id}` | Get room details |
| POST | `/api/rooms/join/{code}` | Join room by code |
| POST | `/api/rooms/{room}/leave` | Leave room |
| PATCH | `/api/rooms/{room}/state` | Change room state (host only) |
| PATCH | `/api/rooms/{room}/toggle-emojis` | Toggle emoji blocking (host only) |

### Vote Sessions
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/rooms/{room}/sessions` | Start new session (host only) |
| GET | `/api/sessions/{session}` | Get session details |
| POST | `/api/sessions/{session}/vote` | Submit a vote |
| POST | `/api/sessions/{session}/reveal` | Reveal votes (host only) |

### Emojis
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/rooms/{room}/emojis` | Send emoji |

## WebSocket Events

All events are broadcast on the **presence channel** `room.{id}`:

| Event | Description |
|-------|-------------|
| `user.joined` | A user joined the room |
| `user.left` | A user left the room |
| `vote.submitted` | A vote was submitted (anonymous until reveal) |
| `reveal.started` | Host triggered reveal (includes votes + average) |
| `room.state_changed` | Room state updated |
| `emoji.sent` | An emoji was sent |

## Database Schema

```
users ─────────────┐
  id               │
  name             │
  email            │
  password         │
  avatar           │   room_users
  bio              │   ┌────────────────┐
                   └──►│ room_id        │
rooms ─────────────┐   │ user_id        │
  id               ├──►│ role           │
  name             │   │ is_online      │
  code (unique)    │   └────────────────┘
  host_id ─────────┘
  card_config (json)   vote_sessions
  state            ┌──►┌────────────────┐
  emojis_blocked   │   │ room_id        │
                   │   │ story_title    │   votes
                   │   │ status         ├──►┌──────────────┐
                   │   │ average        │   │ session_id   │
                   └───┤ revealed_at    │   │ user_id      │
                       └────────────────┘   │ value        │
                                            └──────────────┘
emojis
  room_id
  sender_id
  target_id (nullable)
  emoji
```

## Deployment on Google Cloud

See [docs/deployment-gcp.md](docs/deployment-gcp.md) for the full GCP deployment guide.
