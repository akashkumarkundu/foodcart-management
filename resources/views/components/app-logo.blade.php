@props([
    'sidebar' => false,
])

<a href="{{ route('dashboard') }}" class="flex items-center gap-3 group focus:outline-none">
    <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-200">
        <x-app-logo-icon class="size-6 text-white" />
    </div>
    <div class="flex flex-col text-start leading-tight">
        <div class="flex items-center gap-1.5 font-bold text-base tracking-tight text-zinc-900 dark:text-zinc-100">
            <span>FOODCART</span><span class="text-emerald-500 font-extrabold">360</span>
            <span class="text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">BD</span>
        </div>
        <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 tracking-normal">Food Cart Smarter</span>
    </div>
</a>
