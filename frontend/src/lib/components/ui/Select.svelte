<script lang="ts">
	interface Option {
		value: string;
		label: string;
		disabled?: boolean;
	}

	interface Props {
		value?: string;
		options: Option[];
		label?: string;
		placeholder?: string;
		name?: string;
		required?: boolean;
		disabled?: boolean;
		error?: string;
		class?: string;
		onchange?: (e: Event) => void;
	}

	let {
		value = $bindable(''),
		options = [],
		label,
		placeholder = 'Pilih...',
		name,
		required = false,
		disabled = false,
		error,
		class: className = '',
		onchange
	}: Props = $props();

	let isOpen = $state(false);

	function handleSelect(optionValue: string) {
		value = optionValue;
		isOpen = false;
		onchange?.(new CustomEvent('change', { detail: { value: optionValue } }));
	}

	function toggleDropdown() {
		if (!disabled) {
			isOpen = !isOpen;
		}
	}

	function handleBlur() {
		setTimeout(() => {
			isOpen = false;
		}, 150);
	}

	const selectedLabel = $derived(options.find(o => o.value === value)?.label || placeholder);
</script>

<div class="w-full">
	{#if label}
		<label for={name} class="block text-sm font-medium text-gray-700">
			{label}
			{#if required}
				<span class="text-red-500">*</span>
			{/if}
		</label>
	{/if}

	<div class="relative mt-1">
		<button
			type="button"
			id={name}
			onclick={toggleDropdown}
			onblur={handleBlur}
			{disabled}
			class="relative w-full cursor-pointer rounded-lg border bg-white px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-indigo-500 {error ? 'border-red-500' : 'border-gray-300'} {disabled ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'text-gray-900'}"
		>
			<span class="block truncate {value ? '' : 'text-gray-400'}">
				{selectedLabel}
			</span>
			<span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
				<svg class="h-5 w-5 text-gray-400 transition-transform {isOpen ? 'rotate-180' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
				</svg>
			</span>
		</button>

		{#if isOpen && !disabled}
			<ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
				{#each options as option}
					<li>
						<button
							type="button"
							onclick={() => handleSelect(option.value)}
							disabled={option.disabled}
							class="w-full px-4 py-2.5 text-left transition hover:bg-indigo-50 {option.disabled ? 'cursor-not-allowed text-gray-400' : value === option.value ? 'bg-indigo-100 text-indigo-700' : 'text-gray-900'}"
						>
							{option.label}
						</button>
					</li>
				{/each}
			</ul>
		{/if}
	</div>

	{#if error}
		<p class="mt-1 text-sm text-red-500">{error}</p>
	{/if}
</div>