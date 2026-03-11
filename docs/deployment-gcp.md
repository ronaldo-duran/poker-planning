# Google Cloud Deployment Guide

## Architecture

```
Internet → Cloud Load Balancer → Cloud Run (App) → Cloud SQL (PostgreSQL)
                                       ↕
                              Container Registry
```

## Prerequisites

- Google Cloud SDK installed (`gcloud`)
- Docker installed
- A GCP project created

## Services Used

| Service | Purpose |
|---------|---------|
| Cloud Run | Hosts the Laravel + Reverb app container |
| Cloud SQL (PostgreSQL 16) | Managed database |
| Artifact Registry | Docker image storage |
| Cloud Load Balancer | HTTPS termination |
| Secret Manager | Environment secrets |

---

## Step 1: Set up Google Cloud Project

```bash
gcloud config set project YOUR_PROJECT_ID
gcloud services enable run.googleapis.com sqladmin.googleapis.com artifactregistry.googleapis.com secretmanager.googleapis.com
```

## Step 2: Create Cloud SQL PostgreSQL Instance

```bash
gcloud sql instances create poker-planning-db \
  --database-version=POSTGRES_16 \
  --tier=db-f1-micro \
  --region=us-central1 \
  --root-password=YOUR_DB_PASSWORD

gcloud sql databases create poker_planning --instance=poker-planning-db
gcloud sql users create poker_user --instance=poker-planning-db --password=YOUR_USER_PASSWORD
```

## Step 3: Build and Push Docker Image

> **Important:** Vite inlines `VITE_REVERB_*` variables at **build time**. You must supply the
> correct values when building the Docker image so the SPA can connect to Reverb in production.

```bash
# Configure Docker for Artifact Registry
gcloud auth configure-docker us-central1-docker.pkg.dev

# Create repository
gcloud artifacts repositories create poker-planning \
  --repository-format=docker \
  --location=us-central1

# Build with VITE_REVERB_* build args (replace values with your production domain/ports)
docker build \
  --build-arg VITE_REVERB_APP_KEY=your-reverb-key \
  --build-arg VITE_REVERB_HOST=yourapp.example.com \
  --build-arg VITE_REVERB_PORT=443 \
  --build-arg VITE_REVERB_SCHEME=https \
  -t us-central1-docker.pkg.dev/YOUR_PROJECT_ID/poker-planning/app:latest .

docker push us-central1-docker.pkg.dev/YOUR_PROJECT_ID/poker-planning/app:latest
```

### Dockerfile build args for VITE variables

Add these `ARG`/`ENV` declarations to the frontend build stage in your `Dockerfile` so the Vite
build process can pick them up:

```dockerfile
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
```

## Step 4: Store Secrets in Secret Manager

```bash
echo -n "your-app-key" | gcloud secrets create APP_KEY --data-file=-
echo -n "your-db-password" | gcloud secrets create DB_PASSWORD --data-file=-
echo -n "your-reverb-secret" | gcloud secrets create REVERB_SECRET --data-file=-
```

## Step 5: Deploy to Cloud Run

```bash
gcloud run deploy poker-planning \
  --image=us-central1-docker.pkg.dev/YOUR_PROJECT_ID/poker-planning/app:latest \
  --platform=managed \
  --region=us-central1 \
  --allow-unauthenticated \
  --port=80 \
  --memory=1Gi \
  --cpu=1 \
  --min-instances=1 \
  --set-env-vars="APP_ENV=production,DB_CONNECTION=pgsql,DB_HOST=/cloudsql/YOUR_PROJECT_ID:us-central1:poker-planning-db,DB_DATABASE=poker_planning,DB_USERNAME=poker_user,BROADCAST_CONNECTION=reverb,QUEUE_CONNECTION=database" \
  --set-secrets="APP_KEY=APP_KEY:latest,DB_PASSWORD=DB_PASSWORD:latest,REVERB_APP_SECRET=REVERB_SECRET:latest" \
  --add-cloudsql-instances=YOUR_PROJECT_ID:us-central1:poker-planning-db
```

## Step 6: Run Migrations

```bash
gcloud run jobs create migrate \
  --image=us-central1-docker.pkg.dev/YOUR_PROJECT_ID/poker-planning/app:latest \
  --command="php" \
  --args="artisan,migrate,--force" \
  --region=us-central1 \
  --set-cloudsql-instances=YOUR_PROJECT_ID:us-central1:poker-planning-db

gcloud run jobs execute migrate --region=us-central1
```

## WebSockets (Laravel Reverb)

Reverb runs on port 8080 inside the container. Cloud Run supports WebSocket connections natively.

Ensure the Cloud Run service allows port 8080 traffic (via the Nginx proxy config already set up in `docker/nginx/default.conf`).

## File Storage

Images are stored in `/storage/app/public` within the container. For persistent storage across Cloud Run instances, mount a Cloud Storage FUSE volume or use a shared NFS. The application uses `/storage` as specified and stores only file paths in the database.

## Environment Variables Summary

| Variable | Description |
|----------|-------------|
| `APP_KEY` | Laravel application key |
| `APP_ENV` | `production` |
| `DB_HOST` | Cloud SQL socket path |
| `DB_DATABASE` | `poker_planning` |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password (via Secret Manager) |
| `BROADCAST_CONNECTION` | `reverb` |
| `REVERB_APP_KEY` | Reverb app key |
| `REVERB_APP_SECRET` | Reverb app secret |
| `REVERB_ALLOWED_ORIGINS` | Comma-separated allowed origins (e.g. `https://yourapp.com`) |
