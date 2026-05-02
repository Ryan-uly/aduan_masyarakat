# Logout Flow - Detail Documentation

## Table of Contents
1. [User Click - Sidebar.svelte](#1-sidebar-svelte)
2. [Auth Store - lib/stores/auth.ts](#2-libstoresauthts)
3. [Redirection - routes/login/+page.svelte](#3-routesloginpage-svelte)

---

## 1. Sidebar.svelte

### Apa yang dilakukan file ini:
Tombol logout ada di sidebar. Ketika diklik, akan memanggil fungsi logout dari auth store.

### Step-by-step:

```svelte
<!-- src/lib/components/Sidebar.svelte -->

<script lang="ts">
  // ============ STEP 1: IMPORTS ============
  import { page } from '$app/stores';
  //   ↑ Import page store untuk akses current URL
  //   → Digunakan untuk highlight menu yang aktif

  import { auth } from '$lib/stores/auth';
  //   ↑ Import auth store
  //   → Untuk akses .logout() method

  import { goto } from '$app/navigation';
  //   ↑ Import navigation function
  //   → Untuk redirect setelah logout

  // ============ STEP 2: MENU ITEMS ============
  interface MenuItem {
    label: string;
    href: string;
    icon: string;
  }

  const menuItems: MenuItem[] = [
    // Array of menu items configuration
    { label: 'Dashboard', href: '/dashboard', icon: 'home' },
    { label: 'Aduan Saya', href: '/dashboard/complaints', icon: 'document' },
    { label: 'Profile', href: '/dashboard/profile', icon: 'user' },
    { label: 'Pengaturan', href: '/dashboard/settings', icon: 'cog' }
  ];

  // ============ STEP 3: LOGOUT FUNCTION ============
  function handleLogout() {
    // STEP 3a: Panggil logout dari auth store
    auth.logout();
    //   → Ini akan:
    //   1. Hapus token dari localStorage
    //   2. Reset auth state (token: null, user: null, isAuthenticated: false)
    //   3. Trigger reactive update pada semua $auth subscribers

    // STEP 3b: Redirect ke halaman login
    goto('/login');
    //   → Navigasi ke /login
    //   → Login layout akan cek $auth.isAuthenticated
    //   → Karena false, akan render form login (bukan redirect)
  }

  // ============ STEP 4: COLLAPSE STATE ============
  let isCollapsed = $state(false);
  //   ↑ State untuk toggle sidebar collapsed/expanded
  //   → false = expanded (lebar), true = collapsed (sempit)
</script>

<!-- ============ UI TEMPLATE ============ -->

<aside class="fixed left-0 top-0 h-screen ...">
  <!-- Fixed sidebar - selalu visible -->

  <div class="flex h-full flex-col">

    <!-- Logo Section -->
    <div class="flex h-16 items-center justify-between border-b px-4">
      {#if !isCollapsed}
        <span class="text-lg font-bold text-indigo-600">AduanMas</span>
      {/if}

      <!-- Collapse Toggle Button -->
      <button onclick={() => (isCollapsed = !isCollapsed)} ...>
        <!-- Toggle isCollapsed state -->
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 space-y-1 p-3">
      {#each menuItems as item}
        {@const isActive = $page.url.pathname === item.href ||
                         $page.url.pathname.startsWith(item.href + '/')}
        //  ↑ Cek apakah current URL match dengan menu item
        //  → startsWith untuk handle sub-routes
        //  → /dashboard/complaints starts with /dashboard

        <a href={item.href}
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition
                  {isActive ? 'bg-indigo-50 text-indigo-600'
                            : 'text-gray-600 hover:bg-gray-50'}">
          // Conditional class untuk active state

          <!-- Icon (berbeda untuk setiap menu) -->
          {#if item.icon === 'home'}
            <svg>...</svg>
          {:else if item.icon === 'document'}
            <svg>...</svg>
          {:else if item.icon === 'user'}
            <svg>...</svg>
          {:else if item.icon === 'cog'}
            <svg>...</svg>
          {/if}

          <!-- Label (hide jika collapsed) -->
          {#if !isCollapsed}
            <span class="text-sm font-medium">{item.label}</span>
          {/if}
        </a>
      {/each}
    </nav>

    <!-- Logout Section (TERAKHIR) -->
    <div class="border-t p-3">
      // border-top untuk separation

      <button
        onclick={handleLogout}
        //         ↑ Panggil fungsi saat diklik

        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5
               text-red-600 transition hover:bg-red-50"
        //  ↑ Styling: red text, red hover background
      >
        <!-- Logout Icon -->
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          // Icon: arrow keluar dari box
        </svg>

        <!-- Logout Label (hide jika collapsed) -->
        {#if !isCollapsed}
          <span class="text-sm font-medium">Logout</span>
        {/if}
      </button>
    </div>

  </div>
</aside>
```

---

## 2. lib/stores/auth.ts

### Apa yang dilakukan saat logout:

```typescript
// lib/stores/auth.ts

// ============ LOGOUT METHOD ============
function createAuthStore() {
  // ... (init code)

  return {
    // ... (other methods)

    // ============ LOGOUT ============
    logout: () => {
      // STEP 1: Hapus token dari localStorage (browser storage)
      if (typeof window !== 'undefined') {
        localStorage.removeItem('token');
        //   ↑ Hapus 'token' key dari browser
        //   → Token tidak persist setelah browser close
        //   → User tidak bisa login tanpa token baru
      }

      // STEP 2: Reset state ke initial values
      set({
        token: null,
        //   ↑ Tidak ada token

        user: null,
        //   ↑ Tidak ada data user

        isAuthenticated: false,
        //   ↓ Ini yang penting! Semua komponen yang subscribe ke $auth
        //     akan re-render karena isAuthenticated berubah dari true ke false

        isLoading: false
        //   → Loading selesai
      });
    },

    // ... (other methods)
  };
}

// ============ REACTIVE UPDATE ============
/*
  Ketika logout() dipanggil:

  1. localStorage.removeItem('token')
     → Token dihapus dari browser storage

  2. set({ token: null, user: null, isAuthenticated: false, isLoading: false })
     → State di-reset

  3. Semua komponen yang subscribe ke $auth akan re-render
     → Dashboard layout: {$auth.isAuthenticated} = false → redirect ke /login
     → Sidebar: tidak ada perubahan visual (karena sudah di dalam layout)
     → Semua halaman di /dashboard: tidak akan di-render karena layout redirect

  4. goto('/login')
     → Navigate ke halaman login
*/
```

### Apa yang terjadi dengan reactive state:

```svelte
<!-- Setelah logout -->

<script>
  import { auth } from '$lib/stores/auth';

  // $auth adalah store subscription
  // Ketika logout() dipanggil, $auth.isAuthenticated berubah dari true ke false

  // Di mana saja komponen menggunakan $auth, akan re-render
</script>

{#if $auth.isAuthenticated}
  <!-- SEBELUM: ini ter-render (true) -->
  <!-- SESUDAH: ini TIDAK ter-render (false) -->
{/if}
```

---

## 3. routes/dashboard/+layout.svelte

### Apa yang terjadi setelah redirect ke /login:

```svelte
<!-- routes/dashboard/+layout.svelte -->

<script>
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { auth } from '$lib/stores/auth';
  import Sidebar from '$lib/components/Sidebar.svelte';

  let { children } = $props();

  onMount(() => {
    // Cek authentication
    if (!$auth.isAuthenticated) {
      // SEBELUM logout: $auth.isAuthenticated = true → tidak masuk if
      // SESUDAH logout: $auth.isAuthenticated = false → masuk if

      goto('/login');
      // → Redirect ke /login
    }
  });
</script>

{#if $auth.isAuthenticated}
  <!-- SEBELUM logout: render sidebar dan content -->
  <!-- SESUDAH logout: TIDAK render apa-apa -->
  <!--            karena $auth.isAuthenticated = false -->

  <div class="pl-64">
    <Sidebar />
    <main class="min-h-screen bg-gray-50 p-6">
      {@render children()}
    </main>
  </div>
{/if}
```

### DIagram flow:

```
User klik "Logout" di Sidebar
        │
        ▼
handleLogout() dipanggil
        │
        ▼
auth.logout()
        │
        ├── 1. localStorage.removeItem('token')
        │      → Token dihapus dari browser
        │
        ├── 2. set({ token: null, user: null, isAuthenticated: false })
        │      → State di-reset
        │      → Semua $auth subscribers trigger re-render
        │
        └── 3. goto('/login')
               → Navigate ke /login
        │
        ▼
/dashboard layout cek $auth.isAuthenticated
        │
        ▼
$auth.isAuthenticated = false
        │
        ▼
Layout tidak render Sidebar atau content
(Halaman menjadi kosong/blank)
        │
        ▼
User sudah di /login page
        │
        ▼
Login page tidak redirect (karena memang untuk guest)
        │
        ▼
User melihat form login
```

---

## Complete Logout Flow Summary

```
┌─────────────────────────────────────────────────────────────┐
│  SIDEBAR                                                    │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  <button onclick={handleLogout}>Logout</button>    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  handleLogout()                                            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  1. auth.logout()                                   │    │
│  │  2. goto('/login')                                 │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                          │
          ┌───────────────┴───────────────┐
          ▼                               ▼
┌─────────────────────┐     ┌─────────────────────┐
│  auth.logout()     │     │  goto('/login')     │
│  - remove token    │     │  - navigate to       │
│  - reset state     │     │    /login page       │
│  - isAuth = false  │     └─────────────────────┘
└─────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│  AUTH STORE REACTIVE UPDATE                                  │
│  $auth.isAuthenticated = false                              │
│  Semua komponen yang subscribe ke $auth di-update           │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│  DASHBOARD LAYOUT                                           │
│  if (!$auth.isAuthenticated) { goto('/login') }             │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│  USER DI /LOGIN PAGE                                        │
│  - Melihat form login                                       │
│  - Tidak bisa akses /dashboard tanpa login                  │
└─────────────────────────────────────────────────────────────┘
```

---

## Security Notes

### Apa yang terjadi pada token:

```typescript
// Saat login
localStorage.setItem('token', 'eyJhbGciOiJIUzI1NiIsInR5cCI6...');
//                ↑ Token disimpan di browser

// Saat logout
localStorage.removeItem('token');
//                ↑ Token dihapus

// Attempt akses /dashboard setelah logout
// → api.get() akan called
// → getHeaders() -> getToken() -> null (tidak ada token)
// → Headers tidak include Authorization
// → Server return 401 Unauthorized
// → Client handleResponse throw Error
// → Redirect ke /login
```

### Alternative yang lebih aman (opsional):

```typescript
// Di server (Laravel) bisa:
// 1. Blacklist token (logout permanent)
// 2. Token expiration (expired token)
// 3. Revoke all user tokens (force logout all devices)
```

---

## Summary

| Step | File | Action |
|------|------|--------|
| 1 | Sidebar.svelte | User klik logout button |
| 2 | handleLogout() | Panggil auth.logout() + goto() |
| 3 | auth.ts | Hapus token, reset state |
| 4 | Reactive update | Semua $auth subscribers re-render |
| 5 | dashboard layout | Redirect ke /login |
| 6 | User di /login | Melihat form login |

### Key Points:

- **Token dihapus dari localStorage** - tidak bisa reuse
- **State di-reset** - semua komponen update
- **Redirect** - ke halaman guest (/login)
- **Proteksi** - dashboard tidak bisa diakses tanpa token