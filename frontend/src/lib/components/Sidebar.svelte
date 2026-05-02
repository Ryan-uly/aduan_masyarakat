<script lang="ts">
	import { page } from '$app/stores';
	import { auth } from '$lib/stores/auth';
	import { goto } from '$app/navigation';

	interface MenuItem {
		label: string;
		href: string;
		icon: string;
	}

	const menuItems: MenuItem[] = [
		{ label: 'Dashboard', href: '/dashboard', icon: 'home' },
		{ label: 'Aduan Saya', href: '/dashboard/complaints', icon: 'document' },
		{ label: 'Profile', href: '/dashboard/profile', icon: 'user' },
		{ label: 'Pengaturan', href: '/dashboard/settings', icon: 'cog' }
	];

	function handleLogout() {
		auth.logout();
		goto('/login');
	}

	let isCollapsed = $state(false);
</script>

<aside
	class="fixed left-0 top-0 h-screen bg-white shadow-lg transition-all duration-300 {isCollapsed
		? 'w-16'
		: 'w-64'}"
>
	<div class="flex h-full flex-col">
		<!-- Logo -->
		<div class="flex h-16 items-center justify-between border-b px-4">
			{#if !isCollapsed}
				<span class="text-lg font-bold text-indigo-600">AduanMas</span>
			{/if}
			<button
				onclick={() => (isCollapsed = !isCollapsed)}
				class="rounded-lg p-2 hover:bg-gray-100"
			>
				<svg
					class="h-5 w-5 text-gray-600 transition {isCollapsed ? 'rotate-180' : ''}"
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24"
				>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
				</svg>
			</button>
		</div>

		<!-- Menu -->
		<nav class="flex-1 space-y-1 p-3">
			{#each menuItems as item}
				{@const isActive = $page.url.pathname === item.href || $page.url.pathname.startsWith(item.href + '/')}
				<a
					href={item.href}
					class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition {isActive
						? 'bg-indigo-50 text-indigo-600'
						: 'text-gray-600 hover:bg-gray-50'}"
				>
					{#if item.icon === 'home'}
						<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
						</svg>
					{:else if item.icon === 'document'}
						<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
						</svg>
					{:else if item.icon === 'user'}
						<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
						</svg>
					{:else if item.icon === 'cog'}
						<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
						</svg>
					{/if}
					{#if !isCollapsed}
						<span class="text-sm font-medium">{item.label}</span>
					{/if}
				</a>
			{/each}
		</nav>

		<!-- Logout -->
		<div class="border-t p-3">
			<button
				onclick={handleLogout}
				class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-red-600 transition hover:bg-red-50"
			>
				<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
				</svg>
				{#if !isCollapsed}
					<span class="text-sm font-medium">Logout</span>
				{/if}
			</button>
		</div>
	</div>
</aside>