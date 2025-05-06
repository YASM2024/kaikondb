@php
$expanded_pages = Kaikon2\Kaikondb\Models\ExpandedPage::where('open', 1)->orderBy('seq', 'asc')->get();
@endphp
<div class="clearfix"></div>
<hr class="container">  
<footer class="container pt-2 pb-5">
  <div class="row">
    <div class="col-12 col-md">
      <small class="mb-3 text-body-secondary">{{__('settings.OrganizationName')}} © {{__('settings.StartingYear')}}–{{ \Carbon\Carbon::now()->format('Y') }}</small>
    </div>
    <div class="col-6 col-md">
      <h5>{{__('messages.Contents')}}</h5>
      <ul class="list-unstyled text-small">
        @if(env('LITERATURES')==1) <li><a class="link-secondary text-decoration-none" href="{{route('articles')}}">{{ __('messages.Literatures') }}</a></li>@endif
        @if(env('SPECIMENS')==1) <li><a class="link-secondary text-decoration-none" href="{{route('specimens')}}">{{ __('messages.Specimens') }}</a></li>@endif
        @if(env('INVENTORY')==1) <li><a class="link-secondary text-decoration-none" href="{{route('species')}}">{{ __('messages.Inventory') }}</a></li>@endif
        @if(env('PHOTOS')==1) <li><a class="link-secondary text-decoration-none" href="{{route('photos')}}">{{ __('messages.Photos') }}</a></li>@endif
      </ul>
    </div>
    <div class="col-6 col-md">
      <h5>{{__('settings.ExpandedArea')}}</h5>
      <ul class="list-unstyled text-small">
        @foreach($expanded_pages as $page)
        <li><a class="link-secondary text-decoration-none" href="{{$url = route('expanded_page', ['route_name' => $page->route_name]) }}">{{ $page->title }}</a></li>
        @endforeach
      </ul>
    </div>
    
    
  </div>
</footer>