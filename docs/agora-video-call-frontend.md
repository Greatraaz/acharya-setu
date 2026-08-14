# Agora Video Call — Frontend Implementation Guide

**Product:** Vedrix  
**Audience:** Mobile / app frontend developers  
**Base URL:** `{{baseUrl}} = https://vedrix.online/api/v1`  
**Auth:** `Authorization: Bearer {{userToken}}`  
**Headers:** `Accept: application/json`

Sessions now use **in-app Agora video**, not Google Meet / Zoom links.  
The backend issues the RTC token. The app only joins the channel.

There is **no join time window**. If `canJoinCall` is true, the token API returns 200 and the user can join immediately. Do **not** handle HTTP 422 for joining.

---

## 1. What changed (breaking)

| Before | After |
|---|---|
| `POST /get-agora-token/{channel}` was public | **Auth required** (401 without Bearer token) |
| Response was `{ status, token }` | Response now includes `app_id`, `channel`, `token`, `uid`, `peer`, `session` |
| Join with `uid = 0` | Join with **`uid` = logged-in user id** from the API |
| Open `meetingLink` in a browser | For Agora, **do not open `meetingLink`**. Join Agora in-app |

If the app still uses `uid: 0` or a hardcoded App ID, the call will fail.

---

## 2. APIs

All of these require a logged-in mentor or mentee token.

### 2.1 Get token by session id (use this)

**Mentee**

```
GET /mentee/sessions/{sessionId}/agora-token
```

**Mentor**

```
GET /mentor/sessions/{sessionId}/agora-token
```

**Shared (either role)**

```
GET /sessions/{sessionId}/agora-token
```

### 2.2 Legacy token by channel (still works)

```
GET  /get-agora-token/{channel}
POST /get-agora-token/{channel}
```

`{channel}` must be the session’s `channel` value, and the user must be the mentor or mentee of that session.  
Unknown channel → **404**.

---

## 3. Success response

HTTP **200**

```json
{
  "status": true,
  "statuscode": 200,
  "app_id": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "channel": "K7P2MQ9XAB",
  "token": "006xxxxxxxx...",
  "uid": 113,
  "role": "mentee",
  "expires_at": 1730000000,
  "call_log_id": 15,
  "peer": {
    "id": 20,
    "name": "Kiran Negi",
    "avatar_url": "https://..."
  },
  "session": {
    "id": 53,
    "title": "agora testing",
    "scheduled_at": "2026-08-14T12:30:00+05:30",
    "duration": 15,
    "status": "confirmed"
  }
}
```

### Fields to use for Agora join

| Field | Use |
|---|---|
| `app_id` | Agora App ID — **do not hardcode** |
| `channel` | Channel name |
| `token` | RTC token |
| `uid` | Integer user id — **must be this value, never 0** |
| `peer` | Other person’s name/avatar for the waiting UI |
| `session.duration` | Optional call timer |
| `expires_at` | Unix timestamp; refresh token if the call is still going |

---

## 4. Error responses

Do **not** expect HTTP 422 on join. Join is allowed whenever the session is still open (`pending`, `confirmed`, `upcoming`, `ongoing`).

### 401 Unauthorized
Missing or invalid Bearer token.

### 403 Forbidden
User is not the mentor/mentee of this session, or the session is completed / cancelled / no-show.

```json
{
  "status": false,
  "statuscode": 403,
  "message": "This session is no longer available to join."
}
```

### 404 Not found
Session id / channel does not exist, or does not belong to this user.

```json
{
  "status": false,
  "statuscode": 404,
  "message": "Session not found for this channel."
}
```

### 503 Agora not configured

```json
{
  "status": false,
  "statuscode": 503,
  "message": "Video calling is not configured. Add Agora App ID and Certificate in Admin → App Settings."
}
```

---

## 5. Session list changes

Existing session list APIs now include:

```json
{
  "id": 53,
  "status": "confirmed",
  "meetingLink": null,
  "channel": "K7P2MQ9XAB",
  "canJoinCall": true
}
```

| Field | Meaning |
|---|---|
| `canJoinCall` | Show the Join button when `true` |
| `channel` | Agora channel (optional; prefer session-id token API) |
| `meetingLink` | Zoom / Google Meet URL only. **Ignore for Agora** |

Join button rules:

- Show Join when `canJoinCall === true`
- Do **not** require `meetingLink`
- Do **not** open `meetingLink` in a WebView/browser for Agora sessions

---

## 6. App implementation checklist

1. Add Agora RTC SDK (Flutter: `agora_rtc_engine`, Android/iOS: official Agora RTC SDK).
2. On Join, call `GET /{role}/sessions/{id}/agora-token` with Bearer token.
3. Join with **exactly** `app_id`, `channel`, `token`, `uid` from the response.
4. Use communication / rtc profile (not live-broadcast audience).
5. Request camera + microphone permissions before join.
6. Publish local audio + video.
7. Subscribe when a remote user publishes; play remote video full screen, local as PIP.
8. Mute / unmute mic, camera on/off, End call.
9. On End: `leave()` the channel, dispose tracks, pop back to session detail.
10. Handle 401 / 403 / 404 / 503 in the UI. **Do not handle 422 for join.**
11. Remove hardcoded App ID / `uid = 0`.
12. Never ship the **App Certificate** in the app. Only `app_id` + token come from the API.

---

## 7. Example — Flutter (pseudo)

```dart
final res = await api.get('/mentee/sessions/$sessionId/agora-token');
final appId   = res['app_id'];
final channel = res['channel'];
final token   = res['token'];
final uid     = res['uid']; // int, e.g. 113

await engine.initialize(RtcEngineContext(appId: appId));
await engine.enableVideo();
await engine.joinChannel(
  token: token,
  channelId: channel,
  uid: uid,
  options: const ChannelMediaOptions(
    clientRoleType: ClientRoleType.clientRoleBroadcaster,
    channelProfile: ChannelProfileType.channelProfileCommunication,
  ),
);
```

---

## 8. Example — Android / iOS (concept)

```text
RtcEngine.joinChannel(
  token  = response.token,
  channelName = response.channel,
  optionalInfo = null,
  uid    = response.uid      // NOT 0
)
```

App ID is passed when creating `RtcEngine` (`RtcEngine.create(appId)`), using `response.app_id`.

---

## 9. UI notes

- Waiting state: show `peer.name` / `peer.avatar_url` until remote video arrives.
- Title: `session.title`.
- Timer: optional, based on join time vs `session.duration`.
- End call always leaves Agora even if the other person never joined.
- HTTPS / production build is required for camera on most devices.

Web reference (already live):  
`https://vedrix.online/sessions/{id}/call`

---

## 10. Do / Don’t

**Do**
- Send `Authorization: Bearer …` on every token call
- Use API `uid` and `app_id`
- Show Join from `canJoinCall`
- Join as soon as the user taps Join (no wait-until-start logic)

**Don’t**
- Open `meetingLink` for Agora
- Hardcode App ID or certificate
- Join with `uid = 0`
- Call token API without login
- Generate tokens on the device
- Block join with a 10-minute window or HTTP 422 handling

---

## 11. Quick test

1. Log in as mentee (or mentor) on a **confirmed** session.
2. Confirm list payload has `"canJoinCall": true`.
3. `GET /mentee/sessions/{id}/agora-token` → 200 with `app_id`, `channel`, `token`, `uid`.
4. Join Agora with those four values.
5. Log in as the other user on a second device and join the same session.
6. Both should see/hear each other.

If Agora returns `CAN_NOT_GET_GATEWAY_SERVER / invalid vendor key / can not find appid`, the App ID in **Admin → App Settings → Agora** is wrong. That is a backend/config issue, not an app bug — still use `app_id` from the token API, never a local constant.
