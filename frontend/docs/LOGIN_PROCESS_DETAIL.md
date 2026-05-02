# Proses Login - Detail Documentation

## Table of Contents
1. [User Input - routes/login/+page.svelte](#1-routesloginpage-svelte)
2. [API Call - lib/api/auth/login.ts](#2-libapiauthlogints)
3. [HTTP Client - lib/api/client.ts](#3-libapiclientts)
4. [Auth Store - lib/stores/auth.ts](#4-libstoresauthts)
5. [Dashboard Layout - routes/dashboard/+layout.svelte](#5-routesdashboardlayout-svelte)
6. [Type Definitions - lib/types/index.ts](#6-libtypesindexts)

---

## 1. routes/login/+page.svelte

### Apa yang dilakukan file ini:
File ini bertanggung jawab untuk menampilkan UI form login dan menangani event saat user mengklik tombol login.

### Step-by-step:

```svelte
<script lang="ts">
  // STEP 1: Import dependencies
  import { loginRequest } from '$lib/api/auth/login';
  //   ↑ Import fungsi untuk call API login dari file terpisah
  //   → Tujuan: Decoupling, fungsi API terpisah dari UI

  import { auth } from '$lib/stores/auth';
  //   ↑ Import auth store untuk manage state authentication
  //   → Tujuan: Supaya bisa set token dan user setelah login berhasil

  import { goto } from '$app/navigation';
  //   ↑ Import fungsi routing dari SvelteKit
  //   → Tujuan: Redirect user setelah login berhasil

  import AuthLayout from '$lib/components/AuthLayout.svelte';
  //   ↑ Import komponen layout (bagian kiri: hero image, bagian kanan: form)
  //   → Tujuan: Konsisten dengan halaman register

  import { Button, Card, Input, Alert } from '$lib/components/ui';
  //   ↑ Import reusable UI components
  //   → Tujuan: Konsisten styling, mudah maintain

  // STEP 2: Declare state (reactive variables)
  let email = $state('');
  //   ↑ State untuk input email
  //   - $state adalah Svelte 5 rune untuk membuat reactive state
  //   - Bindable: bisa di-bind ke input field

  let password = $state('');
  //   ↑ State untuk input password

  let error = $state('');
  //   ↑ State untuk menampilkan pesan error
  //   - Empty string = tidak ada error

  let loading = $state(false);
  //   ↑ State untuk loading state
  //   - false = belum loading
  //   - true = sedang proses login (disable button, show "Loading...")

  // STEP 3: Handler function untuk proses login
  async function handleLogin() {
    // STEP 3a: Set loading state
    loading = true;
    //   ↑ Aktifkan loading, button akan disabled dan text berubah

    // STEP 3b: Reset error state
    error = '';
    //   ↑ Hapus error sebelumnya

    // STEP 3c: Try-catch block untuk handle success dan error
    try {
      // STEP 3d: Panggil API login
      const data = await loginRequest(email, password);
      //            ↑ Async function yang return AuthResponse
      //            → POST ke /login dengan {email, password}
      //
      //            Jika success: { token: "abc123", user: {id:1, name:"John", email:"..."} }
      //            Jika error: throw new Error(message)

      // STEP 3e: Simpan token ke auth store
      auth.setToken(data.token);
      //   ↑ calling method dari auth store
      //   → Implementation di auth.ts:
      //      setToken: (token: string) => {
      //        localStorage.setItem('token', token);  // Simpan ke browser storage
      //        update((s) => ({ ...s, token, isAuthenticated: true }));  // Update state
      //      }

      // STEP 3f: Simpan data user ke auth store
      auth.setUser(data.user);
      //   ↑ calling method dari auth store
      //   → Implementation:
      //      setUser: (user: User) => update((s) => ({ ...s, user, isLoading: false }));

      // STEP 3g: Redirect ke halaman dashboard
      goto('/dashboard');
      //   ↑ Gunakan SvelteKit navigation
      //   → Akan dicek oleh dashboard layout apakah sudah authenticated

    } catch (e: any) {
      // STEP 3h: Handle error
      error = e?.message || 'Login gagal';
      //   ↑ Ambil message dari error object
      //   → Fallback ke 'Login gagal' jika tidak ada message

    } finally {
      // STEP 3i: Reset loading state (selalu dijalankan, sukses atau error)
      loading = false;
      //   ↑ Nonaktifkan loading, button kembali enable
    }
  }
</script>

<!-- UI TEMPLATE -->
<AuthLayout>
  <!-- Bagian kanan form -->
  <Card class="w-full max-w-md">

    <!-- Header -->
    <div class="text-center">
      <h2 class="text-2xl font-bold text-gray-800">Login</h2>
      <p class="mt-2 text-gray-500">Masuk ke akun kamu</p>
    </div>

    <!-- Error Alert -muncul jika ada error -->
    {#if error}
      <Alert type="error" class="mt-4">{error}</Alert>
    <!--       ↑ Conditional rendering: hanya render jika error tidak empty string -->
    {/if}

    <!-- Form -->
    <form onsubmit={(e) => { e.preventDefault(); handleLogin(); }} class="mt-6 space-y-4">
    <!--                ↑ preventDefault: 不要 refresh halaman saat submit -->
    <!--                ↑ Panggil handleLogin() saat form disubmit -->

      <!-- Email Input -->
      <Input
        type="email"                    ← Tipe input untuk email validation
        placeholder="Email"             ← Placeholder text
        bind:value={email}              ← Two-way binding: input ↔ state email
        name="email"                    ← Nama field untuk accessibility
        required                        ← HTML5 validation: wajib diisi
      />

      <!-- Password Input -->
      <Input
        type="password"
        placeholder="Password"
        bind:value={password}
        name="password"
        required
      />

      <!-- Submit Button -->
      <Button type="submit" class="w-full" disabled={loading}>
        {loading ? 'Loading...' : 'Login'}
        <!--   ↑ conditional text: ganti saat loading -->
      </Button>
    </form>

    <!-- Link ke register -->
    <p class="mt-6 text-center text-sm text-gray-500">
      Belum punya akun?
      <a href="/register" class="font-semibold text-indigo-600"> Register </a>
    </p>
  </Card>
</AuthLayout>
```

---

## 2. lib/api/auth/login.ts

### Apa yang dilakukan file ini:
File ini adalah "service layer" yang menjembatani antara UI (page) dan HTTP client. Menyediakan fungsi spesifik untuk login.

### Step-by-step:

```typescript
// lib/api/auth/login.ts

// STEP 1: Import dependencies
import { api } from '$lib/api/client';
//   ↑ Import instance dari ApiClient class
//   → Semua HTTP calls harus lewat ini
//   → Benefit: centralized config, auth headers, error handling

import type { AuthResponse } from '$lib/types';
//   ↑ Import TypeScript interface untuk response
//   → Type safety: tahu struktur data yang dikembalikan

// STEP 2: Export fungsi untuk digunakan di page
export async function loginRequest(email: string, password: string): Promise<AuthResponse> {
  //                    ↑ Parameter yang dibutuhkan
  //                    ↑ Return type: AuthResponse
  //
  // STEP 3: Panggil api.post()
  //   - endpoint: '/login'
  //   - body: { email, password }
  //   - includeAuth: false (belum ada token, jadi tidak perlu auth header)
  //
  return api.post<AuthResponse>('/login', { email, password }, false);
  //                        ↑            ↑                    ↑
  //                   endpoint      request body       includeAuth flag
  //
  // STEP 4: Return value
  //   - Success: Promise<AuthResponse> = { token: string, user: User }
  //   - Error: Akan throw Error dari client.ts handleResponse
}
```

### Kenapa terpisah dari page?
- **Single Responsibility**: Page hanya handle UI, API logic terpisah
- **Reusable**: Bisa dipanggil dari tempat lain jika perlu
- **Testable**: Lebih mudah unit test fungsi ini
- **Maintainable**: Perubahan API endpoint cukup di satu tempat

---

## 3. lib/api/client.ts

### Apa yang dilakukan file ini:
Ini adalah **HTTP Client Wrapper** - semua HTTP requests ke backend lewat file ini. Mirip dengan Axios di React atau HTTP Client di Laravel.

### Step-by-step:

```typescript
// lib/api/client.ts

import type { ApiResponse, ErrorResponse } from '$lib/types';

// STEP 1: Define class
class ApiClient {
  // STEP 2: Private properties
  private baseUrl: string;

  // STEP 3: Constructor - initialize base URL
  constructor() {
    // Ambil dari environment variable
    // Fallback ke localhost jika tidak ada
    this.baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
    //               ↑ dari .env file
    //               ↑ otomatis replaced oleh Vite saat build
  }

  // STEP 4: Private method - get token dari localStorage
  private getToken(): string | null {
    // Penting: hanya jalan di browser (typeof window check)
    if (typeof window === 'undefined') return null;
    return localStorage.getItem('token');
    //   ↑ Ambil token yang disimpa saat login
  }

  // STEP 5: Private method - bangun headers
  private getHeaders(includeAuth = true): HeadersInit {
    //   ↑ Parameteropsional, default true
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      //   ↑ Untuk POST/PUT body
      Accept: 'application/json'
      //   ↑ Minta response dalam JSON
    };

    // Jika includeAuth true dan ada token
    if (includeAuth) {
      const token = this.getToken();
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
        //   ↑ Format: Bearer token_for_user
        //   → Digunakan untuk API yang butuh authentication
      }
    }

    return headers;
  }

  // STEP 6: Private method - handle response
  private async handleResponse<T>(res: Response): Promise<T> {
    // STEP 6a: Parse JSON response
    const data = await res.json();
    //   ↑ Semua response di-parse sebagai JSON

    // STEP 6b: Check jika response tidak OK (bukan 2xx)
    if (!res.ok) {
      // Buat error response
      const error: ErrorResponse = {
        message: data.message || 'Terjadi kesalahan'
        //   ↑ Ambi message dari API response
        //   → Fallback jika API tidak punya message
      };
      // Throw error - akan di-catch di try-catch
      throw new Error(error.message);
    }

    // STEP 6c: Return data jika OK
    return data;
  }

  // STEP 7: Public method - GET request
  async get<T>(endpoint: string, includeAuth = true): Promise<T> {
    // STEP 7a: Build full URL
    const url = `${this.baseUrl}${endpoint}`;
    //   → contoh: http://localhost:8000/api/v1/complaints

    // STEP 7b: Fetch dengan GET method
    const res = await fetch(url, {
      method: 'GET',
      headers: this.getHeaders(includeAuth)
      //          ↑ Auto-build headers dengan/without auth
    });

    // STEP 7c: Process response
    return this.handleResponse<T>(res);
  }

  // STEP 8: Public method - POST request
  async post<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
    // STEP 8a: Build full URL
    const url = `${this.baseUrl}${endpoint}`;

    // STEP 8b: Fetch dengan POST method
    const res = await fetch(url, {
      method: 'POST',
      headers: this.getHeaders(includeAuth),
      //          ↑ Include auth jika needed
      body: JSON.stringify(body)
      //          ↑ Convert object ke JSON string
    });

    // STEP 8c: Process response
    return this.handleResponse<T>(res);
  }

  // STEP 9: Public method - PUT request
  async put<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
    // Sama seperti POST tapi method: 'PUT'
    const url = `${this.baseUrl}${endpoint}`;
    const res = await fetch(url, {
      method: 'PUT',
      headers: this.getHeaders(includeAuth),
      body: JSON.stringify(body)
    });
    return this.handleResponse<T>(res);
  }

  // STEP 10: Public method - DELETE request
  async delete<T>(endpoint: string, includeAuth = true): Promise<T> {
    // Sama seperti GET tapi method: 'DELETE'
    const url = `${this.baseUrl}${endpoint}`;
    const res = await fetch(url, {
      method: 'DELETE',
      headers: this.getHeaders(includeAuth)
    });
    return this.handleResponse<T>(res);
  }

  // STEP 11: Helper methods
  isAuthenticated(): boolean {
    return !!this.getToken();
    //   ↑ Boolean: apakah ada token
  }

  clearToken(): void {
    localStorage.removeItem('token');
    //   ↑ Hapus token (untuk logout)
  }
}

// STEP 12: Export singleton instance
export const api = new ApiClient();
//   ↑ Buat instance sekali, reuse everywhere
//   → Tidak perlu import class, cukup import api
```

---

## 4. lib/stores/auth.ts

### Apa yang dilakukan file ini:
File ini adalah **state management** untuk authentication. Menggunakan Svelte store untuk membuat reactive state yang bisa diakses dari mana saja.

### Step-by-step:

```typescript
// lib/stores/auth.ts

import { writable } from 'svelte/store';
//   ↑ Import fungsi untuk membuat writable store
//   → Writable = bisa diubah (berbeda dengan readable)

import type { User } from '$lib/types';

// STEP 1: Define state interface
interface AuthState {
  token: string | null;           // Token dari API
  user: User | null;               // Data user dari API
  isAuthenticated: boolean;       // Computed: !!token
  isLoading: boolean;             // Untuk handle loading state
}

// STEP 2: Function untuk create store
function createAuthStore() {
  // STEP 2a: Function untuk get initial state dari localStorage
  const getInitialState = (): AuthState => ({
    // Ambi token dari localStorage jika ada
    token: typeof window !== 'undefined' ? localStorage.getItem('token') : null,
    // ↑ typeof window check untuk SSR compatibility
    // → return null jika di server-side

    user: null,
    //   ↑ Belum ada data user, akan di-set setelah login

    isAuthenticated: !!localStorage.getItem('token'),
    //   ↑ Boolean: ada token atau tidak
    //   → !! untuk convert string ke boolean

    isLoading: true
    //   ↑ Default true, karena kita perlu cek auth status
  });

  // STEP 2b: Create writable store
  const { subscribe, set, update } = writable<AuthState>(getInitialState());

  // STEP 2c: Return object dengan methods
  return {
    // Subscribe: untuk menggunakan store di komponen
    //   Usage: $auth.isAuthenticated

    // Set user data
    setUser: (user: User) => update((s) => ({
      ...s,
      user,
      isLoading: false
    })),
    //   ↑ Parameters: user object dari API response
    //   → Update state dengan user baru
    //   → Set isLoading ke false

    // Set token
    setToken: (token: string) => {
      // STEP 2d: Simpan ke localStorage (browser)
      if (typeof window !== 'undefined') {
        localStorage.setItem('token', token);
        //   ↑ Simpan token agar persist saat refresh page
      }
      // STEP 2e: Update store state
      update((s) => ({
        ...s,
        token,
        isAuthenticated: true
        //   ↑ Auto-set authenticated = true saat ada token
      }));
    },

    // Logout - hapus semua
    logout: () => {
      // STEP 2f: Hapus dari localStorage
      if (typeof window !== 'undefined') {
        localStorage.removeItem('token');
        //   ↑ Hapus token dari browser storage
      }
      // STEP 2g: Reset state ke initial
      set({
        token: null,
        user: null,
        isAuthenticated: false,
        isLoading: false
      });
      //   → isAuthenticated = false
    },

    // Set loading state
    setLoading: (isLoading: boolean) => update((s) => ({
      ...s,
      isLoading
    }))
  };
}

// STEP 3: Export store instance
export const auth = createAuthStore();

// STEP 4: Cara penggunaan di komponen
/*
  import { auth } from '$lib/stores/auth';

  // Di template:
  {#if $auth.isAuthenticated}
    Halo, {$auth.user?.name}
  {/if}

  // Di script:
  auth.setToken('token_from_api');
  auth.setUser(user_data);
  auth.logout();
*/
```

### Kenapa perlu store? Alternatif yang salah:

```typescript
// ❌ CARA YANG SALAH: Pakai localStorage langsung
const token = localStorage.getItem('token');
if (!token) goto('/login');
// Masalah: tidak reaktif, UI tidak update saat token berubah

// ✅ CARA YANG BENAR: Pakai store
import { auth } from '$lib/stores/auth';
// UI akan reaktif update saat $auth.isAuthenticated berubah
```

---

## 5. routes/dashboard/+layout.svelte

### Apa yang dilakukan file ini:
File ini adalah **layout** untuk semua halaman di bawah `/dashboard`. Setiap route di `/dashboard/*` akan menggunakan layout ini. Berfungsi sebagai **auth guard** - memastikan hanya user yang login yang bisa akses.

### Step-by-step:

```svelte
<script lang="ts">
  // STEP 1: Import dependencies
  import { onMount } from 'svelte';
  //   ↑ Lifecycle hook - dijalankan saat komponen mount

  import { goto } from '$app/navigation';
  //   ↑ Untuk redirect jika tidak authenticated

  import { auth } from '$lib/stores/auth';
  //   ↑ Import auth store untuk cek status login

  import Sidebar from '$lib/components/Sidebar.svelte';
  //   ↑ Import sidebar component

  // STEP 2: Props - children adalah konten halaman
  let { children } = $props();
  //   ↑ Dari Svelte 5, $props() untuk menerima children

  // STEP 3: onMount - jalankan saat komponen mount
  onMount(() => {
    // STEP 3a: Cek apakah user sudah login
    // $auth adalah store subscription - reaktif
    if (!$auth.isAuthenticated) {
      // STEP 3b: Jika tidak login, redirect ke login page
      goto('/login');
      //   → User akan diarahkan ke /login
      //   → Tidak bisa akses dashboard tanpa login
    }
  });
</script>

<!-- STEP 4: Render sesuai kondisi auth -->

{#if $auth.isAuthenticated}
<!--    ↑ Hanya render jika authenticated -->

  <!-- STEP 5: Wrapper dengan padding untuk sidebar -->
  <div class="pl-64">
  <!--    ↑ Padding left 16rem (64) untuk space sidebar -->

    <!-- STEP 6: Include sidebar -->
    <Sidebar />
    <!--    ↑ Sidebar fixed di left, ini untuk offset content -->

    <!-- STEP 7: Main content area -->
    <main class="min-h-screen bg-gray-50 p-6">
    <!--    ↑ Background gray, padding 1.5rem -->
    <!--    ↑ min-h-screen: setidaknya setinggi layar -->

      <!-- STEP 8: Render children (halaman spesifik) -->
      {@render children()}
      <!--    ↑ Render konten dari /dashboard/+page.svelte dll -->
    </main>
  </div>

{/if}
```

### Kenapa perlu layout + auth check?

```
Tanpa layout:
- User bisa akses langsung /dashboard via URL
- Harus cek auth di setiap halaman

Dengan layout:
- Cek sekali di layout, apply ke semua halaman di bawahnya
- Consistent protection
- code reuse
```

---

## 6. lib/types/index.ts

### Apa yang dilakukan file ini:
File ini mendefinisikan semua **TypeScript interfaces** - kontrak data yang digunakan di seluruh aplikasi. Ini memastikan type safety dan autocomplete di IDE.

### Step-by-step:

```typescript
// lib/types/index.ts

// ============ USER ============

// STEP 1: Define User interface
export interface User {
  id: number;
  //   ↑ ID user dari database

  name: string;
  //   ↑ Nama user

  email: string;
  //   ↑ Email user (unique)

  created_at?: string;
  //   ↑ Optional: timestamp creation
  //   ↑ ?: berarti property tidak wajib

  updated_at?: string;
  //   ↑ Optional: timestamp update
}

// ============ COMPLAINT IMAGES ============

// STEP 2: Define ComplaintImage interface
export interface ComplaintImage {
  id: number;
  //   ↑ ID gambar

  complaint_id: number;
  //   ↑ ID parent complaint

  image_path: string;
  //   ↑ Path gambar di storage (contoh: "complaints/abc.jpg")

  created_at?: string;
}

// ============ COMPLAINT STATUS ============

// STEP 3: Define ComplaintStatus type
export type ComplaintStatus = 'pending' | 'process' | 'completed' | 'rejected';
//   ↑ Literal type - hanya boleh salah satu dari 4 nilai
//   → Lebih aman dari string biasa

// ============ COMPLAINT ============

// STEP 4: Define Complaint interface
export interface Complaint {
  id: number;
  user_id: number;
  title: string;
  description: string;
  status: ComplaintStatus;
  //   ↑ Pakai type di atas, bukan string

  images?: ComplaintImage[];
  //   ↑ Optional: array of images
  //   ↑ ? = boleh null/undefined

  created_at: string;
  updated_at?: string;
}

// ============ API RESPONSE ============

// STEP 5: Define ApiResponse generic interface
export interface ApiResponse<T> {
  data: T;
  //   ↑ Data utama (bisa array atau object)

  message?: string;
  //   ↑ Optional message dari API

  meta?: {
    //   ↑ Optional pagination info
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

// ============ AUTH RESPONSE ============

// STEP 6: Define AuthResponse interface
export interface AuthResponse {
  token: string;
  //   ↑ JWT token dari Laravel

  user: User;
  //   ↑ Data user yang login
}

// ============ REQUEST BODIES ============

// STEP 7: Define LoginRequest
export interface LoginRequest {
  email: string;
  password: string;
}

// STEP 8: Define RegisterRequest
export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

// STEP 9: Define CreateComplaintRequest
export interface CreateComplaintRequest {
  title: string;
  description: string;
  images?: File[];
  //   ↑ Optional: array File untuk upload
}

// ============ ERROR RESPONSE ============

// STEP 10: Define ErrorResponse
export interface ErrorResponse {
  message: string;
  //   ↑ Error message

  errors?: Record<string, string[]>;
  //   ↑ Optional: validation errors (Laravel style)
  //   → contoh: { "email": ["The email has already been taken."] }
}
```

### Cara penggunaan di code:

```typescript
// ✅ BENAR: Import dan gunakan interface
import type { Complaint, ApiResponse, ComplaintStatus } from '$lib/types';

// Gunakan sebagai type
let complaints = $state<Complaint[]>([]);
//   ↑ Array of Complaint - IDE tau struktur masing-masing

async function getComplaints(): Promise<ApiResponse<Complaint[]>> {
  // ↑ Return type - tahu punya data dan meta pagination
}

// Switch berdasarkan status type
function getBadge(status: ComplaintStatus) {
  //   ↑ IDE autocomplete: pending, process, completed, rejected
  switch (status) {
    case 'pending': return 'yellow';
    // ...
  }
}

// ❌ SALAH: Pakai string atau any
let data: any;              // ❌ Tidak aman
let status: string = 'foo'; // ❌ IDE tidak tahu ini bukan status valid
```

---

## Summary

| File | Responsibility |
|------|----------------|
| `login/+page.svelte` | UI form + trigger login action |
| `login.ts` | Service layer - call API |
| `client.ts` | HTTP client - handle requests |
| `auth.ts` | State management - store token/user |
| `+layout.svelte` | Auth guard + page wrapper |
| `types/index.ts` | Type definitions |

### Flow lengkap:

```
User input → loginRequest() → api.post() → server
                                            ↓
                                      return token+user
                                            ↓
auth.setToken() → auth.setUser() → goto('/dashboard')
                                            ↓
+layout.svelte → cek $auth.isAuthenticated → render Sidebar
```

---

## Additional: Register Process (Sama dengan Login)

Register mengikuti flow yang sama, bedanya:
1. Endpoint: `/register` bukan `/login`
2. Body: `{ name, email, password, password_confirmation }`
3. IncludeAuth: `false` (belum login)
4. Redirect: ke `/dashboard` jika ada token, atau `/login` jika perlu verifikasi email

Lihat `lib/api/auth/register.ts` dan `routes/register/+page.svelte` untuk detail.