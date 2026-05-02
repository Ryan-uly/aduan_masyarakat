<script lang="ts">
	import { auth } from '$lib/stores/auth';
	import Card from '$lib/components/ui/Card.svelte';
</script>

<div class="space-y-6">
	<h1 class="text-2xl font-bold text-gray-900">Profile</h1>

	<Card>
		<div class="flex items-center gap-4">
			<div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-600">
				{$auth.user?.name?.charAt(0) || 'U'}
			</div>
			<div>
				<p class="text-lg font-semibold">{$auth.user?.name || 'User'}</p>
				<p class="text-gray-500">{$auth.user?.email || '-'}</p>
			</div>
		</div>
	</Card>
</div>