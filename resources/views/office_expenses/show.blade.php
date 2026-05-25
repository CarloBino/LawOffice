<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Office accounting</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $expense->expense_type }}</h2></div><a href="{{ route('office-expenses.edit', $expense->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit</a></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <section class="bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Amount</p><p class="mt-2 text-2xl font-extrabold text-[#030203]">{{ number_format($expense->amount, 2) }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Status</p><p class="mt-2 text-2xl font-extrabold {{ $expense->payment_status === 'Paid' ? 'text-emerald-700' : 'text-red-700' }}">{{ $expense->payment_status }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Due Date</p><p class="mt-2 font-semibold text-[#030203]">{{ $expense->due_date ? \Illuminate\Support\Carbon::parse($expense->due_date)->format('M d, Y') : 'Not recorded' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Payment Date</p><p class="mt-2 font-semibold text-[#030203]">{{ $expense->payment_date ? \Illuminate\Support\Carbon::parse($expense->payment_date)->format('M d, Y') : 'Not paid' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Receipt Number</p><p class="mt-2 font-semibold text-[#030203]">{{ $expense->receipt_number ?: 'Not recorded' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Description</p><p class="mt-2 font-semibold text-[#030203]">{{ $expense->description ?: 'Not recorded' }}</p></div>
                <div class="sm:col-span-2"><p class="text-xs font-bold uppercase text-[#9f7957]">Notes</p><p class="mt-2 text-[#554b45]">{{ $expense->notes ?: 'No notes' }}</p></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5">
                <a href="{{ route('office-expenses.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to expenses</a>
                <form method="POST" action="{{ route('office-expenses.toggle-paid', $expense->id) }}">
                    @csrf
                    @method('PATCH')
                    <button class="bg-[#c7a47b] px-4 py-2 text-sm font-bold text-[#030203] hover:bg-[#030203] hover:text-white">{{ $expense->payment_status === 'Paid' ? 'Mark unpaid' : 'Mark paid' }}</button>
                </form>
            </div>
        </section>
    </div></div>
</x-app-layout>
