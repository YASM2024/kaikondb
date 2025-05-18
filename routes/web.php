<?php

use Kaikon2\Kaikondb\Http\Controllers\RecordController; 
use Kaikon2\Kaikondb\Http\Controllers\PhotoController; 
use Kaikon2\Kaikondb\Http\Controllers\ExpandedPageController; 
use Kaikon2\Kaikondb\Http\Controllers\ArticleController; 
use Kaikon2\Kaikondb\Http\Controllers\SpecimenController; 
use Kaikon2\Kaikondb\Http\Controllers\SpeciesController; 
use Kaikon2\Kaikondb\Http\Controllers\FamilyController;
use Kaikon2\Kaikondb\Http\Controllers\OrderController;
use Kaikon2\Kaikondb\Http\Controllers\HomeController; 
use Kaikon2\Kaikondb\Http\Controllers\UserController; 
use Kaikon2\Kaikondb\Http\Controllers\MunicipalityController;
use Kaikon2\Kaikondb\Http\Controllers\JournalController;
use Kaikon2\Kaikondb\Http\Controllers\DocumentController;
use Kaikon2\Kaikondb\Http\Controllers\BackupController;
use Kaikon2\Kaikondb\Http\Controllers\AdminController;

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

    // ====================================== トップメニュー ======================================
    Route::get('/', [HomeController::class,'showTopMenu'])->name('home');
    Route::get('/chart', [HomeController::class,'showChart'])->name('chart');


    // ====================================== Static Page ======================================
    // ====================================== メインコンテンツ ======================================
    if(env('LITERATURES')==1){
        // 文献検索
        Route::get('/articles', [ArticleController::class, 'showSearchMenu'])->name('articles');
        Route::get('/articles/search',[ArticleController::class,'search']);
        Route::get('/articles/{id}/show',[ArticleController::class,'show']);
        Route::get('/articles/{id}/species',[ArticleController::class,'showSpecies']);
    }

    if(env('SPECIMENS')==1){
        // 標本検索
        Route::get('/specimens', [SpecimenController::class, 'showSearchMenu'])->name('specimens');
        Route::get('/specimens/search',[SpecimenController::class,'search']);
        Route::get('/specimens/{id}/show',[SpecimenController::class,'show']);
        Route::get('/specimens/{id}/species',[SpecimenController::class,'showSpecies']);
    }

    if(env('INVENTORY')==1){
        // 種検索
        Route::get('/species', [SpeciesController::class, 'showSearchMenu'])->name('species');
        Route::get('/species/search',[SpeciesController::class,'search']);
        Route::get('/species/{id}/show',[SpeciesController::class,'show']);
        Route::get('/summary',[SpeciesController::class,'downloadSummary']);
        Route::get('/records/search',[RecordController::class,'search']);
        Route::get('/records/{id}/show',[RecordController::class,'show']);
    }

    if(env('PHOTOS')==1){
        // フォトギャラリー
        Route::get('/photos', [PhotoController::class, 'showSearchMenu'])->name('photos');
        Route::get('/photos/search',[PhotoController::class,'search']);
        Route::get('/photos/{id}/show',[PhotoController::class,'show']);

        Route::get('/users/{id}',[UserController::class,'showOpenProfile'])->name('showOpenProfile');
    }


    // ====================================== Expanded Page ======================================
    // ====================================== サイト情報ほか ======================================

    // 汎用ページ ( ご協力のお願い / プロジェクト説明 / 管理人 / 県地図 )
    Route::get('/expanded/{route_name}', [ExpandedPageController::class,'show'])->name('expanded_page');




    ////////////////////////////////////////// Authenticated User //////////////////////////////////////////

    require __DIR__.'/auth.php';

    Route::middleware('auth')->group(function () {

        // プロフィール
        Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.edit');
        Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        // Route::post('/profile/delete', [UserController::class, 'destroy'])->name('profile.destroy');

    });

    ////////////////////////////////////////// Authenticated & EmailVerified User //////////////////////////////////////////
    Route::middleware(['auth', 'verified'])->group(function () {

        // ====================================== メニュー一覧 ======================================
        Route::get('/dashboard', function () { return view('kaikon::dashboard'); })->name('dashboard');


        ////////////////////////////////////////// User //////////////////////////////////////////

        Route::middleware('isUser')->group(function () {

            if(env('PHOTOS')==1){
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
        
            if(env('LITERATURES')==1){
                // ------------------- 文献編集 -------------------
                Route::get('/articles/import',[ArticleController::class,'showImport'])->name('article.import');
                Route::post('/articles/import',[ArticleController::class,'import']);
                Route::get('/articles/download',[ArticleController::class,'download']);
                Route::get('/articles/create',[ArticleController::class,'showCreate'])->name('article.create');
                Route::post('/articles/create',[ArticleController::class,'create']);
                Route::get('/articles/{id}/edit',[ArticleController::class,'showEdit']);
                Route::post('/articles/{id}/edit',[ArticleController::class,'edit']);
                Route::get('/articles/{id}/delete',[ArticleController::class,'showDelete']);
                Route::post('/articles/{id}/delete',[ArticleController::class,'delete']);
        
                Route::get('/articles/{id}/documents/',[DocumentController::class,'show']);
                Route::post('/articles/{id}/documents/',[DocumentController::class,'edit'])->name('document.edit');
                Route::post('/articles/{id}/documents/upload',[DocumentController::class,'upload']);
                Route::get('/articles/documents/{document_id}',[DocumentController::class,'showItem'])->name('document.showItem');
                Route::get('/articles/documents/{file_name}/delete',[DocumentController::class,'delete'])->name('document.delete');
            }
        
            if(env('INVENTORY')==1){
                // ------------------- 記録編集 -------------------
                Route::get('/records/{id}/edit',[RecordController::class,'showEdit']);
                Route::post('/records/{id}/edit',[RecordController::class,'edit']);
                Route::get('/records/import',[RecordController::class,'showImport'])->name('record.import');
                Route::post('/records/import',[RecordController::class,'import']);
                Route::get('/records/create',[RecordController::class,'showCreate'])->name('record.create');
                Route::post('/records/create',[RecordController::class,'create']);
                Route::post('/records/complete',[RecordController::class,'complete']);
            }
        
        });


        ////////////////////////////////////////// Administrator //////////////////////////////////////////
        Route::middleware('isAdministrator')->group(function () {
            
            // ------------------- マスタ管理 -------------------
        
            // 分類マスタ(目/科/種)
            Route::get('/master/taxon', function(){ return view('kaikon::masters.taxon'); });
        
            Route::get('/master/order/show', [OrderController::class, 'showMaster'])->name('orderMaster');
            Route::get('/master/order/show_enabled', [OrderController::class, 'showMasterHaveSpecies']);
            Route::get('/master/order/download',[OrderController::class,'downloadMaster']);
            Route::post('/master/order/import',[OrderController::class,'importMaster']);
        
            Route::get('/master/family/show',[FamilyController::class,'showMaster'])->name('familyMaster');
            Route::get('/master/family/download',[FamilyController::class,'downloadMaster']);
            Route::post('/master/family/import',[FamilyController::class,'importMaster']);
            Route::get('/master/family/edit',[FamilyController::class,'showEditMaster']);
            Route::post('/master/family/edit',[FamilyController::class,'editMaster']);
        
            Route::get('/master/species/show',[SpeciesController::class,'showMaster'])->name('speciesMaster');
            Route::get('/master/species/download',[SpeciesController::class,'downloadMaster']);
            Route::post('/master/species/import',[SpeciesController::class,'importMaster']);
            Route::get('/master/species/edit',[SpeciesController::class,'showEditMaster']);
            Route::post('/master/species/edit',[SpeciesController::class,'editMaster']);
            
            // 市町村マスタ
            Route::get('/master/municipality/show',[MunicipalityController::class,'showMaster'])->name('municiparityMaster');
            Route::get('/master/municipality/show/{id}',[MunicipalityController::class,'show'])->name('municiparity.show');
            Route::post('/master/municipality/create',[MunicipalityController::class,'create'])->name('municiparity.create');
            Route::post('/master/municipality/edit/{id}',[MunicipalityController::class,'edit'])->name('municiparity.edit');
            Route::get('/master/municipality/delete-screening/{id}',[MunicipalityController::class,'screeningDelete']);
            Route::post('/master/municipality/delete/{id}',[MunicipalityController::class,'delete'])->name('municiparity.delete');
            Route::get('/master/municipality/download',[MunicipalityController::class,'downloadMaster']);
            Route::post('/master/municipality/import',[MunicipalityController::class,'importMaster']);
        
            // 雑誌マスタ
            Route::get('/master/journal/show',[JournalController::class,'showMaster'])->name('journalMaster');
            Route::get('/master/journal/show/{id}',[JournalController::class,'show'])->name('journal.show');
            Route::post('/master/journal/create',[JournalController::class,'create'])->name('journal.create');
            Route::post('/master/journal/edit/{id}',[JournalController::class,'edit'])->name('journal.edit');
            Route::get('/master/journal/delete-screening/{id}',[JournalController::class,'screeningDelete']);
            Route::post('/master/journal/delete/{id}',[JournalController::class,'delete'])->name('journal.delete');
            Route::get('/master/journal/download',[JournalController::class,'downloadMaster']);
            Route::post('/master/journal/import',[JournalController::class,'importMaster']);
        
        
            // ------------------- 運営情報管理 -------------------
        
            // 運営情報管理 
            Route::get('/exp', [ExpandedPageController::class,'index'])->name('expanded_page.index');
            Route::get('/exp/create', [ExpandedPageController::class,'showForm'])->name('expanded_page.showCreate');
            Route::post('/exp/create', [ExpandedPageController::class,'update'])->name('expanded_page.create');
            Route::get('/exp/{route_name}/edit', [ExpandedPageController::class,'showForm'])->name('expanded_page.showEdit');
            Route::post('/exp/{route_name}/edit', [ExpandedPageController::class,'update'])->name('expanded_page.update');
            Route::post('/exp/delete', [ExpandedPageController::class,'delete'])->name('expanded_page.delete');
        
        
            // ------------------- システム管理 -------------------
        
            // ユーザ管理
            Route::get('/admin/users',[UserController::class,'showUsers'])->name('admin.showUsers');
            Route::get('/admin/users/{id}',[UserController::class,'show']);
            Route::post('/admin/users/{id}',[UserController::class,'update']);
            Route::delete('/admin/users/{id}',[UserController::class,'destroy']);
        
            // バックアップ
            Route::get('/admin/backup',[BackupController::class,'showBackupStatus'])->name('admin.showBackup');
            Route::post('/admin/backup',[BackupController::class,'backup'])->name('admin.backup');
            Route::get('/admin/backup/{file}',[BackupController::class,'download'])->name('admin.downloadBackup');
        
            // 開発・ヘルプ
            Route::get('/admin/developers', function () { return view('kaikon::developers'); })->name('admin.developers');
            Route::get('/admin/phpinfo',function(){return phpinfo();});
        
        });

    });

});
