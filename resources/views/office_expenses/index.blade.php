<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Office accounting</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Office Expenses</h2>
            </div>
            <a href="{{ route('office-expenses.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New expense</a>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase text-[#9f7957]">Total recorded</p><p class="mt-2 text-2xl font-extrabold text-[#030203]">{{ number_format($summary['total'], 2) }}</p></div>
                <div class="bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase text-[#9f7957]">Unpaid</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($summary['unpaid'], 2) }}</p></div>
                <div class="bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase text-[#9f7957]">Paid</p><p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($summary['paid'], 2) }}</p></div>
            </section>

            @php
                $filterCount = collect(['expense_type', 'payment_status', 'date_from', 'date_to'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" @click="filtersOpen = ! filtersOpen" class="text-left text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                    <div class="flex gap-4">
                        @if($filterCount)
                            <a href="{{ route('office-expenses.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                        <a href="{{ route('office-expenses.export', request()->query()) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Export CSV</a>
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-4">
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Type
                        <select name="expense_type" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" @selected(request('expense_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Status
                        <select name="payment_status" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Due From
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Due To
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                    </label>
                </div>
            </form>

            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Expense</th>
                                <th class="px-5 py-4">Due</th>
                                <th class="px-5 py-4">Paid</th>
                                <th class="px-5 py-4">Amount</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($expenses as $expense)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('office-expenses.show', $expense->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $expense->expense_type }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $expense->description ?: 'No description' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $expense->due_date ? \Illuminate\Support\Carbon::parse($expense->due_date)->format('M d, Y') : 'No due date' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $expense->payment_date ? \Illuminate\Support\Carbon::parse($expense->payment_date)->format('M d, Y') : 'Not paid' }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($expense->amount, 2) }}</td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('office-expenses.toggle-paid', $expense->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="px-3 py-1 text-xs font-bold {{ $expense->payment_status === 'Paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}" onclick="return confirm('{{ $expense->payment_status === 'Paid' ? 'Mark this expense as unpaid?' : 'Mark this expense as paid?' }}')">{{ $expense->payment_status }}</button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('office-expenses.edit', $expense->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Edit</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-[#554b45]">No office expenses recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div>{{ $expenses->links() }}</div>
        </div>
    </div>
</x-app-layout>
