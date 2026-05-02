<script>
	// Svelte 5 (no runes needed here yet)
</script>

<!-- HERO -->
<section
	class="flex min-h-screen items-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white"
>
	<div class="mx-auto grid max-w-6xl items-center gap-10 px-6 md:grid-cols-2">
		<!-- LEFT -->
		<div>
			<h1 class="text-4xl leading-tight font-bold md:text-5xl">Sistem Aduan Masyarakat</h1>

			<p class="mt-4 text-lg text-white/80">
				Laporkan masalah di sekitar Anda dengan mudah dan cepat.
			</p>

			<div class="mt-6 flex gap-4">
				<a
					href="/login"
					class="rounded-xl bg-white px-6 py-3 font-semibold text-indigo-600 transition hover:bg-gray-100"
				>
					Login
				</a>

				<a
					href="/register"
					class="rounded-xl border border-white px-6 py-3 transition hover:bg-white hover:text-indigo-600"
				>
					Register
				</a>
			</div>
		</div>

		<!-- RIGHT -->
		<div class="hidden md:block">
			<img
				src="https://illustrations.popsy.co/white/work-from-home.svg"
				alt="illustration"
				class="w-full"
			/>
		</div>
	</div>
</section>

<!-- FEATURES -->
<section class="bg-gray-50 py-16">
	<div class="mx-auto max-w-6xl px-6 text-center">
		<h2 class="text-3xl font-bold text-gray-800">Kenapa menggunakan platform ini?</h2>

		<div class="mt-10 grid gap-8 md:grid-cols-3">
			<div class="rounded-2xl bg-white p-6 shadow">
				<h3 class="text-lg font-semibold">Mudah Digunakan</h3>
				<p class="mt-2 text-gray-500">Interface sederhana dan cepat digunakan.</p>
			</div>

			<div class="rounded-2xl bg-white p-6 shadow">
				<h3 class="text-lg font-semibold">Upload Gambar</h3>
				<p class="mt-2 text-gray-500">Sertakan bukti visual laporan Anda.</p>
			</div>

			<div class="rounded-2xl bg-white p-6 shadow">
				<h3 class="text-lg font-semibold">Tracking Status</h3>
				<p class="mt-2 text-gray-500">Pantau progres aduan secara real-time.</p>
			</div>
		</div>
	</div>
</section>

<!-- FOOTER -->
<footer class="bg-gray-900 py-6 text-center text-white">
	<p>© 2026 Aduan Masyarakat</p>
</footer>
