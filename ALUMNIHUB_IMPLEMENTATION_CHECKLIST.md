# AlumniHub — Implementation Checklist (for Code Helper)

This is a practical checklist for implementing the remaining core features inside the existing Laravel + Blade + Tailwind monolith.

## 0) Non-goals / Constraints
- Do not split repos (no separate “frontend repo” needed).
- Do not move auth to FastAPI/JWT.
- Keep features aligned with capstone scope (simple, reliable, maintainable).

## 1) Roles (Admin vs Alumni/Student)
- Confirm user role storage strategy:
  - Option A: `users.role` enum-like string (`admin`, `alumni`)
  - Option B: `users.is_admin` boolean
- Add middleware:
  - `php artisan make:middleware EnsureAdmin`
  - Block `/admin/*` routes unless role is admin.
- Update route structure:
  - group admin routes under `Route::middleware(['auth', 'admin'])`

## 2) Posts + Authorization
- Ensure Post policy exists and is applied:
  - `app/Policies/PostPolicy.php`
  - `AuthServiceProvider` registers policies
- Confirm controller patterns:
  - store/update/destroy guarded by policy

## 3) Likes / Hearts (toggle)
**DB**
- Create `likes` table:
  - columns: `id`, `user_id`, `post_id`, timestamps
  - add unique index: (`user_id`, `post_id`)

**Models**
- `Post` relations:
  - `likes()` hasMany Like
  - `likedBy(auth user)` helper optional
- `User` relations:
  - `likes()` hasMany Like

**Routes**
- `POST /posts/{post}/like` (toggle)

**Controller**
- Toggle logic:
  - if like exists -> delete
  - else -> create
- Return strategy:
  - Blade form flow: redirect back
  - Fetch/AJAX flow: return JSON (`liked`, `likes_count`)

## 4) Comments
**DB**
- Create `comments` table:
  - columns: `id`, `user_id`, `post_id`, `body`, timestamps

**Models**
- `Post::comments()` hasMany
- `User::comments()` hasMany

**Routes**
- `POST /posts/{post}/comments`
- Optional: `DELETE /comments/{comment}` (policy controlled)

**UI (Blade)**
- show comment count
- list recent comments
- comment form under each post or in a post detail page

## 5) Notifications (in-app + optional email)
**DB notifications**
- Ensure notifications table exists (Laravel default notification system):
  - `php artisan notifications:table`
  - `php artisan migrate`

**Notification classes**
- Create notifications for key triggers (examples):
  - New comment on your post
  - New like on your post (optional)
  - Admin verified your account

**Delivery channels**
- Use `via()` returning `['database']` or `['database','mail']`.
- Add a dropdown/list in a shared Blade layout showing `unreadNotifications`.

## 6) Email (registration/forgot password + transactional)
- Use Laravel’s built-in password reset flow (don’t reinvent).
- Configure `.env` mail settings to a real provider for production-like behavior.
- For capstone demo, Mailtrap is fine.

## 7) Realtime Messaging (Laravel Reverb + Echo)
**Data model**
- Create `messages` table:
  - `sender_id`, `receiver_id`, `body`, `read_at` (or `is_read`), timestamps

**Broadcasting**
- Define a `MessageSent` event that implements `ShouldBroadcast`.
- Broadcast on a private channel (per-user):
  - `private-chat.{userId}` or similar
- Authorize private channels in `routes/channels.php`.

**Frontend listener**
- Use Laravel Echo to subscribe and append messages to the chat UI.

**Process/runtime**
- Ensure Reverb server is running in dev/prod.
- Keep payloads small (send IDs + minimal content; fetch more via HTTP if needed).

## 8) Queues (recommended if email/notifications feel slow)
- Use queue driver (database is fine for capstone).
- Run worker during development:
  - `php artisan queue:work`

## 9) Security + Guardrails
- Protect all write actions behind `auth` middleware.
- Apply policies for delete/edit of posts/comments.
- Validate request payloads via Form Requests.

## 10) Suggested Build Order (fastest value)
1. Likes toggle + count
2. Comments create + list
3. Database notifications + unread badge
4. Messaging table + basic MessageSent broadcasting
5. Admin-only tooling + verification notifications
