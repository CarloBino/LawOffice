<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Case Summary - {{ $case->case_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white p-8 text-[#030203]" onload="window.print()">
    <main class="mx-auto max-w-4xl">
        <p class="text-sm font-bold uppercase text-[#9f7957]">Case Summary</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ $case->case_number }} - {{ $case->case_title }}</h1>
        <p class="mt-1 text-sm text-[#554b45]">{{ now()->format('M d, Y') }}</p>

        <section class="mt-8 grid grid-cols-2 border border-[#e3e3df] text-sm">
            <div class="border-b border-[#e3e3df] p-4"><strong>Client</strong><br>{{ optional($case->client)->full_name ?: 'Unassigned' }}</div>
            <div class="border-b border-l border-[#e3e3df] p-4"><strong>Lawyer</strong><br>{{ $case->assignedLawyer?->display_name ?: 'Unassigned' }}</div>
            <div class="border-b border-[#e3e3df] p-4"><strong>Status</strong><br>{{ $case->case_status }}</div>
            <div class="border-b border-l border-[#e3e3df] p-4"><strong>Priority</strong><br>{{ $case->priority_level }}</div>
            <div class="col-span-2 p-4"><strong>Description</strong><br>{{ $case->description ?: 'No description recorded.' }}</div>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-bold uppercase text-[#554b45]">Upcoming Hearings</h2>
            <ul class="mt-3 divide-y divide-[#e3e3df] text-sm">
                @forelse($case->hearings as $hearing)
                    <li class="py-3">{{ $hearing->hearing_date ?: 'No date' }} {{ $hearing->hearing_time ?: '' }} - {{ $hearing->court_branch ?: $hearing->court_venue ?: 'No venue' }} - {{ $hearing->hearing_status }}</li>
                @empty
                    <li class="py-3 text-[#554b45]">No hearings recorded.</li>
                @endforelse
            </ul>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-bold uppercase text-[#554b45]">Billing Summary</h2>
            <table class="mt-3 w-full border-collapse text-left text-sm">
                <thead><tr class="border-b border-[#e3e3df] text-xs uppercase text-[#7a716b]"><th class="py-3">Total</th><th class="py-3">Paid</th><th class="py-3">Balance</th><th class="py-3">Status</th></tr></thead>
                <tbody>
                    @forelse($case->billings as $billing)
                        <tr class="border-b border-[#e3e3df]"><td class="py-3">{{ number_format($billing->total_amount, 2) }}</td><td class="py-3">{{ number_format($billing->amount_paid, 2) }}</td><td class="py-3">{{ number_format($billing->balance, 2) }}</td><td class="py-3">{{ $billing->payment_status }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="py-3 text-[#554b45]">No billing recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
