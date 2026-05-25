<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase text-[#9f7957]">File room</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Document</h2></div><a href="{{ route('documents.edit', $document->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit document</a></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
        <section class="bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $document->document_type ?: 'File' }}</p>
            <h3 class="mt-1 text-2xl font-extrabold text-[#030203]">{{ $document->document_name }}</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Case</p><p class="mt-2 font-semibold text-[#030203]">{{ optional($document->case)->case_number ?: 'No case linked' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Client</p><p class="mt-2 font-semibold text-[#030203]">{{ optional(optional($document->case)->client)->full_name ?: 'No client linked' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-[#9f7957]">Uploaded</p><p class="mt-2 font-semibold text-[#030203]">{{ $document->created_at ? $document->created_at->format('M d, Y') : 'Not recorded' }}</p></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5">
                <a href="{{ route('documents.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to documents</a>
                @if($document->file_path)<a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="bg-[#c7a47b] px-4 py-2 text-sm font-bold text-[#030203] hover:bg-[#030203] hover:text-white">Download file</a>@endif
            </div>
        </section>
    </div></div>
</x-app-layout>
