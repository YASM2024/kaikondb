<!DOCTYPE html>
<html lang="ja" data-bs-theme="light">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $header ?? '' }}｜{{__('settings.ProjectTitle')}}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <meta name="description" content="山梨県の昆虫の文献情報の収集、ファウナ解明を目的とするページ。">

  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-4748KN8NR1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-4748KN8NR1');
  </script>
  <!--// Google tag (gtag.js) -->

  <!-- Bing tag -->
  <meta name="msvalidate.01" content="49BA5C997CC410813F31B99F35CBA70C" />
  <!--// Bing tag -->

  <link rel="shortcut icon" href="{{ url('/favicon.ico') }}">
  <link rel="stylesheet" href="{{ url('/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{ url('/css/highlight.css')}}">
  <link rel="stylesheet" href="{{ url('/css/addstyle.css')}}">
  <link rel="stylesheet" href="{{ url('/css/bootstrap-icons.min.css')}}">

  <style>
  /* 等幅フォントを設定
  body {
    font-family: 'IPAゴシック', 'Noto Sans Mono', 'Noto Sans Mono CJK JP', monospace;
  } */
  .container {  
      max-width: 960px;
  }
  .icon-link > .bi {  
      width: .75em;  
      height: .75em;
  }
  /* * カスタムした半透明サイトのヘッダ */
  .site-header {  
      background-color: rgba(0, 0, 0, .85);  
      -webkit-backdrop-filter: saturate(180%) blur(20px);  
      backdrop-filter: saturate(180%) blur(20px);
  }
  .site-header a {  
      color: #8e8e8e;  
      transition: color .15s ease-in-out;
  }
  .site-header a:hover {  
      color: #fff;  
      text-decoration: none;
  }
  /* * 追加のユーティリティ */
  .flex-equal > * {  
      -ms-flex: 1;  
      flex: 1;
  }
  @media (min-width: 768px) {
        .flex-md-equal > * {
              -ms-flex: 1;    flex: 1;  
          }
  }
  /** ライセンス表示 */
  #powered-by {
    position: absolute;
    right: 10px;
    bottom: 10px;
  }
  /** 言語表示 */
  #langSelect {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
  }

  /**  */
  .nav-item-border {
    border-left: 1px solid #6c757d;
  }
  @media (max-width: 767.98px) {
    .nav-item-border {
      border-left: none;
    }
  }
  </style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js" integrity="sha512-VMsZqo0ar06BMtg0tPsdgRADvl0kDHpTbugCBBrL55KmucH6hP9zWdLIWY//OTfMnzz6xWQRxQqsUFefwHuHyg==" crossorigin="anonymous"></script>
</head>
<body>

<body class="bg-light">

<!-- HEADER -->
@include('kaikon::layouts.header')
<!-- HEADER -->


<!-- MAIN -->
<main>
  <hr class="pb-4">

{{ $slot }}

</main>
<!--/ MAIN -->


<!-- FOOTER -->
@include('kaikon::layouts.footer')
<!-- FOOTER -->


<div id="powered-by" class="small text-body-secondary">Powered by KAIKON-DB</div>

{{ $modal ?? '' }}

  <!-- default script -->
  <script src="{{ url('/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{ url('/js/highlight.min.js')}}"></script>
  <script>hljs.highlightAll();</script>
  <script>
    const CONFIG = Object.freeze({
        baseUrl: "{{ config('app.url') }}"
    });
  </script>
  <!-- component additional script -->
  {{ $scripts ?? '' }}
  
</body>
</html>
