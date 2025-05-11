<x-kaikon::app-layout>
  @slot('header')
    {{ __('messages.Home') }}
  @endslot
  <style>
    /** TOP GET PHOTOS */
    .bg-body-bg-photos{
        position: relative;
        z-index: 0;
    }

    .bg-body-bg-photos:before{
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-position:
      @for ($i = 0; $i < 10; $i++)
      @for ($j = 0; $j < 4; $j++)
      @if( $i == 9 && $j == 3)
      {{$i * 200}}px {{420 - $j * 140}}px;
      @else
      {{$i * 200}}px {{420 - $j * 140}}px,
      @endif
      @endfor
      @endfor

      background-image: 
      @foreach ( $photos as $photo )
      @if( !$loop->last )
      url('./storage/photos/{{ $photo->thumbnail_url }}'),
      @elseif( $loop->last )
      url('./storage/photos/{{ $photo->thumbnail_url }}');
      @else
      @endif
      @endforeach

      background-repeat:no-repeat;
      z-index: -1;
      opacity: 0.5;
    }

    /** TOP PHOTOS EFFECT */
    .bg-body-text{
      position: relative;
      z-index: 0;
    }
    .bg-body-text:before{
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(ellipse at center, #eeeeee 50%, transparent);
      z-index: -1;
      opacity: 0.5;
    }
      /* カルーセルをカスタマイズ
  -------------------------------------------------- */

  /* カルーセルの基本クラス */
  .carousel {
    margin-bottom: 4rem;
  }
  /* 画像を配置しているので、キャプションを手助けする必要がある */
  .carousel-caption {
    bottom: 3rem;
    z-index: 10;
  }

  /* img要素の配置のために高さを宣言 */
  .carousel-item {
    /*height: 28rem;*/
    height: 32rem;
  }


  /* マーケティング・コンテンツ
  -------------------------------------------------- */

  /* カルーセルの下の3列の中のテキストを整列 */
  .marketing .col-lg-4 {
    margin-bottom: 1.5rem;
    text-align: center;
  }
  .marketing .col-lg-4 p {
    margin-right: .75rem;
    margin-left: .75rem;
  }


  /* フィーチャー
  ------------------------- */

  .featurette-divider {
    margin: 5rem 0; /* ブートストラップの&lt;hr&gt;をもっと外に出す */
  }

  /* マーケティングの見出しを細めにする */
  .featurette-heading {
    letter-spacing: -.05rem;
  }

  .costom-card-layout {
        margin: 1em;
        display: block;
      }

  /* レスポンシブCSS
  -------------------------------------------------- */

  @media (min-width: 40em) {
    /* カルーセルコンテンツのサイズを大きくする */
    .carousel-caption p {
      margin-bottom: 1.25rem;
      font-size: 1.25rem;
      line-height: 1.4;
    }

    .featurette-heading {
      font-size: 50px;
    }

    .costom-card-layout {
        display: inline;
      }
  }

  @media (min-width: 62em) {
    .featurette-heading {
      margin-top: 7rem;
    }
  }
  </style>
  <div class="pt-2 row w-100 my-md-3 mx-0">
    <div id="myCarousel" class="carousel slide mb-0 px-0" data-bs-theme="dark"><!-- data-bs-ride="carousel">-->
      <!-- インジケータ -->
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="スライド 1"></button>
        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="スライド 2"></button>
      </div>
      <div class="carousel-inner" data-bs-theme="light">
        <div class="carousel-item active">
          <div class="text-center bg-body-bg-photos h-100">
            <div class="py-5 col-md-8 p-lg-12 mx-auto bg-body-text">
              <h1 class="display-6 fw-bold">{{ __('settings.FirstMessage') }}</h1>
              <h6 class="lead fw-bold my-3">{{ __('settings.SubTitle') }}</h6>
              <div class="costom-card-layout" style="font-family: 'Roboto', sans-serif;">
                <a class="text-decoration-none card rounded-pill py-2 icon-link fw-bold" href="#">
                  <div class="px-4 text-start w-100 small">収録文献数</div>
                  <div class="text-danger px-5 h4 w-100 fw-bold">{{ number_format($articles_count) }} 件</div>
                  <div class="px-5 w-100 small">（{{ $articles_last_update }}時点）</div>  
                </a>
              </div>
              <div class="costom-card-layout" style="font-family: 'Roboto', sans-serif;">
                <a class="text-decoration-none card rounded-pill py-2 icon-link fw-bold" href="#">
                  <div class="px-4 text-start w-100 small">掲載種数</div>
                  <div class="text-danger px-3 h4 w-100 fw-bold">{{ number_format($species_count) }} 種(亜種)</div>
                  <div class="px-5 w-100 small">（{{ $species_last_update }}時点）</div> 
                </a>
              </div>
              <div class="display-7 fw-bold mt-4 mt-sm-5">
                「山梨の昆虫、どうなっているの？」<br>その疑問、一緒に解決しませんか
                <div class="fw-bold pb-4"><a href="{{$url = route('expanded_page', ['route_name' => 'memo']) }}">詳しくはこちら</a></div>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
        <div class="text-center h-100 bg-primary-subtle">
            <div class="py-5 col-md-8 p-lg-12 mx-auto text-dark">
              <h3 class="fw-bold mt-2 mb-3">ご協力のお願い</h3>
              <p>山梨県の昆虫相を一緒に解明しませんか？<br>
              プロジェクトには、あなたの力が必要です。</p>
              <div class="container"><img class="rounded mt-2 mt-sm-0" style="max-width: 100%; max-height: 16rem;" src="{{ url('/storage/img/sample.jpg') }}"></div>
              <a class="d-block mt-3" href="{{$url = route('expanded_page', ['route_name' => 'volunteer']) }}">簡単な情報提供から、運営参画まで</a>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">前へ</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">次へ</span>
      </button>
    </div><!-- /.carousel -->

    @if(env('LITERATURES')==1)
    <div class="col-md-6 text-bg-dark px-3 pt-md-5 px-md-5 text-center overflow-hidden">
      <div class="my-3 py-2">
        <h2 class="display-6 fw-bold">
          <a href="{{ route('articles') }}" class="text-light text-decoration-none">
          {{ __('messages.Literatures') }}<svg class="bi ms-3" width="20" height="20"><use xlink:href="./svg/symbols.svg#chevron-right"></use></svg>
          </a>
        </h2>
        <div class="py-2 fw-bold">収録文献数：{{ number_format($articles_count) }} 件</div>
        <p>学術出版物、商業誌、同好会誌および図鑑等の単行本の情報を検索できます。</p>
      </div>
      <div id="modalArticleChart" class="modalChart mb-3">
        <canvas id="mychart-pie-articles" width="502" height="450" style="display: block; box-sizing: border-box; height: 300px; width: 335.3px;"></canvas>
      </div>
    </div>
    @endif
    @if(env('INVENTORY')==1)
    <div class="col-md-6 bg-body-tertiary px-3 pt-md-5 px-md-5 text-center overflow-hidden">
      <div class="my-3 py-2">
        <h2 class="display-6 fw-bold">
          <a href="{{ route('species') }}" class="text-dark text-decoration-none">
          {{ __('messages.Inventory') }}<svg class="bi ms-3" width="20" height="20"><use xlink:href="./svg/symbols.svg#chevron-right"></use></svg>
          </a>
        </h2> 
        <div class="py-2 fw-bold">掲載種数：{{ number_format($species_count) }} 種(亜種)</div>
        <p>文献にて正式に記録された昆虫を分類に基づき整理していきます。</p>
      </div>
      <div id="modalSpeciesChart" class="modalChart mb-3">
        <canvas id="mychart-pie-species" width="502" height="450" style="display: block; box-sizing: border-box; height: 300px; width: 335.3px;"></canvas>
      </div>
    </div>
    @endif
  </div>
  @slot('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@next/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
  <script>
    const ctx1 = document.getElementById('mychart-pie-articles');
    console.log()
    fetch("{{route('chart')}}")
    .then((data) => {return data.json()})
    .then((chartData) => {
      let myArticlesChart = new Chart(ctx1, {
        type: 'pie',
        data: chartData['articles'],
        options: {
          plugins: {
            legend: {
              labels: {
                color: '#dddddd', // 文字色を白に設定
              }
          }},
          maintainAspectRatio: false
        }
      })
      history.pushState(null,null,location.href);
    })

    /*
    options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: {
                color: 'white'
                font: {
                  size: 16 // 文字サイズを大きく設定
                }
              }
            }
          }
        }
    */

    const ctx2 = document.getElementById('mychart-pie-species');
    fetch("{{route('chart')}}")
    .then((data) => {return data.json()})
    .then((chartData) => {
      let mySpeciesChart = new Chart(ctx2, {
        type: 'pie',
        data: chartData['species'],
        options: {
          maintainAspectRatio: false
        }
      })
      history.pushState(null,null,location.href);
    })
        
  </script>
  @endslot
</x-kaikon::app-layout>