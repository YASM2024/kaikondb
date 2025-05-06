<x-kaikon::app-layout>
  @slot('header')
    分類マスタ
  @endslot
    <style>
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {.container { max-width: 736px;}}
    .hover-color:hover{
        background-color: #dddddd;
    }
    </style>

    <!-- アイコンの設定 -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

    <symbol id="x" viewBox="0 0 512 512"><style>.x{fill:#4B4B4B;}</style>
        <g>
        <polygon class="x" points="512,89.75 422.256,0.005 256.004,166.256 89.754,0.005 0,89.75 166.255,256 0,422.25 89.754,511.995 
            256.004,345.745 422.26,511.995 512,422.25 345.744,256"></polygon>
        </g>
    </symbol>

    <symbol id="upload" viewBox="0 0 512 512"><style>.uploadicon{fill:#4B4B4B;}</style>
        <g>
        <path class="uploadicon" d="M427.258,244.249c0.204-2.604,0.338-5.228,0.338-7.885c0-55.233-44.775-100.008-100.008-100.008
            c-17.021,0-33.042,4.264-47.072,11.764c-15.136-42.633-55.81-73.172-103.633-73.172c-60.729,0-109.96,49.231-109.96,109.96
            c0,11.416,1.741,22.425,4.97,32.778C29.804,234.254,0,275.238,0,323.21c0,62.627,50.769,113.396,113.396,113.396h292.642
            c3.021,0.284,6.079,0.445,9.175,0.445c53.454,0,96.788-43.333,96.788-96.788C512,290.891,475.024,250.183,427.258,244.249z
            M311.709,296.227h-20.452c-6.044,0-10.989,4.945-10.989,10.99v58.074c0,6.044-4.946,10.99-10.989,10.99h-26.558
            c-6.044,0-10.989-4.946-10.989-10.99v-58.074c0-6.044-4.945-10.99-10.989-10.99h-20.452c-6.044,0-8-3.94-4.347-8.755l53.414-70.405
            c3.652-4.816,9.631-4.816,13.284,0l53.414,70.405C319.709,292.288,317.753,296.227,311.709,296.227z"></path>
        </g>
    </symbol>

    <symbol id="download" viewBox="0 0 512 512"><style>.downloadicon{fill:#4B4B4B;}</style>
        <g>
            <path class="downloadicon" d="M242.956,313.537c3.442,4.534,8.073,7.034,13.044,7.034c4.971,0,9.602-2.5,13.024-7.011l94.723-119.88
                c3.517-4.639,4.493-9.126,2.75-12.636c-1.743-3.51-5.906-5.443-11.726-5.443h-36.26c-9.866,0-17.894-8.024-17.894-17.892V43.661
                c0-11.623-9.452-21.079-21.073-21.079h-47.087c-11.621,0-21.073,9.456-21.073,21.079V157.71c0,9.868-8.028,17.892-17.896,17.892
                h-36.26c-5.817,0-9.98,1.933-11.724,5.443c-1.743,3.509-0.767,7.997,2.77,12.659L242.956,313.537z" style="fill: rgb(75, 75, 75);"></path>
            <path class="downloadicon" d="M511.934,360.164l-48.042-160.14h-58.09l-28.242,50.885h36.246L444.7,359.03H67.3l30.893-108.121h36.246
                l-28.242-50.885h-58.09L0,360.622v103.354c0,14.03,11.413,25.442,25.441,25.442h461.118c14.028,0,25.441-11.413,25.441-25.442
                v-55.652L511.934,360.164z"></path>
        </g>
    </symbol>

    <symbol id="edit" viewBox="0 0 256 256"><style>.editicon{fill:#4B4B4B;}</style>
        <g>
            <path class="editicon" d="M118,176.1L75,192.3l16.7-42.7L118,176.1z M246,50l-11.3-11.4L111.4,160.3l11.3,11.4l66.2-65.4v118.3H30
                V31.3h158.9V53h0.3l-93,91.9l11.3,11.4L230.8,34.5l-11.3-11.4l-10.6,10.5V21.3c0-5.5-4.5-10-10-10H20c-5.5,0-10,4.5-10,10v213.3c0,
                5.5,4.5,10,10,10h178.9c5.5,0,10-4.5,10-10V89.1h-2.5L246,50z M57.8,66.9h96.7c3.7,0,6.7-3,6.7-6.7c0-3.7-3-6.7-6.7-6.7H57.8c-3.7,
                0-6.7,3-6.7,6.7C51.1,63.9,54.1,66.9,57.8,66.9z M57.8,104.7h56.7c3.7,0,6.7-3,6.7-6.7c0-3.7-3-6.7-6.7-6.7H57.8c-3.7,0-6.7,3-6.7,
                6.7C51.1,101.7,54.1,104.7,57.8,104.7z"></path>
        </g>
    </symbol>

    </svg>

    <div class="container mt-4 py-2">
        <div class="mx-2 mx-md-0">
            <h4 class="my-3 px-0 mx-2">分類マスタ(目/科/種)</h4>
            <p>文字コードはUTF-8、改行コードはLFとして下さい。</p>

            <h5 class="my-3 px-0">目(Order)マスタ</h5>

            <div class="row border-bottom pb-3 me-3" id="order">
                
                <a class="col-12 col-sm col-md-4 py-3 text-dark text-decoration-none hover-color" href="./order/download">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#download"></use></svg>
                    <span style="vertical-align: super;">現在の設定内容</span>
                </a>
            
                <div class="col-12 col-sm col-md-4 hover-color">
                <iframe id="iframe" name="iframe" class="d-none"></iframe>
                <form action="./master/order/import" id="order_file_upload_form" class="d-block" method="post" target="iframe" enctype="multipart/form-data" style="cursor: pointer;">
                    @csrf
                    <label class="d-block py-3" style="cursor: pointer;">
                        <a target="_blank" rel="noopener">
                            <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#upload"></use></svg>
                            <span style="vertical-align: super;">設定内容取込み</span>
                        </a>
                        <input type="file" id="order_file" name="order_file" form="order_file_upload_form" style="display:none">
                    </label>
                </form>
                </div>

                <span class="col-12 col-sm-3 col-md-2 mt-3"></span>

            </div>

            
            <h5 class="my-3 px-0">科(Family)マスタ</h5>

            <div class="row border-bottom pb-3 me-3" id="family">
                
                <a class="col-12 col-sm col-md-4 py-3 text-dark text-decoration-none hover-color" href="./family/download">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#download"></use></svg>
                    <span style="vertical-align: super;">現在の設定内容</span>
                </a>
            
                <div class="col-12 col-sm col-md-4 hover-color">
                <iframe id="iframe" name="iframe" class="d-none"></iframe>
                <form action="./master/family/import" id="family_file_upload_form" class="d-block" method="post" target="iframe" enctype="multipart/form-data" style="cursor: pointer;">
                    @csrf
                    <label class="d-block py-3" style="cursor: pointer;">
                        <a target="_blank" rel="noopener">
                            <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#upload"></use></svg>
                            <span style="vertical-align: super;">設定内容取込み</span>
                        </a>
                        <input type="file" id="family_file" name="family_file" form="family_file_upload_form" style="display:none">
                    </label>
                </form>
                </div>

                <a class="col-12 col-sm-3 col-md-2 py-3 text-dark text-decoration-none hover-color" href="./family/edit">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#edit"></use></svg>
                    <span style="vertical-align: super;">編集</span>
                </a>

            </div>

            <h5 class="my-3 px-0">種(Species)マスタ</h5>

            <div class="row me-3" id="species">
                
                <a class="col-12 col-sm col-md-4 py-3 text-dark text-decoration-none hover-color" href="./species/download">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#download"></use></svg>
                    <span style="vertical-align: super;">現在の設定内容</span>
                </a>
            
                <div class="col-12 col-sm col-md-4 hover-color">
                <iframe id="iframe" name="iframe" class="d-none"></iframe>
                <form action="./master/species/import" id="species_file_upload_form" class="d-block" method="post" target="iframe" enctype="multipart/form-data" style="cursor: pointer;">
                    @csrf
                    <label class="d-block py-3" style="cursor: pointer;">
                        <a target="_blank" rel="noopener">
                            <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#upload"></use></svg>
                            <span style="vertical-align: super;">設定内容取込み</span>
                        </a>
                        <input type="file" id="species_file" name="species_file" form="species_file_upload_form" style="display:none">
                    </label>
                </form>
                </div>

                <a class="col-12 col-sm-3 col-md-2 py-3 text-dark text-decoration-none  hover-color" href="./species/edit">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#edit"></use></svg>
                    <span style="vertical-align: super;">編集</span>
                </a>

            </div>

        </div>
    </div>
    
</x-kaikon::app-layout>