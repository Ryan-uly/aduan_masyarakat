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

	const typeConfig = {
		error: { bg: 'bg-red-100', text: 'text-red-600', icon: '⚠️' },
		success: { bg: 'bg-green-100', text: 'text-green-600', icon: '✓' },
		warning: { bg: 'bg-yellow-100', text: 'text-yellow-600', icon: '⚡' },
		info: { bg: 'bg-blue-100', text: 'text-blue-600', icon: 'ℹ️' }
	};

	const config = $derived(typeConfig[type]);
</script>

<div class="rounded-lg p-4 {config.bg} {config.text} {className}">
	{@render children()}
</div>