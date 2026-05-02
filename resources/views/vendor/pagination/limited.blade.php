@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">

            {{-- Previous --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
            </li>

            @php

                $maxLinks = $maxLinks ?? 5;            // max numeric links to show
                $current  = $paginator->currentPage(); // current active page
                $last     = $paginator->lastPage();    // total number of pages
                $half     = floor($maxLinks / 2);      // half window size
                
                $start = max(1, $current - $half);             // first page in the window
                $end   = min($last, $start + $maxLinks - 1);   // last page in the window
                $start = max(1, $end - $maxLinks + 1);         // adjust start if near the end

            @endphp

            {{-- First + leading dots --}}
            @if ($start > 1)
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endif

            {{-- Page window --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                @endif
            @endfor

            {{-- Trailing dots + last --}}
            @if ($end < $last)
                @if ($end < $last - 1)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($last) }}">{{ $last }}</a></li>
            @endif

            {{-- Next --}}
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
            </li>
        </ul>
    </nav>
@endif
