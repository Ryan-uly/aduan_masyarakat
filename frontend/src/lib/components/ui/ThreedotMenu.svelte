<script lang="ts">
	import type { Snippet } from 'svelte';

	interface MenuItem {
		label: string;
		icon?: Snippet;
		onclick?: () => void;
		danger?: boolean;
		disabled?: boolean;
		separator?: boolean;
	}

	interface Props {
		items: MenuItem[];
		class?: string;
	}

	let {
		items = [],
		class: className = ''
	}: Props = $props();

	let isOpen = $state(false);

	function toggleMenu() {
		isOpen = !isOpen;
	}

	function handleItemClick(callback: (() => void) | undefined) {
		if (callback) {
			callback();
		}
		isOpen = false;
	}

	function handleBlur() {
		setTimeout(() => {
			isOpen = false;
		}, 150);
	}
</script>

<div class="relative inline-block {className}">
	<button
		type="button"
		onclick={toggleMenu}
		onblur={handleBlur}
		class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
		aria-label="Menu"
	>
		<svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
			<circle cx="12" cy="5" r="1.5" />
			<circle cx="12" cy="12" r="1.5" />
			<circle cx="12" cy="19" r="1.5" />
		</svg>
	</button>

	{#if isOpen}
		<div class="absolute right-0 z-20 mt-1 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
			{#each items as item}
				{#if item.separator}
					<div class="my-1 border-t border-gray-200"></div>
				{:else}
					<button
						type="button"
						onclick={() => handleItemClick(item.onclick)}
						disabled={item.disabled}
						class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-gray-50 {item.disabled ? 'cursor-not-allowed text-gray-400' : item.danger ? 'text-red-600 hover:bg-red-50' : 'text-gray-700'}"
					>
						{#if item.icon}
							{@render item.icon()}
						{/if}
						{item.label}
					</button>
				{/if}
			{/each}
		</div>
	{/if}
</div>