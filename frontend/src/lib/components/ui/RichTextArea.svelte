<script lang="ts">
	interface Props {
		value?: string;
		label?: string;
		placeholder?: string;
		name?: string;
		required?: boolean;
		disabled?: boolean;
		rows?: number;
		error?: string;
		class?: string;
		oninput?: (e: Event) => void;
		onchange?: (e: Event) => void;
	}

	let {
		value = $bindable(''),
		label,
		placeholder = '',
		name,
		required = false,
		disabled = false,
		rows = 4,
		error,
		class: className = '',
		oninput,
		onchange
	}: Props = $props();

	let editorRef: HTMLDivElement;
	let isFocused = $state(false);

	function handleInput(e: Event) {
		const target = e.target as HTMLDivElement;
		value = target.innerText;
		oninput?.(e);
	}

	function execCommand(command: string, value?: string) {
		document.execCommand(command, false, value);
		editorRef?.focus();
	}

	function handleKeyDown(e: KeyboardEvent) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
		}
	}
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

	<div class="mt-1 rounded-lg border {error ? 'border-red-500' : isFocused ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-300'} {disabled ? 'bg-gray-100' : 'bg-white'}">
		<div class="flex gap-1 border-b border-gray-200 bg-gray-50 px-2 py-1.5 {disabled ? 'opacity-50 cursor-not-allowed' : ''}">
			<button
				type="button"
				class="rounded p-1.5 hover:bg-gray-200 disabled:opacity-50"
				onclick={() => execCommand('bold')}
				disabled={disabled}
				title="Bold"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" /></svg>
			</button>
			<button
				type="button"
				class="rounded p-1.5 hover:bg-gray-200 disabled:opacity-50"
				onclick={() => execCommand('italic')}
				disabled={disabled}
				title="Italic"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4 M14 4l-4 16 M6 20h4" /></svg>
			</button>
			<button
				type="button"
				class="rounded p-1.5 hover:bg-gray-200 disabled:opacity-50"
				onclick={() => execCommand('underline')}
				disabled={disabled}
				title="Underline"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v7a5 5 0 0010 0V4 M5 20h14" /></svg>
			</button>
			<div class="w-px bg-gray-300 mx-1"></div>
			<button
				type="button"
				class="rounded p-1.5 hover:bg-gray-200 disabled:opacity-50"
				onclick={() => execCommand('insertUnorderedList')}
				disabled={disabled}
				title="Bullet List"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16 M4 12h16 M4 18h16" /></svg>
			</button>
		</div>

		<div
			bind:this={editorRef}
			contenteditable={!disabled}
			role="textbox"
			aria-multiline="true"
			onfocus={() => isFocused = true}
			onblur={() => isFocused = false}
			oninput={handleInput}
			onkeydown={handleKeyDown}
			class="min-h-[{rows * 1.5}rem] px-4 py-3 focus:outline-none {className}"
			{placeholder}
			style="min-height: {rows * 1.5}rem;"
		>
			{value}
		</div>
	</div>

	{#if error}
		<p class="mt-1 text-sm text-red-500">{error}</p>
	{/if}
</div>