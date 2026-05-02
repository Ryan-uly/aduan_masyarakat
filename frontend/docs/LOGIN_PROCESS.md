# Proses Login - Flow Documentation

## Diagram Alur

```
User membuka /login
        │
        ▼
┌─────────────────────────────────────┐
│  routes/login/+page.svelte          │
│  - Tampilkan form login             │
│  - User input email & password      │
└─────────────────────────────────────┘
        │
        ▼ [User klik Login]
┌─────────────────────────────────────┐
│  handleLogin() function             │
│  - Set loading = true              │
└─────────────────────────────────────┘
        │
        ▼ [await loginRequest()]
┌─────────────────────────────────────┐
│  lib/api/auth/login.ts             │
│  - Panggil api.post()              │
│  - Endpoint: /login                │
└─────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────┐
│  lib/api/client.ts                 │
│  - POST ke http://localhost:8000  │
│  - Headers: Content-Type, Accept   │
│  - body: { email, password }       │
│  - Return: { token, user }        │
└─────────────────────────────────────┘
        │
        ▼ [Success]
┌─────────────────────────────────────┐
│  routes/login/+page.svelte         │
│  - auth.setToken(data.token)       │
│  - auth.setUser(data.user)        │
│  - goto('/dashboard')             │
└─────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────┐
│  routes/dashboard/+layout.svelte   │
│  - onMount: cek $auth.isAuthenticated│
│  - Kalau false → redirect ke /login│
│  - Kalau true → render Sidebar     │
└─────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────┐
│  lib/stores/auth.ts                │
│  - Simpan token ke localStorage    │
│  - Update state: isAuthenticated   │
│  - Trigger reactive update         │
└─────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────┐
│  routes/dashboard/+page.svelte     │
│  - Tampilkan dashboard             │
│  - Load complaints data            │
└─────────────────────────────────────┘
```

## File yang Terlibat

### 1. routes/login/+page.svelte
**Peran:** UI form login

```svelte
<script>
  // State
  let email = $state('');
  let password = $state('');
  let loading = $state(false);

  // Action
  async function handleLogin() {
    loading = true;
    const data = await loginRequest(email, password);
    auth.setToken(data.token);    // ← simpan token
    auth.setUser(data.user);      // ← simpan user
    goto('/dashboard');           // ← redirect
  }
</script>
```

### 2. lib/api/auth/login.ts
**Peran:** fungsi untuk call API login

```typescript
export async function loginRequest(email: string, password: string) {
  return api.post<AuthResponse>('/login', { email, password }, false);
}
//                                                             ↑
//                                               tanpa auth header (false)
```

### 3. lib/api/client.ts
**Peran:** HTTP client pusat

```typescript
class ApiClient {
  async post<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
    const res = await fetch(`${this.baseUrl}${endpoint}`, {
      method: 'POST',
      headers: this.getHeaders(includeAuth),  // ← auto add headers
      body: JSON.stringify(body)
    });
    return this.handleResponse<T>(res);
  }
}
```

### 4. lib/stores/auth.ts
**Peran:** State management

```typescript
export const auth = createAuthStore();
// Methods:
// - setToken(token) → simpan ke localStorage + update state
// - setUser(user)   → simpan data user
// - logout()        → hapus token + reset state
// - $auth.isAuthenticated → reaktif check login
```

### 5. routes/dashboard/+layout.svelte
**Peran:** Layout dengan auth guard

```svelte
<script>
  onMount(() => {
    if (!$auth.isAuthenticated) {
      goto('/login');  // ← proteksi halaman
    }
  });
</script>

{#if $auth.isAuthenticated}
  <Sidebar />
  <main>{@render children()}</main>
{/if}
```

### 6. lib/types/index.ts
**Peran:** Type definitions

```typescript
export interface AuthResponse {
  token: string;
  user: User;
}

export interface User {
  id: number;
  name: string;
  email: string;
}
```

## Config Environment

```env
# .env
VITE_API_URL=http://localhost:8000/api/v1
```

## Logout Flow

```
User klik Logout (di Sidebar)
        │
        ▼
auth.logout()  →  hapus localStorage token
        │
        ▼
goto('/login') →  redirect ke halaman login
```

## Keamanan

1. **Token disimpan di localStorage** - persistensi saat browser close
2. **Auth guard di layout** - proteksi akses langsung ke /dashboard
3. **API client auto-inject token** - semua request authenticated
4. **Logout hapus token** - memastikan session berakhir

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| Login gagal | API returns non-2xx | Check error message dari response |
| Redirect loop | Auth state sync issue | Refresh halaman |
| Token expired | 401 dari API | auto redirect ke login di client.ts |