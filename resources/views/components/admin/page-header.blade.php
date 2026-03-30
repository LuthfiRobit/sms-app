@props(['title', 'breadcrumbs' => []])

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $title }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') ?? '#' }}"><i class="bi bi-house-door"></i></a></li>
                    @foreach($breadcrumbs as $label => $url)
                        @if($loop->last || is_numeric($label))
                            <li class="breadcrumb-item" aria-current="page">{{ is_numeric($label) ? $url : $label }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
