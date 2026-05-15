<?php

use Kaikon2\Kaikondb\Http\Controllers\RecordController; 
use Kaikon2\Kaikondb\Http\Controllers\PhotoController; 
use Kaikon2\Kaikondb\Http\Controllers\ExpandedPageController; 
use Kaikon2\Kaikondb\Http\Controllers\LiteratureController; 
use Kaikon2\Kaikondb\Http\Controllers\SpecimenController; 
use Kaikon2\Kaikondb\Http\Controllers\RecordedSpeciesController; 
use Kaikon2\Kaikondb\Http\Controllers\SpeciesController; 
use Kaikon2\Kaikondb\Http\Controllers\FamilyController;
use Kaikon2\Kaikondb\Http\Controllers\TaxonController;
use Kaikon2\Kaikondb\Http\Controllers\OrderController;
use Kaikon2\Kaikondb\Http\Controllers\HomeController; 
use Kaikon2\Kaikondb\Http\Controllers\UserController; 
use Kaikon2\Kaikondb\Http\Controllers\MunicipalityController;
use Kaikon2\Kaikondb\Http\Controllers\JournalController;
use Kaikon2\Kaikondb\Http\Controllers\DocumentController;
use Kaikon2\Kaikondb\Http\Controllers\AdminController;
use Kaikon2\Kaikondb\Http\Controllers\SystemStatusController;

use Illuminate\Support\Facades\Route;



Route::group(['middleware' => ['web']], function () {

    ////////////////////////////////////////// Anonymous //////////////////////////////////////////

    // ====================================== 言語対応 ======================================
    Route::get('/lang/{lang}', function ($lang) {
        $availableLang = ['en', 'ja'];
        if (!in_array($lang, $availableLang)) { $lang = config('app.locale'); }
        session(['locale' => $lang]);
        return redirect()->back();
    })->name('lang.switch');

    // ====================================== 利用同意 ======================================
    Route::post('/agree', [HomeController::class,'agree'])->name('agree');

    // ====================================== トップメニュー ======================================
    Route::get('/', [HomeController::class,'showTopMenu'])->name('home');
    Route::get('/chart', [HomeController::class,'showChart'])->name('chart');


    // ====================================== Static Page ======================================
    // ====================================== メインコンテンツ ======================================
    if(config('kaikon.LITERATURES')==1){
        // 文献検索
        Route::get('/literatures', [LiteratureController::class, 'showSearchMenu'])->name('literatures');
        Route::middleware('throttle:15,1')->group(function () {
            Route::get('/literatures/search',[LiteratureController::class,'search']);
            Route::get('/literatures/{id}/show',[LiteratureController::class,'show']);
            Route::get('/literatures/{id}/species',[LiteratureController::class,'showSpecies']);
        });
        // 旧 URL 互換（フェーズ2で整理可）
        Route::redirect('/articles', '/literatures', 301)->name('articles');
        Route::redirect('/articles/search', '/literatures/search', 301);
    }

    if(config('kaikon.SPECIMENS')==1){
        // 標本検索
        Route::get('/specimens', [SpecimenController::class, 'showSearchMenu'])->name('specimens');
        Route::middleware('throttle:15,1')->group(function () {
            Route::get('/specimens/search',[SpecimenController::class,'index']);
            Route::get('/specimens/{id}',[SpecimenController::class,'show']);
        });
    }

    if(config('kaikon.INVENTORY')==1){
        // 種検索
        Route::get('/species',[RecordedSpeciesController::class, 'showSearchMenu'])->name('species');
        Route::middleware('throttle:20,1')->group(function () {
            Route::get('/species/search',[RecordedSpeciesController::class,'search']);
            Route::get('/species/{id}/show',[RecordedSpeciesController::class,'show']);
            Route::get('/summary',[RecordedSpeciesController::class,'downloadSummary']);
            Route::get('/records/search',[RecordController::class,'search']);
            Route::get('/records/{id}/show',[RecordController::class,'show']);
            Route::get('/upper-taxa',[TaxonController::class, 'upperTaxa'])->name('upper-taxa');
        });
    }

    if(config('kaikon.PHOTOS')==1){
        // フォトギャラリー
        Route::get('/photos', [PhotoController::class, 'showSearchMenu'])->name('photos');
        Route::get('/photos/search',[PhotoController::class,'search']);
        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/photos/{id}/show',[PhotoController::class,'show'])->name('photo.show');
            Route::get('/users/{id}',[UserController::class,'showOpenProfile'])->name('showOpenProfile');
        });
    }


    // ====================================== Expanded Page ======================================
    // ====================================== サイト情報ほか ======================================

    // 汎用ページ ( ご協力のお願い / プロジェクト説明 / 管理人 / 県地図 )
    Route::get('/expanded/{route_name}', [ExpandedPageController::class,'show'])->name('expanded_page');




    ////////////////////////////////////////// Authenticated User //////////////////////////////////////////

    require __DIR__.'/auth.php';

    Route::middleware('auth')->group(function () {

        // プロフィール
        Route::get('/mypage/profile', [UserController::class, 'showProfile'])->name('profile.edit');
        Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::post('/mypage/profile/delete', [UserController::class, 'destroyProfile'])->name('profile.destroy');

    });

    ////////////////////////////////////////// Authenticated & EmailVerified User //////////////////////////////////////////
    Route::middleware(['auth', 'verified'])->group(function () {

        // ====================================== メニュー一覧 ======================================
        Route::get('/dashboard', function () { return view('kaikon::pages.dashboard'); })->name('dashboard');


        ////////////////////////////////////////// User //////////////////////////////////////////

        Route::middleware('isUser')->group(function () {

            if(config('kaikon.PHOTOS')==1){
                // 写真編集
                Route::get('/photos/create',[PhotoController::class,'showCreate'])->name('photos/create');
                Route::post('/photos/create',[PhotoController::class,'create']);
                Route::post('/photos/edit',[PhotoController::class,'edit']);
                Route::post('/photos/delete',[PhotoController::class,'delete']);
                Route::get('/photos/download',[PhotoController::class,'download']);
            }
        });


        ////////////////////////////////////////// Moderator //////////////////////////////////////////

        Route::middleware('isModerator')->group(function () {
        
            if(config('kaikon.LITERATURES')==1){
                // ------------------- 文献編集 -------------------
                Route::get('/literatures/import',[LiteratureController::class,'showImport'])->name('literature.import');
                Route::post('/literatures/import',[LiteratureController::class,'import']);
                Route::get('/literatures/download',[LiteratureController::class,'download']);
                Route::get('/literatures/create',[LiteratureController::class,'showCreate'])->name('literature.create');
                Route::post('/literatures/create',[LiteratureController::class,'create']);
                Route::get('/literatures/{id}/edit',[LiteratureController::class,'showEdit']);
                Route::post('/literatures/{id}/edit',[LiteratureController::class,'edit']);
                Route::get('/literatures/{id}/delete',[LiteratureController::class,'showDelete']);
                Route::post('/literatures/{id}/delete',[LiteratureController::class,'delete']);
        
                Route::get('/literatures/{id}/documents/',[DocumentController::class,'show']);
                Route::post('/literatures/{id}/documents/',[DocumentController::class,'edit'])->name('document.edit');
                Route::post('/literatures/{id}/documents/upload',[DocumentController::class,'upload'])->name('document.upload');
                Route::get('/literatures/documents/{document_id}',[DocumentController::class,'open'])->name('document.open');
                Route::get('/literatures/documents/{file_name}/delete',[DocumentController::class,'delete'])->name('document.delete');

                Route::redirect('/articles/import', '/literatures/import', 301)->name('article.import');
                Route::redirect('/articles/create', '/literatures/create', 301)->name('article.create');
            }
        
            if(config('kaikon.INVENTORY')==1){
                // ------------------- 記録編集 -------------------
                Route::get('/records/{literature_species}/edit',[RecordController::class,'showEdit']);
                Route::post('/records/{literature_species}/edit',[RecordController::class,'edit']);
                Route::post('/records/{literature_species}/delete',[RecordController::class,'delete']);
                Route::get('/records/import',[RecordController::class,'showImport'])->name('record.import');
                Route::post('/records/import',[RecordController::class,'import']);
                Route::get('/records/download',[RecordController::class,'download']);
                Route::get('/records/create',[RecordController::class,'showCreate'])->name('record.create');
                Route::post('/records/create',[RecordController::class,'create']);
                Route::post('/records/complete',[RecordController::class,'complete']);
            }

            if (config('kaikon.SPECIMENS')==1){
                // ------------------- 標本情報管理 -------------------
                Route::get('/specimens/create', [SpecimenController::class,'showCreate'])->name('specimen.create');
                Route::post('/specimens/create', [SpecimenController::class,'create']);
            }

            /**
             * サブディレクトリ公開時、フルパス（例: /dbdev/specimens/create）でアクセスされるケース向けに
             * kaikon.APP_PATH_PREFIX 付きの経路も受ける（route 名は付けない）。
             */
            if (config('kaikon.SPECIMENS')==1){
                $specimensCompatPrefix = config('kaikon.APP_PATH_PREFIX');
                if (is_string($specimensCompatPrefix) && $specimensCompatPrefix !== '') {
                    Route::prefix(trim($specimensCompatPrefix, '/'))->group(function () {
                        Route::get('/specimens/create', [SpecimenController::class,'showCreate']);
                        Route::post('/specimens/create', [SpecimenController::class,'create']);
                    });
                }
            }
                
            if(config('kaikon.PHOTOS')==1){
                // ------------------- 写真管理（承認・却下） ------------------- 
                Route::get('/admin/photos', [PhotoController::class,'admin'])->name('photos.admin');
                Route::post('/admin/photos/accept', [PhotoController::class,'accept'])->name('photos.accept');
            }

            // ------------------- マスタ利用 -------------------
            Route::get('/master/order/show', [OrderController::class, 'showMaster'])->name('orderMaster');
            Route::get('/master/order/show_enabled', [OrderController::class, 'showMasterHaveSpecies']);
            Route::get('/master/family/show',[FamilyController::class,'showMaster'])->name('familyMaster');
            Route::get('/master/species/show',[SpeciesController::class,'showMaster'])->name('speciesMaster');
            Route::get('/master/municipality/show',[MunicipalityController::class,'showMaster'])->name('municipalityMaster');
            Route::get('/master/journal/show',[JournalController::class,'showMaster'])->name('journalMaster');

        });


        ////////////////////////////////////////// Administrator //////////////////////////////////////////
        Route::middleware('isAdministrator')->group(function () {
            
            // ------------------- マスタ管理 -------------------

            // 分類マスタ参照(目/科/種)
            Route::get('/master/taxon/order', [TaxonController::class, 'showMaster'])->name('taxon.order');
            Route::get('/master/taxon/family',[FamilyController::class,'showEditMaster']);
            Route::get('/master/taxon/species',[SpeciesController::class,'showEditMaster']);
            Route::get('/master/taxon', function () { return redirect()->route('taxon.order'); });

            // 分類マスタアップロード(目/科/種)
            Route::post('/master/order/import',[OrderController::class,'importMaster']);
            Route::post('/master/family/import',[FamilyController::class,'importMaster']);
            Route::post('/master/species/import',[SpeciesController::class,'importMaster']);
            
            // 旧分類マスタ(目/科/種)
            Route::get('/master/taxon_old', function(){ return view('kaikon::masters.taxon_old'); });
        
            Route::get('/master/order/download',[OrderController::class,'downloadMaster']);
            Route::post('/master/order/create', [OrderController::class, 'edit']);
            Route::post('/master/order/edit',[OrderController::class,'edit']);
            Route::post('/master/order/edit-status',[OrderController::class,'editStatus']);
                    
            Route::get('/master/family/download',[FamilyController::class,'downloadMaster']);
            Route::post('/master/family/create', [FamilyController::class, 'edit']);
            Route::post('/master/family/edit',[FamilyController::class,'edit']);
            Route::post('/master/family/edit-status',[FamilyController::class,'editStatus']);
            
            Route::get('/master/species/download',[SpeciesController::class,'downloadMaster']);
            Route::post('/master/species/create', [SpeciesController::class, 'edit']);
            Route::post('/master/species/edit',[SpeciesController::class,'edit']);
            Route::post('/master/species/edit-status',[SpeciesController::class,'editStatus']);
            
            // 市町村マスタ
            // Route::get('/master/municipality/show',[MunicipalityController::class,'showMaster'])->name('municiparityMaster');
            Route::get('/master/municipality/show/{id}',[MunicipalityController::class,'show'])->name('municiparity.show');
            Route::post('/master/municipality/create',[MunicipalityController::class,'create'])->name('municiparity.create');
            Route::post('/master/municipality/edit/{id}',[MunicipalityController::class,'edit'])->name('municiparity.edit');
            Route::post('/master/municipality/edit-status',[MunicipalityController::class,'editStatus'])->name('municiparity.editStatus');
            Route::get('/master/municipality/delete-screening/{id}',[MunicipalityController::class,'screeningDelete']);
            Route::post('/master/municipality/delete/{id}',[MunicipalityController::class,'delete'])->name('municiparity.delete');
            Route::get('/master/municipality/download',[MunicipalityController::class,'downloadMaster']);
            Route::post('/master/municipality/import',[MunicipalityController::class,'importMaster']);
        
            // 雑誌マスタ
            // Route::get('/master/journal/show',[JournalController::class,'showMaster'])->name('journalMaster');
            Route::get('/master/journal/show/{id}',[JournalController::class,'show'])->name('journal.show');
            Route::get('/master/journal/edit/{id}',[JournalController::class,'show'])->name('journal.edit.show');
            Route::post('/master/journal/create',[JournalController::class,'create'])->name('journal.create');
            Route::post('/master/journal/edit/{id}',[JournalController::class,'edit'])->name('journal.edit');
            Route::post('/master/journal/edit-status',[JournalController::class,'editStatus'])->name('journal.editStatus');
            Route::get('/master/journal/delete-screening/{id}',[JournalController::class,'screeningDelete']);
            Route::post('/master/journal/delete/{id}',[JournalController::class,'delete'])->name('journal.delete');
            Route::get('/master/journal/download',[JournalController::class,'downloadMaster']);
            Route::post('/master/journal/import',[JournalController::class,'importMaster']);
            Route::get('/master/journals',[JournalController::class,'all']);

            // 市町村マスタ（一覧API: Journalと同じ使い方）
            Route::get('/master/municipalities',[MunicipalityController::class,'all']);
        
            // ------------------- 運営情報管理 -------------------
                        
            // ユーザ管理
            Route::get('/admin/users',[UserController::class,'showUsers'])->name('admin.showUsers');
            Route::get('/admin/users/{id}',[UserController::class,'show']);
            Route::post('/admin/users/{id}',[UserController::class,'update']);
            // パスを /users/{id} から分離（ホスト側の destroy ルートと衝突しないようにする）
            Route::delete('/admin/users/{id}/purge', [UserController::class, 'adminDestroyUser'])->name('admin.users.destroy');

        });
        
        Route::middleware('isDeveloper')->group(function () {
            // ------------------- 運営情報管理 -------------------
        
            // 運営情報管理 
            Route::get('/admin/exp', [ExpandedPageController::class,'index'])->name('expanded_page.index');
            Route::get('/admin/exp/create', [ExpandedPageController::class,'showForm'])->name('expanded_page.showCreate');
            Route::post('/admin/exp/create', [ExpandedPageController::class,'create'])->name('expanded_page.create');
            Route::get('/admin/exp/{route_name}/edit', [ExpandedPageController::class,'showForm'])->name('expanded_page.showEdit');
            Route::get('/admin/exp/{route_name}/preview', [ExpandedPageController::class,'preview'])->name('expanded_page.preview');
            Route::post('/admin/exp/update', [ExpandedPageController::class,'update'])->name('expanded_page.update');
            Route::post('/admin/exp/delete', [ExpandedPageController::class,'delete'])->name('expanded_page.delete');

            // ------------------- システム管理 -------------------

            Route::get('/admin/phpinfo',function(){return phpinfo();});

            // ジョブ／リスナー 起動状況
            Route::get('/admin/system-status', [SystemStatusController::class, 'show'])->name('admin.system.status');
            Route::post('/admin/system-status/queue-worker/drain-now', [SystemStatusController::class, 'drainQueueNow'])
                ->middleware(['throttle:2,1'])
                ->name('admin.system.status.queue_worker.drain_now');
        
            if(config('kaikon.PHOTOS')==1){
                // ------------------- 写真管理（承認・却下） -------------------
                Route::get('/admin/photos', [PhotoController::class,'admin'])->name('photos.admin');
            }
            
        });

    });

});
