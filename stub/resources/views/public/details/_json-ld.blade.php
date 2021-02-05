{{--
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "",
    "name": "{{ $detail->title }}",
    "description": "{{ $detail->summary !== '' ? $detail->summary : strip_tags($detail->body) }}",
    "image": [
        "{{ $detail->present()->image() }}"
    ],
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ $detail->uri() }}"
    }
}
</script>
--}}
