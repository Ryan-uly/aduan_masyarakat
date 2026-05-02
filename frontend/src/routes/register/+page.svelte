<script lang="ts">
	import { registerRequest } from '$lib/api/auth/register';
	import { auth } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import AuthLayout from '$lib/components/AuthLayout.svelte';
	import { Button, Card, Input, Alert } from '$lib/components/ui';

	let name = $state('');
	let email = $state('');
	let password = $state('');
	let confirmPassword = $state('');
	let error = $state('');
	let loading = $state(false);

	async function handleRegister() {
		error = '';

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
			const data = await registerRequest(name, email, password, confirmPassword);

			if (data.token) {
				auth.setToken(data.token);
				auth.setUser(data.user);
				goto('/dashboard');
			} else {
				goto('/login');
			}
		} catch (e: any) {
			error = e?.message || 'Register gagal';
		} finally {
			loading = false;
		}
	}
</script>

<AuthLayout>
	<Card class="w-full max-w-md">
		<div class="text-center">
			<h2 class="text-2xl font-bold text-gray-800">Register</h2>
			<p class="mt-2 text-gray-500">Buat akun baru</p>
		</div>

		{#if error}
			<Alert type="error" class="mt-4">{error}</Alert>
		{/if}

		<form onsubmit={(e) => { e.preventDefault(); handleRegister(); }} class="mt-6 space-y-4">
			<Input
				type="text"
				placeholder="Nama"
				bind:value={name}
				name="name"
				required
				disabled={loading}
			/>

			<Input
				type="email"
				placeholder="Email"
				bind:value={email}
				name="email"
				required
				disabled={loading}
			/>

			<Input
				type="password"
				placeholder="Password"
				bind:value={password}
				name="password"
				required
				disabled={loading}
			/>

			<Input
				type="password"
				placeholder="Konfirmasi Password"
				bind:value={confirmPassword}
				name="password_confirmation"
				required
				disabled={loading}
			/>

			<Button type="submit" class="w-full" disabled={loading}>
				{loading ? 'Loading...' : 'Register'}
			</Button>
		</form>

		<p class="mt-6 text-center text-sm text-gray-500">
			Sudah punya akun?
			<a href="/login" class="font-semibold text-indigo-600">Login</a>
		</p>
	</Card>
</AuthLayout>