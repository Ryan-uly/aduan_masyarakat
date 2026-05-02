<script lang="ts">
	import { loginRequest } from '$lib/api/auth/login';
	import { auth } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import AuthLayout from '$lib/components/AuthLayout.svelte';
	import { Button, Card, Input, Alert } from '$lib/components/ui';

	let email = $state('');
	let password = $state('');
	let error = $state('');
	let loading = $state(false);

	async function handleLogin() {
		loading = true;
		error = '';

		try {
			const data = await loginRequest(email, password);
			auth.setToken(data.token);
			auth.setUser(data.user);
			goto('/dashboard');
		} catch (e: any) {
			error = e?.message || 'Login gagal';
		} finally {
			loading = false;
		}
	}
</script>

<AuthLayout>
	<Card class="w-full max-w-md">
		<div class="text-center">
			<h2 class="text-2xl font-bold text-gray-800">Login</h2>
			<p class="mt-2 text-gray-500">Masuk ke akun kamu</p>
		</div>

		{#if error}
			<Alert type="error" class="mt-4">{error}</Alert>
		{/if}

		<form onsubmit={(e) => { e.preventDefault(); handleLogin(); }} class="mt-6 space-y-4">
			<Input
				type="email"
				placeholder="Email"
				bind:value={email}
				name="email"
				required
			/>

			<Input
				type="password"
				placeholder="Password"
				bind:value={password}
				name="password"
				required
			/>

			<Button type="submit" class="w-full" disabled={loading}>
				{loading ? 'Loading...' : 'Login'}
			</Button>
		</form>

		<p class="mt-6 text-center text-sm text-gray-500">
			Belum punya akun?
			<a href="/register" class="font-semibold text-indigo-600"> Register </a>
		</p>
	</Card>
</AuthLayout>