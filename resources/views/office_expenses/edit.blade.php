<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Office accounting</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Edit Office Expense</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('office-expenses.update', $expense->id) }}" class="bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            @include('office_expenses.form', ['expense' => $expense])
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('office-expenses.show', $expense->id) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a><button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Save expense</button></div>
        </form>
    </div></div>
</x-app-layout>
