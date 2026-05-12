<x-admin.layout.app title="Sửa gói cước" active="plans">
	{{-- Breadcrumb --}}
	<div class="mb-4 flex items-center gap-2 text-sm">
		<a href="{{ route('admin.plans.index') }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Gói cước</a>
		<svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
		<span class="text-text-primary font-medium">{{ $plan->name }}</span>
	</div>

	<div class="max-w-2xl bg-white border border-border-light rounded-xl p-5 sm:p-6 shadow-card">
		@include('admin.plans.partials.form', [
			'action' => route('admin.plans.update', $plan),
			'method' => 'PUT',
			'plan' => $plan,
		])
	</div>
</x-admin.layout.app>
