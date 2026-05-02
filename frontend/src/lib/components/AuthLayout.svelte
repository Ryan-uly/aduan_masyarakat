<script lang="ts">
	import type { Snippet } from 'svelte';

	interface Props {
		children: Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="grid min-h-screen md:grid-cols-2">
	<!-- Left Side - Hero -->
	<div
		class="hidden flex-col items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 p-10 text-white md:flex"
	>
		<h1 class="text-center text-4xl leading-tight font-bold">Sistem Aduan Masyarakat</h1>

		<p class="mt-4 max-w-md text-center text-white/80">
			Laporkan masalah di sekitar Anda dengan cepat, mudah, dan transparan.
		</p>

		<img
			src="https://illustrations.popsy.co/white/customer-support.svg"
			class="mt-10 w-80"
			alt="illustration"
		/>
	</div>

	<!-- Right Side - Form -->
	<div class="flex items-center justify-center bg-gray-100 px-6 py-10">
		{@render children()}
	</div>
</div>