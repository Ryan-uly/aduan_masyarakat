<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { auth } from '$lib/stores/auth';
	import Sidebar from '$lib/components/Sidebar.svelte';

	let { children } = $props();

	onMount(() => {
		if (!$auth.isAuthenticated) {
			goto('/login');
		}
	});
</script>

{#if $auth.isAuthenticated}
	<div class="pl-64">
		<Sidebar />
		<main class="min-h-screen bg-gray-50 p-6">
			{@render children()}
		</main>
	</div>
{/if}