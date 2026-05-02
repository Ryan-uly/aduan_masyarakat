<script lang="ts">
	import { registerRequest } from '$lib/api/auth/register';
	import { goto } from '$app/navigation';

	let name = $state('');
	let email = $state('');
	let password = $state('');
	let confirmPassword = $state('');
	let error = $state('');
	let loading = $state(false);

	async function handleRegister() {
		error = '';

		// 🔥 validasi sederhana
		if (!name || !email || !password) {
			error = 'Semua field wajib diisi';
			return;
		}

		if (password !== confirmPassword) {
			error = 'Password tidak sama';
			return;
		}

		loading = true;

		try {
			const data = await registerRequest(
				name,
				email,
				password,
				confirmPassword
			);

			// 🔥 kalau API return token langsung
			if (data.token) {
				localStorage.setItem('token', data.token);
				goto('/complaints');
			} else {
				// kalau tidak, arahkan ke login
				goto('/login');
			}
		} catch (e: any) {
			error = e?.message || 'Register gagal';
		} finally {
			loading = false;
		}
	}
</script>

<div class="grid min-h-screen md:grid-cols-2">

	<!-- LEFT SIDE -->
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

	<!-- RIGHT SIDE -->
	<div class="flex items-center justify-center bg-gray-100 px-6 py-10">

		<div class="w-full max-w-md bg-white p-8 rounded-2xl shadow">

			<h2 class="text-2xl font-bold text-center text-gray-800">
				Register
			</h2>

			<p class="text-center text-gray-500 mt-2">
				Buat akun baru
			</p>

			{#if error}
				<div class="mt-4 bg-red-100 text-red-600 p-3 rounded text-sm">
					{error}
				</div>
			{/if}

			<form onsubmit={(e) => { e.preventDefault(); handleRegister(); }} class="mt-6 space-y-4">

				<input
					type="text"
					placeholder="Nama"
					class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
					bind:value={name}
					disabled={loading}
				/>

				<input
					type="email"
					placeholder="Email"
					class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
					bind:value={email}
					disabled={loading}
				/>

				<input
					type="password"
					placeholder="Password"
					class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
					bind:value={password}
					disabled={loading}
				/>

				<input
					type="password"
					placeholder="Konfirmasi Password"
					class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
					bind:value={confirmPassword}
					disabled={loading}
				/>

				<button
					type="submit"
					class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
					disabled={loading}
				>
					{loading ? 'Loading...' : 'Register'}
				</button>

			</form>

			<p class="text-center text-sm text-gray-500 mt-6">
				Sudah punya akun?
				<a href="/login" class="text-indigo-600 font-semibold">
					Login
				</a>
			</p>

		</div>

	</div>

</div>