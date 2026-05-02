<script lang="ts">
	import type { ComplaintStatus } from '$lib/types';

	interface Props {
		status: ComplaintStatus;
	}

	let { status }: Props = $props();

	const statusConfig: Record<ComplaintStatus, { bg: string; text: string; label: string }> = {
		pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Menunggu' },
		process: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Diproses' },
		completed: { bg: 'bg-green-100', text: 'text-green-800', label: 'Selesai' },
		rejected: { bg: 'bg-red-100', text: 'text-red-800', label: 'Ditolak' }
	};

	const config = $derived(statusConfig[status] || { bg: 'bg-gray-100', text: 'text-gray-800', label: status });
</script>

<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {config.bg} {config.text}">
	{config.label}
</span>