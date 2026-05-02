<script lang="ts">
	interface Props {
		type?: 'text' | 'email' | 'password' | 'number' | 'tel';
		placeholder?: string;
		value?: string;
		label?: string;
		name?: string;
		required?: boolean;
		disabled?: boolean;
		error?: string;
		class?: string;
		oninput?: (e: Event) => void;
	}

	let {
		type = 'text',
		placeholder = '',
		value = $bindable(''),
		label,
		name,
		required = false,
		disabled = false,
		error,
		class: className = '',
		oninput
	}: Props = $props();
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

	<input
		{type}
		id={name}
		{name}
		{placeholder}
		{required}
		{disabled}
		bind:value
		oninput={oninput}
		class="mt-1 block w-full rounded-lg border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 {error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300'} {disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'} {className}"
	/>

	{#if error}
		<p class="mt-1 text-sm text-red-500">{error}</p>
	{/if}
</div>