{{--
    COMPONENT: tutorial-step
    Lokasi: resources/views/components/tutorial-step.blade.php
--}}

@props([
    'nomor',
    'judul',
    'menu',
    'peran',
    'warnaPeran' => 'pink',
    'aksi'       => [],
    'catatan'    => '',
])

@php
$warnaBadge = match($warnaPeran) {
    'blue'   => 'bg-blue-50 text-blue-700',
    'purple' => 'bg-purple-50 text-purple-700',
    'green'  => 'bg-green-50 text-green-700',
    default  => 'bg-pink-50 text-pink-700',
};
$warnaNum = match($warnaPeran) {
    'blue'   => 'bg-blue-50 text-blue-700 ring-blue-200',
    'purple' => 'bg-purple-50 text-purple-700 ring-purple-200',
    'green'  => 'bg-green-50 text-green-700 ring-green-200',
    default  => 'bg-pink-50 text-pink-700 ring-pink-200',
};
$warnaDot = match($warnaPeran) {
    'blue'   => 'bg-blue-400',
    'purple' => 'bg-purple-400',
    'green'  => 'bg-green-400',
    default  => 'bg-pink-400',
};
@endphp

<div class="flex gap-4 items-start">
    <div class="flex-shrink-0 w-10 h-10 rounded-full ring-2 flex items-center justify-center text-base font-semibold {{ $warnaNum }}">
        {{ $nomor }}
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-800">{{ $judul }}</h3>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $warnaBadge }}">{{ $peran }}</span>
        </div>
        <p class="text-xs text-gray-400 mb-3">
            Menu:&nbsp;<span class="font-medium text-gray-500">{{ $menu }}</span>
        </p>
        <ul class="space-y-2 mb-3">
            @foreach ($aksi as $item)
            <li class="flex items-start gap-2.5 text-sm text-gray-600 leading-snug">
                <span class="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full {{ $warnaDot }}"></span>
                {{ $item }}
            </li>
            @endforeach
        </ul>
        @if ($catatan)
        <div class="rounded-lg border-l-4 border-pink-300 bg-pink-50 px-3 py-2">
            <p class="text-xs text-pink-700 leading-relaxed">
                <span class="font-semibold">Catatan:</span> {{ $catatan }}
            </p>
        </div>
        @endif
    </div>
</div>