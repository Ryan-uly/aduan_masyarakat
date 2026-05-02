<script lang="ts">
	import { onMount } from 'svelte';
	import { auth } from '$lib/stores/auth';
	import { getComplaints } from '$lib/api/complaints';
	import Card from '$lib/components/ui/Card.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import type { Complaint } from '$lib/types';

	let stats = $state({
		total: 0,
		pending: 0,
		process: 0,
		completed: 0
	});
	let recentComplaints = $state<Complaint[]>([]);
	let loading = $state(true);

	onMount(async () => {
		try {
			const { data } = await getComplaints();
			recentComplaints = data.slice(0, 5);
			stats.total = data.length;
			stats.pending = data.filter((c) => c.status === 'pending').length;
			stats.process = data.filter((c) => c.status === 'process').length;
			stats.completed = data.filter((c) => c.status === 'completed').length;
		} catch (e) {
			console.error(e);
		} finally {
			loading = false;
		}
	});
</script>

<div class="space-y-6">
	<!-- Header -->
	<div>
		<h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
		<p class="text-gray-500">Selamat datang, {$auth.user?.name || 'User'}!</p>
	</div>

	<!-- Stats Cards -->
	<div class="grid gap-4 md:grid-cols-4">
		<Card>
			<div class="text-center">
				<p class="text-3xl font-bold text-indigo-600">{stats.total}</p>
				<p class="text-sm text-gray-500">Total Aduan</p>
			</div>
		</Card>
		<Card>
			<div class="text-center">
				<p class="text-3xl font-bold text-yellow-600">{stats.pending}</p>
				<p class="text-sm text-gray-500">Menunggu</p>
			</div>
		</Card>
		<Card>
			<div class="text-center">
				<p class="text-3xl font-bold text-blue-600">{stats.process}</p>
				<p class="text-sm text-gray-500">Diproses</p>
			</div>
		</Card>
		<Card>
			<div class="text-center">
				<p class="text-3xl font-bold text-green-600">{stats.completed}</p>
				<p class="text-sm text-gray-500">Selesai</p>
			</div>
		</Card>
	</div>

	<!-- Recent Complaints -->
	<Card>
		<h2 class="mb-4 text-lg font-semibold">Aduan Terbaru</h2>
		{#if loading}
			<p class="text-center text-gray-500">Loading...</p>
		{:else if recentComplaints.length === 0}
			<p class="text-center text-gray-500">Belum ada aduan</p>
		{:else}
			<div class="space-y-3">
				{#each recentComplaints as complaint}
					<div class="flex items-center justify-between border-b pb-3 last:border-0">
						<div>
							<p class="font-medium text-gray-900">{complaint.title}</p>
							<p class="text-sm text-gray-500">
								{new Date(complaint.created_at).toLocaleDateString('id-ID')}
							</p>
						</div>
						<Badge status={complaint.status} />
					</div>
				{/each}
			</div>
			<a href="/dashboard/complaints" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">
				Lihat semua →
			</a>
		{/if}
	</Card>
</div>