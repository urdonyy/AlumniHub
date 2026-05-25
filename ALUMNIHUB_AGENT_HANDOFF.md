# AlumniHub Agent Handoff

## Project Snapshot
AlumniHub is a capstone social platform for alumni of PUP-ITECH. The app is built with Laravel Blade and Tailwind CSS, and it is currently around 30% complete. The product behaves like a mix of Facebook, LinkedIn, and Reddit:

- alumni profiles and verification
- communities and forum-style posts
- hearts/likes and comments on posts
- notifications
- real-time messaging
- admin and alumni/student roles

## Current Stack
- Backend: Laravel
- Frontend: Blade templates + Tailwind CSS + Vite
- Database: MySQL or another Laravel-supported SQL database
- Auth: Laravel session-based authentication
- Real-time: Laravel Reverb + Laravel Echo
- Mail: Laravel notifications / mailer
- Deployment target: one unified Laravel deployment, not split frontend/backend repos

## Main Architecture Decision
This project does not need a Next.js/FastAPI-style split repository setup.

The recommended structure is a Laravel monolith:
- Laravel handles authentication, registration, login, forgot password, role checks, and session cookies.
- Blade handles the UI.
- Tailwind handles styling.
- Reverb handles real-time messaging and live notifications.
- Laravel mailer/notifications handles email delivery.

If FastAPI is ever used later, it should only be for a specialized Python-only service, not for core authentication.

## Auth and User Roles
Authentication should stay inside Laravel.

Use Laravel for:
- registration
- login/logout
- forgot password
- password reset
- session management
- admin vs alumni/student authorization

Recommended approach:
- add a role column or is_admin flag to the users table
- protect admin routes with middleware
- show/hide Blade UI elements with auth checks

## Notifications and Messaging
Use Laravel for both notifications and messaging.

### Notifications
Laravel can send:
- in-app notifications
- email notifications
- database notifications

Likely implementation:
- notifications stored in the database
- unread notifications shown in the Blade layout
- admin actions trigger notifications to users

### Messaging
Use Laravel Reverb for real-time chat.

Suggested flow:
- save messages in the database
- broadcast message events through Reverb
- listen on the frontend with Laravel Echo
- update the chat UI without a full page reload

## Posts, Hearts, and Comments
The social interaction layer should also stay inside Laravel.

### Likes / Hearts
- users can heart a post
- one user should not be able to like the same post multiple times
- store post_id and user_id in a likes table
- toggle like/unlike behavior through a POST request or AJAX/Fetch

### Comments
- users can comment on posts
- comments belong to both a post and a user
- show comment counts and lists in Blade
- optionally update comments dynamically with Fetch or Livewire

## Recommended Database Tables
Core tables likely needed or already present:
- users
- posts
- comments
- likes
- messages
- notifications
- communities
- community_moderators
- community_rules
- flairs
- connections
- verification_documents
- profile_experiences
- profile_educations

Useful constraints:
- unique like per user per post
- foreign keys for post/user/message relationships
- timestamps on all interaction tables

## Practical Implementation Priority
If the agent helper is continuing development, the best order is:
1. finalize auth and role handling
2. finalize post likes and comments
3. finalize notifications
4. finalize messaging with Reverb
5. polish admin controls and verification flow
6. make sure Blade UI stays responsive and consistent with Tailwind

## Deployment Notes
This is a capstone project with low expected traffic, so a single Laravel deployment is enough.

Recommended production concerns:
- queue email/notification jobs if needed
- keep Reverb running as a background process
- use environment variables for secrets
- deploy the whole app together instead of splitting repositories

## Important Takeaway
The app does not need a separate frontend repo and backend repo.

Laravel Blade + Tailwind can handle the main UI and backend together, while Laravel Reverb and Laravel notifications handle the real-time and messaging pieces. FastAPI is not required for the current design.

## Short Summary for the Code Helper
Build AlumniHub as one Laravel app. Use Laravel auth for login/register/forgot password, Laravel notifications and mailer for Gmail/email and in-app alerts, Laravel Reverb for real-time messaging and notifications, and standard Laravel models/controllers/migrations for posts, likes, comments, and roles.
