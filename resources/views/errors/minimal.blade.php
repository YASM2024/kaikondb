<x-kaikon::app-layout>
  @slot('header')
    エラー
  @endslot
  <div class="container mt-4 py-2">
    <div class="pt-2 row w-100 my-md-3 mx-0">
        <h2 class="my-3 px-3 px-md-0">
          @yield('code') -
          @yield('message')
        </h2>
        <p>@yield('error_additional_comment')</p>
        <p>お手数ですが、次のページにアクセスしてください。</p>
        <p>山梨県の昆虫データベース: <a href="{{ url('/') }}">{{ url('/') }}</a></p>
    </div>
  </div>
  @slot('scripts')
  @endslot
</x-kaikon::app-layout>