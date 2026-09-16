@if($paginator ?? false)
    <div class="pager">{{ $paginator->links() }}</div>
@endif
