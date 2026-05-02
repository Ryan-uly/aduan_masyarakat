# Error Handling Flow - Detail Documentation

## Table of Contents
1. [API Client Error Handling - lib/api/client.ts](#1-libapiclientts)
2. [API Service Error Propagation](#2-api-service-error-propagation)
3. [UI Error Display - Alert Component](#3-ui-error-display-alert-component)
4. [Specific Error Types & Handling](#4-specific-error-types--handling)

---

## 1. lib/api/client.ts

### Apa yang dilakukan:
Client menangani semua HTTP responses dan melemparkan error jika response bukan 2xx.

### Step-by-step:

```typescript
// lib/api/client.ts

class ApiClient {
  // ============ HANDLE RESPONSE ============
  private async handleResponse<T>(res: Response): Promise<T> {
    // STEP 1: Parse JSON dari response
    const data = await res.json();
    //   ↑ Baca body response sebagai JSON
    //   → Biasa berupa: { "message": "...", "errors": {...} }

    // STEP 2: Check jika response NOT OK (bukan 2xx)
    if (!res.ok) {
      //   ↑ res.ok = false untuk status:
      //   - 400 Bad Request
      //   - 401 Unauthorized
      //   - 403 Forbidden
      //   - 404 Not Found
      //   - 422 Validation Error
      //   - 500 Server Error
      //   - dll

      // STEP 3: Build error object
      const error: ErrorResponse = {
        message: data.message || 'Terjadi kesalahan'
        //   ↑ Ambi message dari API response
        //   → Fallback ke default message jika API tidak kasih message
      };

      // STEP 4: Throw error - akan di-catch oleh caller
      throw new Error(error.message);
      //   ↑ Error ini adalah JavaScript Error object
      //   → Akan ditangkap di try-catch block
    }

    // STEP 5: Return data jika success (res.ok = true)
    return data;
    //   → Data berupa ApiResponse<T>
    //   → Diproses oleh caller
  }

  // ============ GET REQUEST ============
  async get<T>(endpoint: string, includeAuth = true): Promise<T> {
    const res = await fetch(`${this.baseUrl}${endpoint}`, {
      method: 'GET',
      headers: this.getHeaders(includeAuth)
      //         ↑ Includes auth token jika needed
    });

    // Call handleResponse - akan throw jika error
    return this.handleResponse<T>(res);
    //   ↑ Jika success: return data
    //   ↑ Jika error: throw Error
  }

  // ============ POST REQUEST ============
  async post<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
    const res = await fetch(`${this.baseUrl}${endpoint}`, {
      method: 'POST',
      headers: this.getHeaders(includeAuth),
      body: JSON.stringify(body)
    });

    return this.handleResponse<T>(res);
    //   ↑ Error handling yang sama
  }

  // ============ ERROR TYPES DARI SERVER ============
  /*
    SERVER RESPONSE EXAMPLES:

    1. Validation Error (422):
    {
      "message": "The given data was invalid.",
      "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
      }
    }

    2. Authentication Error (401):
    {
      "message": "Invalid credentials"
    }

    3. Authorization Error (403):
    {
      "message": "You are not authorized to perform this action"
    }

    4. Not Found Error (404):
    {
      "message": "Resource not found"
    }

    5. Server Error (500):
    {
      "message": "Something went wrong"
    }
  */
}
```

---

## 2. API Service Error Propagation

### Bagaimana error dilempar dari service ke page:

```typescript
// lib/api/auth/login.ts

export async function loginRequest(email: string, password: string): Promise<AuthResponse> {
  // STEP 1: Panggil api.post()
  return api.post<AuthResponse>('/login', { email, password }, false);
  //                ↑ Ini akan throw Error jika response tidak OK

  // STEP 2: Jika error dari client.ts (non-2xx response)
  //   Error akan dilempar ke caller (page component)
  //   Tidak ada try-catch di sini karena ingin propagate error ke UI

  // STEP 3: Jika success
  //   Return AuthResponse { token, user }
}
```

```typescript
// lib/api/complaints/index.ts

export async function getComplaints() {
  try {
    // Try-catch di level service (opsional)
    return await api.get<ApiResponse<Complaint[]>>('/complaints');
  } catch (e) {
    // bisa logging di sini
    console.error('Get complaints failed:', e);
    // Re-throw agar page bisa handle
    throw e;
  }
}

export async function createComplaint(data: CreateComplaintRequest) {
  // Tidak perlu try-catch - propagate langsung
  return api.post<ApiResponse<Complaint>>('/complaints', data);
}
```

---

## 3. UI Error Display - Alert Component

### Alert Component:

```svelte
<!-- src/lib/components/ui/Alert.svelte -->

<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    type?: 'error' | 'success' | 'warning' | 'info';
    class?: string;
    children: Snippet;
  }

  let {
    type = 'error',
    class: className = '',
    children
  }: Props = $props();

  // STEP 1: Konfigurasi style berdasarkan type
  const typeConfig = {
    error: {
      bg: 'bg-red-100',
      text: 'text-red-600',
      icon: '⚠️'
    },
    success: {
      bg: 'bg-green-100',
      text: 'text-green-600',
      icon: '✓'
    },
    warning: {
      bg: 'bg-yellow-100',
      text: 'text-yellow-600',
      icon: '⚡'
    },
    info: {
      bg: 'bg-blue-100',
      text: 'text-blue-600',
      icon: 'ℹ️'
    }
  };

  // STEP 2: Get config berdasarkan type
  const config = $derived(typeConfig[type]);
  //   ↑ Derived - recalculate jika type berubah
</script>

<!-- STEP 3: Render dengan dynamic classes -->
<div class="rounded-lg p-4 {config.bg} {config.text} {className}">
  {@render children()}
  //  ↑ Content yang di-pass ke component
</div>
```

### Penggunaan di Page:

```svelte
<!-- routes/login/+page.svelte -->

<script>
  let error = $state('');

  async function handleLogin() {
    try {
      const data = await loginRequest(email, password);
      // ...
    } catch (e: any) {
      // STEP 1: Ambi error message
      error = e?.message || 'Login gagal';
      //   ↑ e.message dari Error yang dilempar client.ts
      //   ↑ Fallback jika e.message undefined
    }
  }
</script>

<!-- STEP 2: Tampilkan error dengan conditional -->
{#if error}
  <Alert type="error" class="mt-4">
    {error}
  </Alert>
{/if}
```

### Complete Error Flow di Page:

```svelte
<script>
  // State
  let error = $state('');
  let loading = $state(false);

  // Handler
  async function handleLogin() {
    // Reset error sebelum attempt
    error = '';
    loading = true;

    try {
      // Panggil API
      const data = await loginRequest(email, password);

      // Success: proses data
      auth.setToken(data.token);
      goto('/dashboard');

    } catch (e: any) {
      // ERROR: Simpan ke state
      // e adalah Error object dari client.ts
      error = e?.message || 'Terjadi kesalahan';

      // Optional: log untuk debugging
      console.error('Login error:', e);

    } finally {
      // Selalu reset loading
      loading = false;
    }
  }
</script>

<!-- Tampilkan -->
<form>
  {#if error}
    <Alert type="error" class="mb-4">
      {error}
    </Alert>
  {/if}

  <!-- Form fields -->
  <Button disabled={loading}>
    {loading ? 'Loading...' : 'Login'}
  </Button>
</form>
```

---

## 4. Specific Error Types & Handling

### A. Authentication Errors (401)

```typescript
// Di complaints/+page.svelte

async function fetchComplaints() {
  try {
    const { data } = await getComplaints();
    complaints = data;
  } catch (e: any) {
    // Check jika error karena tidak login
    if (e.message.includes('401') || e.message.includes('unauthenticated')) {
      // Logout dan redirect
      auth.logout();
      goto('/login');
    } else {
      // Error lain - tampilkan ke user
      error = e.message;
    }
  }
}
```

### B. Validation Errors (422)

```typescript
// Dari server:
// {
//   "message": "The given data was invalid.",
//   "errors": {
//     "email": ["The email must be a valid email address."],
//     "password": ["The password must be at least 8 characters."]
//   }
// }

// Di client.ts handleResponse sudah mengambil message utama
// Tapi bisa juga parse errors:

async function handleSubmit() {
  try {
    await createComplaint(newComplaint);
  } catch (e: any) {
    // Jika server return validation errors
    if (e.message.includes('invalid')) {
      // Bisa parse errors jika diperlukan
      // Tapi sekarang cukup tampilkan message
      error = 'Mohon periksa kembali data yang diisi';
    } else {
      error = e.message;
    }
  }
}
```

### C. Network Errors

```typescript
// Jika server tidak reachable (offline, timeout)

try {
  const data = await api.get('/complaints');
} catch (e: any) {
  if (e.message.includes('Failed to fetch') ||
      e.message.includes('NetworkError') ||
      e.message.includes('Network request failed')) {
    error = 'Tidak ada koneksi internet';
  } else {
    error = e.message;
  }
}
```

### D. Custom Error Handling di Setiap Page

```svelte
<!-- Different approach: Error boundary pattern -->

<script>
  // Di dashboard/+page.svelte

  let stats = $state({...});
  let recentComplaints = $state<Complaint[]>([]);
  let loading = $state(true);
  let error = $state('');

  onMount(async () => {
    try {
      const { data } = await getComplaints();
      // Set data...

    } catch (e: any) {
      // Different error messages untuk different cases
      switch (true) {
        case e.message.includes('401'):
          error = 'Sesi Anda telah berakhir. Silakan login ulang.';
          break;
        case e.message.includes('network'):
          error = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
          break;
        case e.message.includes('500'):
          error = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
          break;
        default:
          error = e.message;
      }
    } finally {
      loading = false;
    }
  });
</script>

{#if error}
  <Alert type="error">
    <strong>Error:</strong> {error}
  </Alert>
{/if}
```

---

## Error Flow Diagram

```
USER ACTION (submit form)
        │
        ▼
┌───────────────────────────────┐
│  Page handler function       │
│  try {                       │
│    await apiCall()          │
│  } catch (e) {              │
│    error = e.message        │
│  }                          │
└───────────────────────────────┘
        │
        ▼
┌───────────────────────────────┐
│  api/client.ts               │
│  fetch(...).then(res => {   │
│    if (!res.ok) {           │
│      throw Error(message)  │ ← Create Error
│    }                        │
│    return data              │
│  })                         │
└───────────────────────────────┘
        │
        ▼
┌───────────────────────────────┐
│  SERVER RESPONSE             │
│  - 2xx → return data         │
│  - 4xx/5xx → throw Error    │
└───────────────────────────────┘
        │
        ▼ (Error thrown)
┌───────────────────────────────┐
│  Page catch block            │
│  catch (e: any) {           │
│    error = e.message        │ ← Capture error
│  }                          │
└───────────────────────────────┘
        │
        ▼
┌───────────────────────────────┐
│  STATE UPDATE                │
│  error = "Error message"     │
│  loading = false            │
└───────────────────────────────┘
        │
        ▼ (Reactive update)
┌───────────────────────────────┐
│  UI RENDER                   │
│  {#if error}                 │
│    <Alert>{error}</Alert>    │ ← Display error
│  {/if}                       │
└───────────────────────────────┘
```

---

## Best Practices

### 1. Always Reset Error Before Request

```typescript
// ✅ BENAR
async function handleSubmit() {
  error = '';  // Reset terlebih dahulu
  loading = true;
  try {
    await apiCall();
  } catch (e: any) {
    error = e.message;
  }
}

// ❌ SALAH
async function handleSubmit() {
  loading = true;  // Error tidak di-reset
  try {
    await apiCall();  // Jika error, error state lama masih ada
  } catch (e: any) {
    error = e.message;
  }
}
```

### 2. Always Reset Loading in Finally

```typescript
// ✅ BENAR
async function handleSubmit() {
  loading = true;
  try {
    await apiCall();
  } catch (e) {
    // handle error
  } finally {
    loading = false;  // Selalu jalan, sukses atau error
  }
}

// ❌ SALAH
async function handleSubmit() {
  loading = true;
  try {
    await apiCall();
    loading = false;  // Hanya jalan jika success
  } catch (e) {
    // Tidak ada loading = false → button stuck disabled
  }
}
```

### 3. Provide User-Friendly Messages

```typescript
// ✅ BENAR - User friendly
catch (e: any) {
  if (e.message.includes('email')) {
    error = 'Email sudah terdaftar';
  } else if (e.message.includes('password')) {
    error = 'Password terlalu lemah';
  } else {
    error = 'Terjadi kesalahan. Silakan coba lagi.';
  }
}

// ❌ SALAH - Too technical
catch (e: any) {
  error = e.message;  // "SQLSTATE[23000]: Integrity constraint violation"
}
```

### 4. Use Error State for Different UI

```typescript
// Tampilkan loading, error, atau content
{#if loading}
  <LoadingSpinner />
{:else if error}
  <Alert type="error">{error}</Alert>
{:else}
  <Content />
{/if}
```

---

## Summary

| Layer | Responsibility |
|-------|----------------|
| `client.ts` | Convert HTTP errors ke Error, throw ke caller |
| `api/*.ts` | Propagate errors, optional logging |
| `page.svelte` | Catch errors, update error state, display UI |
| `Alert.svelte` | Display error dengan appropriate styling |

### Error Flow:

```
Server (4xx/5xx)
    ↓
client.ts → throw new Error(message)
    ↓
page catch → error = e.message
    ↓
UI re-render → <Alert>{error}</Alert>
    ↓
User melihat error message
```