<div class="card">
    <h3>{{ $title }}</h3>
    <p>{{ $description }}</p>
    @if(!empty($link))
        <a class="btn" href="{{ $link }}">View</a>
    @endif
</div>
