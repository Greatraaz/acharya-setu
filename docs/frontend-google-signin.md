# Google Sign-In — mobile / frontend app guide

Use this to add **Continue with Google** in the Android (or iOS) app for Vedrix.

Email/OTP signup stays available. Google is an alternate path.

---

## 1. Overview

```
App: Google Sign-In SDK
   ↓  gets id_token
App: POST /api/v1/auth/google  { id_token, role?, accepted_terms? }
   ↓
Backend: verifies token with Web Client ID
   ↓
App: store Sanctum token + route by role / onboarding flags
```

| Platform | Google Cloud client |
|---|---|
| Website | **Web** client (already used by Laravel) |
| Android app | **Android** client (package + SHA-1) |
| Backend `.env` | **Web** `GOOGLE_CLIENT_ID` + `GOOGLE_CLIENT_SECRET` only |

The app requests an **ID token** using the **Web Client ID** as `serverClientId` / audience.  
That token is what the API verifies.

---

## 2. Prerequisites (from backend / DevOps)

Ask backend for:

1. **Web Client ID**  
   Example: `xxxxxxxxxxxx-xxxxxxxx.apps.googleusercontent.com`  
   Same value as `GOOGLE_CLIENT_ID` in Laravel `.env`.
2. **API base URL**  
   Example: `https://yourdomain.com/api/v1` or `http://127.0.0.1:8000/api/v1`
3. Confirm Google Auth is configured on the server (otherwise API returns `503`).

Create in Google Cloud (same Vedrix project):

1. **Android** OAuth client  
   - Package name (e.g. `com.vedrix.app`)  
   - SHA-1 of debug + release keystores
2. Keep the existing **Web** client for the backend

While the OAuth consent screen is in **Testing**, add every tester Gmail under **Test users**.

---

## 3. Android: Google Sign-In setup

### 3.1 Dependencies

Use Google Identity / Credential Manager (recommended) or the older Google Sign-In SDK.

You need:

- Android OAuth client → lets the device show the Google account picker  
- Web Client ID → used when requesting `idToken` for the server

### 3.2 Request an ID token

When configuring Sign-In, set:

```text
serverClientId / webClientId = WEB_CLIENT_ID  (from Laravel GOOGLE_CLIENT_ID)
```

Scopes needed:

- `openid`
- `email`
- `profile`

After success, read:

```text
idToken   ← send this to the API (required)
email     ← optional UI only
displayName / photoUrl ← optional UI only
```

Do **not** send the Android client secret (there isn’t one for native apps).  
Do **not** put `GOOGLE_CLIENT_SECRET` in the mobile app.

### 3.3 Debug SHA-1 (common blocker)

If Sign-In fails with `DEVELOPER_ERROR` / code `10`:

```bash
# Debug keystore example
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android
```

Add that SHA-1 to the Android OAuth client in Google Cloud Console.  
Also add the **release** SHA-1 before Play Store builds.

---

## 4. API: exchange Google token for app session

### Endpoint

```http
POST /api/v1/auth/google
Content-Type: application/json
Accept: application/json
```

No `Authorization` header on this call.

### Request body

| Field | Required | Notes |
|---|---|---|
| `id_token` | Yes* | Google ID token from Sign-In |
| `access_token` | Yes* | Alternative if you don’t have `id_token` |
| `role` | New users only | `mentee` \| `mentor` \| `expert` |
| `accepted_terms` | New users only | must be `true` / accepted |

\* One of `id_token` or `access_token` is required. Prefer `id_token`.

#### Login (existing Google / linked email account)

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

#### Signup (first time)

1. User picks role: mentee / mentor / expert  
2. User accepts Terms & Privacy  
3. Then call Google Sign-In and send:

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
  "role": "mentee",
  "accepted_terms": true
}
```

If role/terms are missing for a **new** account, API returns `422` with a clear message  
(e.g. choose role, or accept terms).

### Success response (`200` existing, `201` new)

```json
{
  "success": true,
  "status": true,
  "token": "1|xxxxxxxxxxxxxxxx",
  "user": {
    "id": 123,
    "name": "Rohit Sharma",
    "email": "rohit@gmail.com",
    "role": "mentee",
    "avatar_url": "https://...",
    "onboarding_completed": false,
    "onboarding_step": 0,
    "mentor_status": "approved",
    "auth_provider": "google",
    "is_active": true
  },
  "onboarding_completed": false,
  "can_use_platform": false,
  "can_skip_onboarding": true,
  "is_new": true
}
```

### Important response flags

| Field | Meaning |
|---|---|
| `token` | Sanctum bearer token — store securely and send on later API calls |
| `is_new` | `true` if account was just created |
| `onboarding_completed` | profile wizard finished? |
| `can_use_platform` | can access dashboard / sessions / booking? |
| `can_skip_onboarding` | Google user may leave onboarding for later (until gates require it) |
| `user.role` | `mentee` \| `mentor` \| `expert` |
| `user.mentor_status` | mentors/experts: `pending` \| `approved` \| `rejected` \| `suspended` |
| `user.auth_provider` | `google` for Google-only accounts |

### Error responses

| HTTP | When |
|---|---|
| `503` | Google not configured on server |
| `422` | Invalid/expired token, missing role/terms for signup, validation errors |
| `403` | Account disabled |

Example:

```json
{
  "success": false,
  "status": false,
  "message": "Choose Mentee, Mentor, or Expert, then continue with Google."
}
```

---

## 5. After login — where to navigate

Always branch on `user.role` first, then flags.

```
role === mentee
  onboarding_completed == false
      → mentee onboarding
      → show “Complete later” only if can_skip_onboarding == true
  onboarding_completed == true
      → mentee home / dashboard

role === mentor OR expert
  onboarding_completed == false AND mentor_status in [pending, rejected]
      → pending / waiting screen
      → allow starting onboarding OR skip later (if can_skip_onboarding)
  onboarding_completed == false AND mentor_status == approved
      → FORCE onboarding (no skip) — required before sessions
  onboarding_completed == true AND mentor_status == pending
      → pending approval screen (no dashboard)
  onboarding_completed == true AND mentor_status == approved
      → mentor/expert dashboard
  mentor_status == suspended
      → blocked / contact support
```

### Platform gate rule (critical)

If `can_use_platform == false`, **block**:

- booking sessions  
- dashboard actions  
- mentor requests  
- career services / mock interviews  
- mentor accepting sessions / going live  

Show a banner/CTA: **Complete profile** (or **Waiting for admin confirmation**).

Google mentors/experts may skip profile **before** approval.  
After admin approval, profile completion is **mandatory**.

---

## 6. Authenticated calls after Google login

Store `token`, then:

```http
Authorization: Bearer {token}
Accept: application/json
```

Useful endpoints:

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/api/v1/auth/me` | refresh user + onboarding state |
| `GET` | `/api/v1/mentee/config` | mentee flags (`onboarding_required`, `can_skip_onboarding`, `can_use_platform`) |
| `GET` | `/api/v1/mentee/onboarding/meta` | mentee onboarding |
| `POST` | `/api/v1/mentee/onboarding/skip` | skip later (Google mentee only when allowed) |
| `GET` | `/api/v1/mentor/onboarding/meta` | mentor/expert onboarding |
| `POST` | `/api/v1/mentor/onboarding/skip` | skip later (Google host only when allowed) |
| `POST` | `/api/v1/mentor/onboarding/submit` | finish mentor/expert profile |

If a gated route is hit too early, API returns `403` with a message like:

> Complete your profile before taking sessions or using the dashboard.

---

## 7. Suggested app UX flow

### Login screen
1. Email/password (existing)  
2. **Continue with Google**  
   - Existing users: no role picker needed  
   - If API says choose role → treat as signup and show role + terms

### Signup screen
1. Role cards: Mentee / Mentor / Expert  
2. Checkbox: Terms & Privacy  
3. Button: **Continue with Google**  
4. On success → save token → navigate using section 5

### “I’ll complete later”
Show only when `can_skip_onboarding == true`.  
Call the matching `/onboarding/skip` endpoint, then go to home / pending screen.  
Keep reminding until `can_use_platform == true`.

---

## 8. Pseudocode (Android)

```kotlin
// 1) User already chose role + accepted terms (signup)
//    or is tapping Google on login screen (existing)

val idToken = googleSignIn.getIdToken() // using WEB_CLIENT_ID as serverClientId

val body = mutableMapOf<String, Any>(
  "id_token" to idToken
)

if (isSignup) {
  body["role"] = selectedRole           // mentee | mentor | expert
  body["accepted_terms"] = true
}

val response = api.post("/auth/google", body)

if (response.success) {
  secureStorage.save("api_token", response.token)
  secureStorage.save("user", response.user)

  when {
    !response.can_use_platform && response.user.role in listOf("mentor", "expert")
      && response.user.mentor_status == "approved"
        -> openForcedOnboarding()

    !response.onboarding_completed && response.can_skip_onboarding
        -> openOnboardingOrSkipScreen()

    !response.can_use_platform
        -> openPendingOrProfileGate()

    else
        -> openHomeForRole(response.user.role)
  }
} else {
  showError(response.message)
}
```

---

## 9. Testing checklist

- [ ] Web Client ID configured on server; Android client has correct package + SHA-1  
- [ ] Test Gmail added if consent screen is Testing  
- [ ] New mentee Google signup with role + terms → `201`, `can_skip_onboarding: true`  
- [ ] New mentor/expert Google signup → `mentor_status: pending`, not listed publicly  
- [ ] Existing Google user login without role → `200`  
- [ ] Google-only account cannot login with email/password  
- [ ] After admin approval, incomplete mentor/expert is forced to finish profile  
- [ ] Booking / mentor dashboard blocked until `can_use_platform == true`  
- [ ] Token works on `GET /auth/me`

---

## 10. Common mistakes

1. Using **Android Client ID** as `serverClientId` → API rejects token (`aud` mismatch). Use **Web Client ID**.  
2. Forgetting SHA-1 → Google Sign-In fails before API is called.  
3. Calling Google signup without `role` / `accepted_terms` for new users.  
4. Letting users book sessions while `can_use_platform` is false.  
5. Putting `GOOGLE_CLIENT_SECRET` in the mobile app (never do this).  
6. Mixing `localhost` vs `127.0.0.1` only matters for **web** redirect; Android uses package/SHA-1 + API base URL.

---

## 11. Quick reference

```text
POST /api/v1/auth/google

Signup:  { id_token, role, accepted_terms: true }
Login:   { id_token }

Save:    token
Route:   by role + onboarding_completed + mentor_status + can_use_platform
```

Questions for backend: Web Client ID, API base URL, and whether Google env vars are live.
