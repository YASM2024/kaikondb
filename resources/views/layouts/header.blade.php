
<header>
  <nav class="navbar navbar-expand-md fixed-top bg-body-secondary" data-bs-theme="dark">
    <div class="container-fluid">
      <a class="h6 mt-2 fw-bold navbar-brand" href="{{ route('home') }}">{{__('settings.ProjectTitle')}}</a>
      <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-header" aria-controls="navbar-header" aria-expanded="false" aria-label="ナビゲーションの切替">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-collapse collapse" id="navbar-header" style="">
        <hr class="border-light">
        <ul class="navbar-nav ms-auto">
          @if(env('LITERATURES')==1)
          <li class="nav-item nav-item-border fs-6">
            <a class="nav-link active" aria-current="page" href="{{url('articles')}}">{{ __('messages.Literatures') }}</a>
          </li>
          @endif
          @if(env('SPECIMENS')==1)
          <li class="nav-item nav-item-border fs-6">
            <a class="nav-link active" aria-current="page" href="{{url('specimens')}}">{{__('messages.Specimens')}}</a>
          </li>
          @endif
          @if(env('INVENTORY')==1)
          <li class="nav-item nav-item-border">
            <a class="nav-link active" aria-current="page" href="{{url('species')}}">{{ __('messages.Inventory') }}</a>
          </li>
          @endif
          @if(env('PHOTOS')==1)
          <li class="nav-item nav-item-border">
            <a class="nav-link active" aria-current="page" href="
            @if(Auth::check())
            {{url('photos').'?user_id='.\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->id}}
            @else
            {{url('photos')}} 
            @endif
            ">{{ __('messages.Photos') }}</a>
          </li>
          @endif
          <hr class="border-light">
          <li class="nav-item dropdown">
            <a class="ps-md-4 py-0 nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              @if (Auth::check() && isset(\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->profile->icon))
              <img src="{{url('storage/profile/'.\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->profile->icon, null, true)}}" class="round img-fluid" style="width:3em; height:3em; border-radius:50%;">
              @else
              <svg width="3em" height="3em"><use xlink:href="{{url('/')}}/svg/symbols.svg#people-circle"></use></svg>
              @endif
              <span class="d-inline d-md-none d-lg-inline" style="vertical-align: baseline;">
                @if (Auth::check())
                {{\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->name}}
                @else
                Guest
                @endif
              </span>
            </a>
            <ul class="dropdown-menu">
            <li>
              <span class="dropdown-item">言語選択</span>
              <div id="langarea" class="d-inlline ms-3 mt-1" style="width: 6em;">
                <select id="langSelect" class="form-control">
                  <option value="ja" {{ session('locale', config('app.locale')) === 'ja' ? 'selected' : '' }}>日本語</option>
                  <option value="en" {{ session('locale', config('app.locale')) === 'en' ? 'selected' : '' }}>English</option>                </select>
              </div>
            </li>
              @if (Auth::check())
              <li><a class="dropdown-item" href="{{route('dashboard')}}">管理者メニュー</a></li>
              <li><a class="dropdown-item" href="{{route('profile.edit')}}">マイページ</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="{{route('logout')}}" method="post">
                  @csrf
                  <button class="dropdown-item">ログアウト</button>
                </form>
              </li>
              @else
              <li><a class="dropdown-item" href="{{url('register')}}">利用者登録</a></li>
              <li><a class="dropdown-item" href="{{url('login')}}">ログイン</a></li>
              @endif
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>

<script>

    document.addEventListener("click", (event) => {
        const nav = document.querySelector("#navbar-header");
        const threeBar = document.querySelector(".navbar-toggler");
        if (nav.contains(event.target)) return;
        if (nav.classList.contains("show")) {
            threeBar.click();
        }
    });

    (function() {
      const langSelect = document.getElementById('langSelect');
      if (!langSelect) return;
      langSelect.addEventListener('change', function() {
        const selectedLang = this.value;
        if (!['ja', 'en'].includes(selectedLang)) return;
        const redirectUrl = "{{ route('lang.switch', ['lang' => '__LANG__']) }}".replace('__LANG__', selectedLang);        
        if (redirectUrl) window.location.href = redirectUrl;
      });
    })();

</script>