<x-kaikon::app-layout>
  @slot('header')
    {{ __('messages.Home') }}
  @endslot
  <style>
    .bg-body-bg-photos {
        height: 100vh; /* 画面の高さいっぱいに設定 */
        display: flex; /* 中央配置のためのFlexbox */
        align-items: center; /* 垂直方向の中央配置 */
        justify-content: center; /* 水平方向の中央配置 */
    }
  </style>
  <div class="pt-2 row w-100 my-md-3 mx-0">
    <div id="myCarousel" class="mb-0 px-0" data-bs-theme="dark">
      <div class="text-center bg-body-bg-photos">
        <div class="py-5 col-md-8 p-lg-12 mx-auto bg-body-text">
          <h1 class="display-6 fw-bold">FirstMessage</h1>
          <h6 class="lead fw-bold my-3">SubTitle</h6>

        </div>
      </div>
    </div>
  </div>
</x-kaikon::app-layout>