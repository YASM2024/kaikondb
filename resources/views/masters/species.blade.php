<x-kaikon::app-layout>
  @slot('header')
    分類マスタ
  @endslot
  <script src="https://unpkg.com/vue@3.5.12/dist/vue.global.prod.js"></script>
  <style>
  /* コンテナのカスタマイズ */
    @media (min-width: 768px) {.container { max-width: 736px;}}
    tbody > tr :hover{ cursor: pointer;}
    .rowItem > tr > span{ color: red;}
  </style>

  <div class="container mt-4 py-2">
      <h4 class="my-3 px-0 mx-2">種（Species）マスタ</h4>
      <h5 class="my-3 px-0">現在の設定内容</h5>
      <div id="app" class="px-3" style="text-align: start;"></div>
  </div>

  @slot('scripts')
  <script type="module" src="{{url('/')}}/js/showTaxonMaster.js"></script>
  @endslot
</x-kaikon::app-layout>