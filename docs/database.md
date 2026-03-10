# Database Schema

Entities:

- Users
- Rooms
- RoomUsers
- VoteSessions
- Votes
- Emojis
- RoomStates

Relationships:

User → Rooms (host)
User → RoomUsers
Room → VoteSessions
VoteSession → Votes