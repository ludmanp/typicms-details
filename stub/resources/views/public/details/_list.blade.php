<ul class="object-detail-list-list">
    @foreach ($items as $detail)
    @include('objects::public.details._list-item')
    @endforeach
</ul>
