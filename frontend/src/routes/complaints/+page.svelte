<script lang="ts">
	import { onMount } from 'svelte';

	let complaints = $state<any[]>([]);
	let loading = $state(true);
	let error = $state('');
	let showForm = $state(false);

	let newComplaint = $state({ title: '', description: '' });
	let submitting = $state(false);

	const API_URL = 'http://localhost:8000/api/v1';

	async function fetchComplaints() {
		const token = localStorage.getItem('token');
		if (!token) {
			window.location.href = '/login';
			return;
		}

		try {
			const res = await fetch(`${API_URL}/complaints`, {
				headers: {
					Authorization: `Bearer ${token}`,
					Accept: 'application/json'
				}
			});

			if (res.status === 401) {
				localStorage.removeItem('token');
				window.location.href = '/login';
				return;
			}

			const data = await res.json();
			complaints = data.data || data;
		} catch (e: any) {
			error = e.message;
		} finally {
			loading = false;
		}
	}

	async function handleSubmit() {
		const token = localStorage.getItem('token');
		submitting = true;

		try {
			const res = await fetch(`${API_URL}/complaints`, {
				method: 'POST',
				headers: {
					Authorization: `Bearer ${token}`,
					Accept: 'application/json'
				},
				body: JSON.stringify(newComplaint)
			});

			const data = await res.json();

			if (!res.ok) throw new Error(data.message);

			complaints = [data.data, ...complaints];
			showForm = false;
			newComplaint = { title: '', description: '' };
		} catch (e: any) {
			error = e.message;
		} finally {
			submitting = false;
		}
	}

	function logout() {
		localStorage.removeItem('token');
		window.location.href = '/login';
	}

	function getStatusColor(status: string) {
		const colors: Record<string, string> = {
			pending: 'bg-yellow-100 text-yellow-800',
			process: 'bg-blue-100 text-blue-800',
			completed: 'bg-green-100 text-green-800',
			rejected: 'bg-red-100 text-red-800'
		};
		return colors[status] || 'bg-gray-100 text-gray-800';
	}

	onMount(fetchComplaints);
</script>

<div class="min-h-screen bg-gray-50">
	<nav class="bg-white shadow">
		<div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
			<div class="flex items-center justify-between">
				<h1 class="text-xl font-bold text-gray-900">Aduan Saya</h1>
				<div class="flex gap-4">
					<button
						onclick={() => (showForm = !showForm)}
						class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
					>
						{showForm ? 'Batal' : 'Buat Aduan'}
					</button>
					<button
						onclick={logout}
						class="rounded-lg border px-4 py-2 text-gray-600 hover:bg-gray-100"
					>
						Logout
					</button>
				</div>
			</div>
		</div>
	</nav>

	<main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
		{#if error}
			<div class="mb-4 rounded bg-red-100 p-4 text-red-600">{error}</div>
		{/if}

		{#if showForm}
			<div class="mb-8 rounded-lg bg-white p-6 shadow">
				<h2 class="mb-4 text-lg font-semibold">Buat Aduan Baru</h2>
				<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
					<div>
						<label class="block text-sm font-medium text-gray-700">Judul</label>
						<input
							type="text"
							bind:value={newComplaint.title}
							class="mt-1 block w-full rounded-lg border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
							placeholder="Judul aduan"
							required
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700">Deskripsi</label>
						<textarea
							bind:value={newComplaint.description}
							class="mt-1 block w-full rounded-lg border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
							rows="4"
							placeholder="Jelaskan keluhan Anda..."
							required
						></textarea>
					</div>
					<button
						type="submit"
						disabled={submitting}
						class="rounded-lg bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
					>
						{submitting ? 'Mengirim...' : 'Kirim Aduan'}
					</button>
				</form>
			</div>
		{/if}

		{#if loading}
			<div class="text-center text-gray-500">Loading...</div>
		{:else if complaints.length === 0}
			<div class="text-center text-gray-500">Belum ada aduan</div>
		{:else}
			<div class="space-y-4">
				{#each complaints as complaint}
					<div class="rounded-lg bg-white p-6 shadow">
						<div class="flex items-start justify-between">
							<div class="flex-1">
								<h3 class="text-lg font-semibold text-gray-900">{complaint.title}</h3>
								<p class="mt-1 text-gray-600">{complaint.description}</p>

								{#if complaint.images && complaint.images.length > 0}
									<div class="mt-3 flex gap-2">
										{#each complaint.images as img}
											<img
												src={`http://localhost:8000/storage/${img.image_path}`}
												alt="Bukti"
												class="h-20 w-20 rounded-lg object-cover"
											/>
										{/each}
									</div>
								{/if}

								<div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
									<span class={getStatusColor(complaint.status)}>
										{complaint.status}
									</span>
									<span>{new Date(complaint.created_at).toLocaleDateString('id-ID')}</span>
								</div>
							</div>
						</div>
					</div>
				{/each}
			</div>
		{/if}
	</main>
</div>