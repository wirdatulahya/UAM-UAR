{{--
    Reusable Breadcrumb Component
    Usage: <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Request Access Matrix', 'url' => route('access-matrix.request.index')],
        ['label' => 'Current Page'],   // no 'url' = current/active item
    ]" />
--}}
@props(['items' => []])

<nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:.75rem;">
    <ol style="list-style:none;padding:0;margin:0;display:flex;align-items:center;gap:.3rem;font-size:.78rem;font-weight:500;flex-wrap:wrap;">
        @foreach($items as $index => $item)
            @if(!$loop->first)
                <li style="color:#d1d5db;font-size:.7rem;">/</li>
            @endif

            @if(isset($item['url']))
                <li>
                    <a href="{{ $item['url'] }}"
                       style="color:#9ca3af;text-decoration:none;transition:color .15s;"
                       onmouseenter="this.style.color='#6b7280'"
                       onmouseleave="this.style.color='#9ca3af'">{{ $item['label'] }}</a>
                </li>
            @else
                <li style="display:flex;align-items:center;gap:.4rem;">
                    <span style="color:#1e3a5f;font-weight:600;" aria-current="page">{{ $item['label'] }}</span>
                    @if(isset($item['badge']))
                        <span style="background:#e8edf7;color:#1e3a5f;border-radius:20px;padding:.1rem .5rem;font-size:.68rem;font-weight:700;letter-spacing:.02em;">{{ $item['badge'] }}</span>
                    @endif
                </li>
            @endif
        @endforeach
    </ol>
</nav>
