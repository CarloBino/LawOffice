<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Expense Type</label>
        <select name="expense_type" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
            @foreach(['Rent Fee', 'Electricity Fee', 'WiFi Fee', 'Office Supplies', 'Salary', 'Others'] as $type)
                <option value="{{ $type }}" @selected(old('expense_type', optional($expense)->expense_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Amount</label><input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', optional($expense)->amount) }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Due Date</label><input type="date" name="due_date" value="{{ old('due_date', optional($expense)->due_date) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Status</label><select name="payment_status" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">@foreach(['Unpaid', 'Paid'] as $status)<option value="{{ $status }}" @selected(old('payment_status', optional($expense)->payment_status ?? 'Unpaid') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Payment Date</label><input type="date" name="payment_date" value="{{ old('payment_date', optional($expense)->payment_date) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Receipt Number</label><input type="text" name="receipt_number" value="{{ old('receipt_number', optional($expense)->receipt_number) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
    <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Description</label><input type="text" name="description" value="{{ old('description', optional($expense)->description) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
    <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Notes</label><textarea name="notes" rows="4" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">{{ old('notes', optional($expense)->notes) }}</textarea></div>
</div>
