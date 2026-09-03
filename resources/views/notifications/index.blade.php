<x-layouts::app title="Notifications">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Notifications & Alerts</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Low stock warnings, daily closing notices, and waste alarms</p>
            </div>

            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] hover:bg-[var(--fc-bg)]">
                    Mark All as Read
                </button>
            </form>
        </div>

        <div class="fc-card overflow-hidden shadow-xs divide-y divide-[var(--fc-border)]">
            @forelse($notifications as $notif)
                <div class="p-4 flex items-start justify-between gap-4 {{ $notif->is_read ? 'opacity-70' : 'bg-emerald-500/5 font-medium' }}">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-xs text-[var(--fc-text)]">{{ $notif->title }}</h4>
                            <span class="text-[10px] text-[var(--fc-text-muted)]">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-[var(--fc-text-muted)]">{{ $notif->message }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($notif->link)
                            <a href="{{ $notif->link }}" class="text-xs font-bold text-[var(--fc-primary)] hover:underline">
                                View &rarr;
                            </a>
                        @endif

                        @if(!$notif->is_read)
                            <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-semibold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">
                                    Done
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-[var(--fc-text-muted)]">
                    <flux:icon name="bell-slash" class="size-8 text-[var(--fc-text-muted)] mx-auto mb-2 opacity-50" />
                    <p>You have no notifications right now.</p>
                </div>
            @endforelse
        </div>

        <div>
            {{ $notifications->links() }}
        </div>
    </div>
</x-layouts::app>
