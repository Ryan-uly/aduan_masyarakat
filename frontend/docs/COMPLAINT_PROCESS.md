# Complaint Creation Flow - Detail Documentation

## Table of Contents
1. [User Interface - dashboard/complaints/+page.svelte](#1-dashboardcomplaintspage-svelte)
2. [API Service - lib/api/complaints/index.ts](#2-libapicomplaintsindex-ts)
3. [API Client - lib/api/client.ts](#3-libapiclientts)
4. [Type Definitions - lib/types/index.ts](#4-libtypesindex-ts)
5. [Complaint List Display](#5-complaint-list-display)

---

## 1. routes/dashboard/complaints/+page.svelte

### Apa yang dilakukan file ini:
Halaman untuk menampilkan daftar complaint user dan form untuk membuat complaint baru.

### Step-by-step:

```svelte
<script lang="ts">
  // ============ STEP 1: IMPORTS ============
  import { onMount } from 'svelte';
  //   ↑ Lifecycle hook - untuk fetch data saat page load

  import { getComplaints, createComplaint, deleteComplaint } from '$lib/api/complaints';
  //   ↑ Import semua complaint API functions
  //   → getComplaints: fetch list
  //   → createComplaint: create new
  //   → deleteComplaint: delete by id

  import { auth } from '$lib/stores/auth';
  //   ↑ Import auth store (walaupun tidak langsung dipakai di page ini)
  //   → Untuk type consistency jika diperlukan

  import type { Complaint, CreateComplaintRequest } from '$lib/types';
  //   ↑ Import TypeScript interfaces
  //   → Complaint: untuk list
  //   → CreateComplaintRequest: untuk form

  import Button from '$lib/components/ui/Button.svelte';
  import Input from '$lib/components/ui/Input.svelte';
  import Card from '$lib/components/ui/Card.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import Alert from '$lib/components/ui/Alert.svelte';
  import ThreedotMenu from '$lib/components/ui/ThreedotMenu.svelte';
  //   ↑ Import UI components yang akan digunakan

  // ============ STEP 2: STATE DECLARATIONS ============
  let complaints = $state<Complaint[]>([]);
  //   ↑ State untuk menyimpan array of complaints
  //   → Tipe: Complaint[] (array of Complaint interface)
  //   → Default: empty array

  let loading = $state(true);
  //   ↑ State untuk loading indicator saat fetch list
  //   → true = sedang loading, false = selesai

  let error = $state('');
  //   ↑ State untuk error message
  //   → Empty = tidak ada error

  let showForm = $state(false);
  //   ↑ State untuk toggle form visibility
  //   → false = form hidden, true = form shown

  // ============ STEP 3: FORM STATE ============
  let newComplaint = $state<CreateComplaintRequest>({
    title: '',
    description: ''
    //   ↑ Initialize dengan empty values
    //   → Bind ke form inputs
  });

  let submitting = $state(false);
  //   ↑ State untuk submit loading
  //   → true = sedang submit, disable button

  // ============ STEP 4: FETCH COMPLAINTS ============
  async function fetchComplaints() {
    // Fungsi untuk mengambil data complaint dari API
    try {
      // Panggil API
      const { data } = await getComplaints();
      //              ↑ Destructuring response
      //              → { data: Complaint[], meta?: ... }
      //
      // Wait for response dan assign ke state
      complaints = data;
      //   → complaints = array of Complaint dari API
      //   → $state akan trigger re-render

    } catch (e: any) {
      // Handle error jika API gagal
      error = e.message;
      //   ↑ Simpan error message ke state
      //   → Akan ditampilkan di UI via Alert component

    } finally {
      // Selalu jalan, sukses atau error
      loading = false;
      //   → Set loading ke false
      //   → Indicator "Loading..." akan hilang
    }
  }

  // ============ STEP 5: CREATE COMPLAINT ============
  async function handleSubmit() {
    // Fungsi untuk handle form submission
    submitting = true;
    //   ↑ Aktifkan submit loading

    error = '';
    //   ↑ Reset error sebelumnya

    try {
      // Panggil API create complaint
      const { data } = await createComplaint(newComplaint);
      //                                ↑ Object { title, description }
      //
      // API akan return:
      // { data: { id: 1, title: "...", description: "...", status: "pending", ... } }

      // Update state dengan complaint baru
      complaints = [data, ...complaints];
      //   ↑ prepend data baru ke array
      //   → complaint terbaru muncul di atas

      // Reset form
      showForm = false;
      //   ↑ Hide form setelah submit success

      newComplaint = { title: '', description: '' };
      //   → Reset form fields ke empty

    } catch (e: any) {
      // Handle error
      error = e.message;
      //   ↑ Simpan error message

    } finally {
      // Reset loading state
      submitting = false;
    }
  }

  // ============ STEP 6: DELETE COMPLAINT ============
  async function handleDelete(id: number) {
    // Fungsi untuk hapus complaint
    if (!confirm('Yakin hapus aduan ini?')) {
      // Confirmation dialog
      //   ↑ Browser confirm() - user harus klik OK atau Cancel
      return; // Exit function jika Cancel
    }

    try {
      // Panggil API delete
      await deleteComplaint(id);
      //              ↑ ID complaint yang akan dihapus

      // Update state - filter out deleted item
      complaints = complaints.filter(c => c.id !== id);
      //   ↑ Array filter - buat array baru tanpa item yang dihapus
      //   → c.id !== id berarti "keep semua yang tidak sama dengan id yang dihapus"

    } catch (e: any) {
      // Handle error
      error = e.message;
    }
  }

  // ============ STEP 7: ON MOUNT ============
  onMount(fetchComplaints);
  //   ↑ Panggil fetchComplaints() saat komponen mount
  //   → Component mounted ke DOM
  //   → Trigger initial data fetch

  // ============ STEP 8: MENU ITEMS FOR THREEDOT ============
  const menuItems = (id: number) => [
    // Function untuk generate menu items per complaint
    //   ↑ Diterima oleh ThreedotMenu component
    { label: 'Hapus', onclick: () => handleDelete(id), danger: true }
    //                ↑ Label button
    //                ↑ Function yang dijalankan saat klik
    //                ↑ danger: true = styling merah (untuk action berbahaya)
  ];
  //   Return array of menu items
</script>

<!-- ============ UI TEMPLATE ============ -->

<div class="space-y-6">
  <!-- Header Section -->
  <div class="flex items-center justify-between">
    // FLEX container - title di kiri, button di kanan
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Aduan Saya</h1>
      <p class="text-gray-500">Kelola semua aduan Anda</p>
    </div>

    <Button onclick={() => (showForm = !showForm)}>
      // Toggle button
      {showForm ? 'Batal' : 'Buat Aduan'}
      //   ↑ Conditional text
    </Button>
  </div>

  <!-- Error Alert -->
  {#if error}
    // Conditional rendering - hanya muncul jika ada error
    <Alert type="error">{error}</Alert>
  {/if}

  <!-- Form Section (Collapsible) -->
  {#if showForm}
    // Hanya render jika showForm = true
    <Card>
      <h2 class="mb-4 text-lg font-semibold">Buat Aduan Baru</h2>

      <form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
      // Form dengan preventDefault - 不要 page reload

        <!-- Title Input -->
        <Input
          type="text"
          placeholder="Judul aduan"
          bind:value={newComplaint.title}
          //              ↑ Two-way binding ke state
          label="Judul"
          required
        />

        <!-- Description Textarea -->
        <div>
          // Manual textarea (belum pake RichTextArea)
          <label class="block text-sm font-medium text-gray-700">
            Deskripsi <span class="text-red-500">*</span>
          </label>

          <textarea
            bind:value={newComplaint.description}
            //              ↑ Bind ke description field
            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            rows="4"
            placeholder="Jelaskan keluhan Anda..."
            required
          ></textarea>
        </div>

        <!-- Submit Button -->
        <Button type="submit" disabled={submitting}>
          {submitting ? 'Mengirim...' : 'Kirim Aduan'}
        </Button>
      </form>
    </Card>
  {/if}

  <!-- Loading State -->
  {#if loading}
    // Tampilkan loading text saat fetch
    <p class="text-center text-gray-500">Loading...</p>

  <!-- Empty State -->
  {:else if complaints.length === 0}
    // Tampilkan jika tidak ada data
    <p class="text-center text-gray-500">Belum ada aduan</p>

  <!-- Complaint List -->
  {:else}
    <div class="space-y-4">
      // Loop through complaints array
      {#each complaints as complaint}
        // Iterasi untuk setiap complaint

        <Card>
          // Card wrapper untuk satu complaint

          <div class="flex items-start justify-between">
            // Flex: content di kiri, menu di kanan

            <div class="flex-1">
              // Content area

              <!-- Title -->
              <h3 class="text-lg font-semibold text-gray-900">
                {complaint.title}
              </h3>

              <!-- Description -->
              <p class="mt-1 text-gray-600">
                {complaint.description}
              </p>

              <!-- Images (jika ada) -->
              {#if complaint.images && complaint.images.length > 0}
                // Conditional: hanya render jika ada images

                <div class="mt-3 flex gap-2">
                  {#each complaint.images as img}
                    <img
                      src={`${import.meta.env.VITE_API_URL}/storage/${img.image_path}`}
                      //        ↑ Environment variable + image path
                      //        → Full URL: http://localhost:8000/api/v1/storage/xxx.jpg

                      alt="Bukti"
                      class="h-20 w-20 rounded-lg object-cover"
                      //  Fixed size, object-fit cover
                    />
                  {/each}
                </div>
              {/if}

              <!-- Status & Date -->
              <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                <Badge status={complaint.status} />
                //    ↑ Badge component, auto styling berdasarkan status

                <span>
                  {new Date(complaint.created_at).toLocaleDateString('id-ID')}
                  //  Convert timestamp ke format Indonesia
                </span>
              </div>
            </div>

            <!-- Threedot Menu -->
            <ThreedotMenu items={menuItems(complaint.id)} />
            //  Menu dengan delete action
          </div>
        </Card>
      {/each}
    </div>
  {/if}
</div>
```

---

## 2. lib/api/complaints/index.ts

### Apa yang dilakukan file ini:
Service layer untuk semua complaint-related API calls.

### Step-by-step:

```typescript
// lib/api/complaints/index.ts

// STEP 1: Import dependencies
import { api } from '$lib/api/client';
//   ↑ Import HTTP client singleton

import type { Complaint, ApiResponse, CreateComplaintRequest } from '$lib/types';
//   ↑ Import type definitions


// STEP 2: GET - Fetch all complaints
export function getComplaints() {
  // Fungsi untuk mengambil semua complaints user

  return api.get<ApiResponse<Complaint[]>>('/complaints');
  //           ↑ Generic type: response akan berupa ApiResponse dengan array Complaint
  //   Endpoint: GET /api/v1/complaints
  //
  // Response:
  // {
  //   "data": [
  //     { "id": 1, "title": "...", "status": "pending", ... },
  //     { "id": 2, "title": "...", "status": "process", ... }
  //   ],
  //   "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 2 }
  // }
}

// STEP 3: GET - Fetch single complaint by ID
export function getComplaint(id: number) {
  // Fungsi untuk mengambil satu complaint berdasarkan ID

  return api.get<ApiResponse<Complaint>>(`/complaints/${id}`);
  //                                     ↑ Dynamic endpoint dengan ID
  //
  // Endpoint: GET /api/v1/complaints/1
  //
  // Response:
  // {
  //   "data": { "id": 1, "title": "...", "status": "pending", ... }
  // }
}

// STEP 4: POST - Create new complaint
export function createComplaint(data: CreateComplaintRequest) {
  // Fungsi untuk membuat complaint baru
  //   ↑ Parameter type dari types/index.ts

  return api.post<ApiResponse<Complaint>>('/complaints', data);
  //                              ↑ Body: { title, description }
  //
  // Endpoint: POST /api/v1/complaints
  //
  // Request body:
  // {
  //   "title": "Jalan Rusak",
  //   "description": "Ada lubang di depan rumah"
  // }
  //
  // Response:
  // {
  //   "data": { "id": 3, "title": "Jalan Rusak", "status": "pending", ... }
  // }
}

// STEP 5: PUT - Update complaint
export function updateComplaint(id: number, data: Partial<CreateComplaintRequest>) {
  // Fungsi untuk update complaint
  //   ↑ Partial<...> = semua fields optional (tidak perlu semua)

  return api.put<ApiResponse<Complaint>>(`/complaints/${id}`, data);
  //                                                   ↑ Partial update
  //
  // Endpoint: PUT /api/v1/complaints/1
  //
  // Request body (contoh partial):
  // {
  //   "description": "Deskripsi baru"
  // }
}

// STEP 6: DELETE - Delete complaint
export function deleteComplaint(id: number) {
  // Fungsi untuk hapus complaint

  return api.delete<void>(`/complaints/${id}`);
  //                    ↑ Generic type: void = tidak ada data di response
  //
  // Endpoint: DELETE /api/v1/complaints/1
  //
  // Response:
  // {}  (empty object atau null)
}
```

---

## 3. lib/api/client.ts

### Bagaimana GET dan POST bekerja untuk complaints:

```typescript
// GET /complaints
async function fetchComplaints() {
  const res = await fetch('http://localhost:8000/api/v1/complaints', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
      //   ↑ Token dari localStorage otomatis ditambahkan
    }
  });

  const data = await res.json();
  return data;
}

// POST /complaints
async function createComplaint(body) {
  const res = await fetch('http://localhost:8000/api/v1/complaints', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
    },
    body: JSON.stringify(body)
  });

  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.message || 'Error');
  }

  return data;
}
```

---

## 4. lib/types/index.ts

### Type definitions yang relevan:

```typescript
// ============ COMPLAINT STATUS ============
export type ComplaintStatus = 'pending' | 'process' | 'completed' | 'rejected';
//   ↑ Literal type - hanya 4 nilai yang valid


// ============ COMPLAINT IMAGE ============
export interface ComplaintImage {
  id: number;
  complaint_id: number;
  image_path: string;
  created_at?: string;
}


// ============ COMPLAINT ============
export interface Complaint {
  id: number;
  user_id: number;
  title: string;
  description: string;
  status: ComplaintStatus;  // ← Pakai type di atas
  images?: ComplaintImage[];  // ← Optional array
  created_at: string;
  updated_at?: string;
}


// ============ CREATE REQUEST ============
export interface CreateComplaintRequest {
  title: string;
  description: string;
  images?: File[];  // ← Optional untuk upload
}


// ============ API RESPONSE ============
export interface ApiResponse<T> {
  data: T;
  message?: string;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
```

---

## 5. Complaint List Display

### Bagaimana data ditampilkan:

```
API Response:
{
  "data": [
    {
      "id": 1,
      "title": "Jalan Rusak",
      "description": "Lubang di Jalan Melati",
      "status": "pending",
      "created_at": "2026-05-02T10:30:00Z"
    },
    {
      "id": 2,
      "title": "Lampu Jalan Mati",
      "description": "Semua lampu jalan tidak menyala",
      "status": "process",
      "created_at": "2026-05-01T08:00:00Z"
    }
  ]
}

↓ parse dan assign ke state

State:
complaints = [
  { id: 1, title: "Jalan Rusak", status: "pending", ... },
  { id: 2, title: "Lampu Jalan Mati", status: "process", ... }
]

↓ loop dengan #each

UI:
┌─────────────────────────────────────────┐
│ Card                                     │
│ ┌─────────────────────────────────────┐ │
│ │ Jalan Rusak                          │ │
│ │ Lubang di Jalan Melati              │ │
│ │ [Badge: Menunggu] 02/05/2026       │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
┌─────────────────────────────────────────┐
│ Card                                     │
│ ┌─────────────────────────────────────┐ │
│ │ Lampu Jalan Mati                    │ │
│ │ Semua lampu jalan tidak menyala    │ │
│ │ [Badge: Diproses] 01/05/2026       │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

---

## Summary - Complaint Creation Flow

```
User klik "Buat Aduan"
        │
        ▼
showForm = true  → Form muncul
        │
        ▼
User input title & description
        │
        ▼
User klik "Kirim Aduan"
        │
        ▼
handleSubmit() dipanggil
        │
        ▼
createComplaint(newComplaint)
        │
        ▼
api.post('/complaints', body)
        │
        ▼
HTTP POST ke server
        │
        ▼
Server return { data: newComplaint }
        │
        ▼
complaints = [newComplaint, ...complaints]
        │
        ▼
showForm = false  → Form sembunyi
        │
        ▼
UI update - complaint baru muncul di list
```

### File yang Terlibat:

| File | Responsibility |
|------|----------------|
| `dashboard/complaints/+page.svelte` | UI form + list + event handlers |
| `api/complaints/index.ts` | Service functions (get, create, delete) |
| `api/client.ts` | HTTP requests + auth headers |
| `types/index.ts` | Type definitions |