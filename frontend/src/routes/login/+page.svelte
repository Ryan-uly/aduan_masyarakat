<script lang="ts">
	import { loginRequest } from '$lib/api/auth/login';
	import { goto } from '$app/navigation';

	let email = $state('');
	let password = $state('');
	let error = $state('');
	let loading = $state(false);

	async function handleLogin() {
		loading = true;
		error = '';

		try {
			const data = await loginRequest(email, password);

			localStorage.setItem('token', data.token);

			goto('/complaints');
		} catch (e: any) {
			error = e?.message || 'Login gagal';
		} finally {
			loading = false;
		}
	}
</script>

<div class="grid min-h-screen md:grid-cols-2">
	<!-- LEFT SIDE (HERO) -->
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

	<!-- RIGHT SIDE (FORM) -->
	<div class="flex items-center justify-center bg-gray-100 px-6 py-10">
		<div class="w-full max-w-md rounded-2xl bg-white p-8 shadow">
			<h2 class="text-center text-2xl font-bold text-gray-800">Login</h2>

			<p class="mt-2 text-center text-gray-500">Masuk ke akun kamu</p>

			{#if error}
				<div class="mt-4 rounded bg-red-100 p-3 text-sm text-red-600">
					{error}
				</div>
			{/if}

			<div class="mt-6 space-y-4">
				<input
					type="email"
					placeholder="Email"
					class="w-full rounded-lg border px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
					bind:value={email}
				/>

				<input
					type="password"
					placeholder="Password"
					class="w-full rounded-lg border px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
					bind:value={password}
				/>

				<button
					onclick={handleLogin}
					class="w-full rounded-lg bg-indigo-600 py-3 text-white transition hover:bg-indigo-700 disabled:opacity-50"
					disabled={loading}
				>
					{loading ? 'Loading...' : 'Login'}
				</button>
			</div>

			<p class="mt-6 text-center text-sm text-gray-500">
				Belum punya akun?
				<a href="/register" class="font-semibold text-indigo-600"> Register </a>
			</p>
		</div>
	</div>
</div>
