<script lang="ts">
	import { onMount } from 'svelte';
	import { getComplaints, createComplaint, deleteComplaint } from '$lib/api/complaints';
	import { auth } from '$lib/stores/auth';
	import type { Complaint, CreateComplaintRequest } from '$lib/types';
	import Button from '$lib/components/ui/Button.svelte';
	import Input from '$lib/components/ui/Input.svelte';
	import Card from '$lib/components/ui/Card.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import ThreedotMenu from '$lib/components/ui/ThreedotMenu.svelte';

	let complaints = $state<Complaint[]>([]);
	let loading = $state(true);
	let error = $state('');
	let showForm = $state(false);

	let newComplaint = $state<CreateComplaintRequest>({ title: '', description: '' });
	let submitting = $state(false);

	async function fetchComplaints() {
		try {
			const { data } = await getComplaints();
			complaints = data;
		} catch (e: any) {
			error = e.message;
		} finally {
			loading = false;
		}
	}

	async function handleSubmit() {
		submitting = true;
		error = '';

		try {
			const { data } = await createComplaint(newComplaint);
			complaints = [data, ...complaints];
			showForm = false;
			newComplaint = { title: '', description: '' };
		} catch (e: any) {
			error = e.message;
		} finally {
			submitting = false;
		}
	}

	async function handleDelete(id: number) {
		if (!confirm('Yakin hapus aduan ini?')) return;

		try {
			await deleteComplaint(id);
			complaints = complaints.filter((c) => c.id !== id);
		} catch (e: any) {
			error = e.message;
		}
	}

	onMount(fetchComplaints);

	const menuItems = (id: number) => [
		{ label: 'Hapus', onclick: () => handleDelete(id), danger: true }
	];
</script>

<div class="space-y-6">
	<div class="flex items-center justify-between">
		<div>
			<h1 class="text-2xl font-bold text-gray-900">Aduan Saya</h1>
			<p class="text-gray-500">Kelola semua aduan Anda</p>
		</div>
		<Button onclick={() => (showForm = !showForm)}>
			{showForm ? 'Batal' : 'Buat Aduan'}
		</Button>
	</div>

	{#if error}
		<Alert type="error">{error}</Alert>
	{/if}

	{#if showForm}
		<Card>
			<h2 class="mb-4 text-lg font-semibold">Buat Aduan Baru</h2>
			<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
				<Input
					type="text"
					placeholder="Judul aduan"
					bind:value={newComplaint.title}
					label="Judul"
					required
				/>
				<div>
					<label class="block text-sm font-medium text-gray-700">
						Deskripsi <span class="text-red-500">*</span>
					</label>
					<textarea
						bind:value={newComplaint.description}
						class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
						rows="4"
						placeholder="Jelaskan keluhan Anda..."
						required
					></textarea>
				</div>
				<Button type="submit" disabled={submitting}>
					{submitting ? 'Mengirim...' : 'Kirim Aduan'}
				</Button>
			</form>
		</Card>
	{/if}

	{#if loading}
		<p class="text-center text-gray-500">Loading...</p>
	{:else if complaints.length === 0}
		<p class="text-center text-gray-500">Belum ada aduan</p>
	{:else}
		<div class="space-y-4">
			{#each complaints as complaint}
				<Card>
					<div class="flex items-start justify-between">
						<div class="flex-1">
							<h3 class="text-lg font-semibold text-gray-900">{complaint.title}</h3>
							<p class="mt-1 text-gray-600">{complaint.description}</p>

							{#if complaint.images && complaint.images.length > 0}
								<div class="mt-3 flex gap-2">
									{#each complaint.images as img}
										<img
											src={`${import.meta.env.VITE_API_URL}/storage/${img.image_path}`}
											alt="Bukti"
											class="h-20 w-20 rounded-lg object-cover"
										/>
									{/each}
								</div>
							{/if}

							<div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
								<Badge status={complaint.status} />
								<span>{new Date(complaint.created_at).toLocaleDateString('id-ID')}</span>
							</div>
						</div>

						<ThreedotMenu items={menuItems(complaint.id)} />
					</div>
				</Card>
			{/each}
		</div>
	{/if}
</div>