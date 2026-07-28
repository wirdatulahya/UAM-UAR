{{--
    Reusable Breadcrumb Component
    Usage: <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Request Access Matrix', 'url' => route('access-matrix.request.index')],
        ['label' => 'Current Page'],   // no 'url' = current/active item
    ]" />
--}}
@props(['items' => []])

<nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:.85rem;">
    <ol style="list-style:none;padding:0;margin:0;display:flex;align-items:center;gap:.2rem;font-size:.77rem;font-weight:500;flex-wrap:wrap;">
        @foreach($items as $index => $item)
            @if(!$loop->first)
                <li style="color:var(--border);font-size:.7rem;line-height:1;padding:0 .1rem;">
                    <i class="bi bi-chevron-right" style="font-size:.6rem;"></i>
                </li>
            @endif

            @if(isset($item['url']))
                <li>
                    <a href="{{ $item['url'] }}"
                       style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .35rem;border-radius:6px;transition:all var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)';this.style.background='var(--secondary-light)'"
                       onmouseleave="this.style.color='var(--text-muted)';this.style.background='transparent'">{{ $item['label'] }}</a>
                </li>
            @else
                <li style="display:flex;align-items:center;gap:.35rem;">
                    <span style="color:var(--secondary);font-weight:700;padding:.2rem .35rem;" aria-current="page">{{ $item['label'] }}</span>
                    @if(isset($item['badge']))
                        <span style="background:var(--secondary-light);color:var(--secondary);border-radius:99px;padding:.1rem .55rem;font-size:.67rem;font-weight:700;letter-spacing:.02em;">{{ $item['badge'] }}</span>
                    @endif
                </li>
            @endif
        @endforeach
    </ol>
</nav>
