<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Client Statement - {{ $client->full_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white p-8 text-[#030203]" onload="window.print()">
    <main class="mx-auto max-w-4xl">
        <p class="text-sm font-bold uppercase text-[#9f7957]">Client Statement</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ $client->full_name }}</h1>
        <p class="mt-1 text-sm text-[#554b45]">{{ now()->format('M d, Y') }}</p>

        <section class="mt-8 grid grid-cols-3 border border-[#e3e3df]">
            <div class="p-4"><p class="text-xs font-bold uppercase text-[#9f7957]">Billed</p><p class="mt-2 text-xl font-bold">{{ number_format($stats['total_billed'], 2) }}</p></div>
            <div class="border-l border-[#e3e3df] p-4"><p class="text-xs font-bold uppercase text-[#9f7957]">Paid</p><p class="mt-2 text-xl font-bold">{{ number_format($stats['amount_paid'], 2) }}</p></div>
            <div class="border-l border-[#e3e3df] p-4"><p class="text-xs font-bold uppercase text-[#9f7957]">Balance</p><p class="mt-2 text-xl font-bold">{{ number_format($stats['balance'], 2) }}</p></div>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-bold uppercase text-[#554b45]">Cases and Billings</h2>
            <table class="mt-3 w-full border-collapse text-left text-sm">
                <thead><tr class="border-b border-[#e3e3df] text-xs uppercase text-[#7a716b]"><th class="py-3">Case</th><th class="py-3">Billed</th><th class="py-3">Paid</th><th class="py-3">Balance</th><th class="py-3">Status</th></tr></thead>
                <tbody>
                    @foreach($client->cases as $case)
                        @foreach($case->billings as $billing)
                            <tr class="border-b border-[#e3e3df]">
                                <td class="py-3"><strong>{{ $case->case_number }}</strong><br>{{ $case->case_title }}</td>
                                <td class="py-3">{{ number_format($billing->total_amount, 2) }}</td>
                                <td class="py-3">{{ number_format($billing->amount_paid, 2) }}</td>
                                <td class="py-3">{{ number_format($billing->balance, 2) }}</td>
                                <td class="py-3">{{ $billing->payment_status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
