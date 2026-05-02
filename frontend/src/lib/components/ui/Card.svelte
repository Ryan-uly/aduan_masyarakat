<script lang="ts">
	import type { Snippet } from 'svelte';

	interface Props {
		padding?: 'none' | 'sm' | 'md' | 'lg';
		class?: string;
		children: Snippet;
	}

	let {
		padding = 'md',
		class: className = '',
		children
	}: Props = $props();

	const paddingClasses = {
		none: '',
		sm: 'p-4',
		md: 'p-6',
		lg: 'p-8'
	};
</script>

<div class="rounded-xl bg-white shadow {paddingClasses[padding]} {className}">
	{@render children()}
</div>