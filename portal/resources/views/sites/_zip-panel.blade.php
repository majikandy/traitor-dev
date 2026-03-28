@php $zipOpen = $startOpen ?? false; @endphp
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    <button type="button" id="zip-toggle"
        class="flex w-full items-center gap-3 px-6 py-4 text-left"
        aria-expanded="{{ $zipOpen ? 'true' : 'false' }}" aria-controls="zip-panel">
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-brand-50">
            <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
        </div>
        <span class="flex-1 text-base font-semibold text-gray-900">Create Release from Zip file</span>
        <svg id="zip-chevron" class="h-4 w-4 text-gray-400 transition-transform duration-200{{ $zipOpen ? ' rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div id="zip-panel" class="{{ $zipOpen ? '' : 'hidden' }} px-6 pb-6 sm:pl-[4.5rem]">
        <p class="text-sm text-gray-500 mb-4">Upload a .zip to create a new versioned release with a shareable preview link.</p>
        <form action="{{ route('sites.release', $site) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="dropzone" class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50/50 transition mb-4">
                <input type="file" name="zip" accept=".zip" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p class="dropzone-text text-sm text-gray-500">Drop a .zip or <span class="font-semibold text-brand-600">browse</span></p>
                <p class="dropzone-filename mt-1 text-sm font-semibold text-brand-600" id="filename"></p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    name="notes"
                    placeholder="Release notes (optional)"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition"
                >
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition sm:whitespace-nowrap">Create Release</button>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var btn = document.getElementById('zip-toggle');
        var panel = document.getElementById('zip-panel');
        var chevron = document.getElementById('zip-chevron');
        btn.addEventListener('click', function () {
            var hidden = panel.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!hidden));
            chevron.style.transform = hidden ? '' : 'rotate(180deg)';
        });
    })();
</script>
