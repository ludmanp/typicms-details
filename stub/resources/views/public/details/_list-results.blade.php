<ul class="object-detail-list-results-list">
    @foreach ($items as $detail)
    <li class="object-detail-list-results-item">
        <a class="object-detail-list-results-item-link" href="{{ $detail->uri() }}" title="{{ $detail->title }}">
            <span class="object-detail-list-results-item-title">{{ $detail->title }}</span>
        </a>
    </li>
    @endforeach
</ul>
