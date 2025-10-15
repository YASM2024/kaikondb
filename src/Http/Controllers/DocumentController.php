<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Document;

use Illuminate\Http\Request;
// use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{

    protected static $pdf_path = 'documents/';
    
    //
    public function show( Request $request ){
        if(!Auth::check()){
            abort(403, 'Unauthorized action.');
        }

        $user = User::fromAppUser(Auth::user());

        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        if ($user->isAdmin()) {
            $documents = Document::where('article_id', $request->article_id)->get();
        } elseif ($user->isModerator()) {
            $tags = $user->tags->pluck('id');
            $documents = Document::where('article_id', $request->article_id)
                                ->whereIn('tag_id', $tags)->get();
        } else {
            abort(403, 'Unauthorized action.');
        }

        return $documents;
    }

    //
    public function upload( $id, Request $request ){
        $document = $request->file('document_file');
        $save_file_name = now()->format('YmdHisu').'.pdf';
        if(isset($document)){
            $path = $document->storeAs('documents', $save_file_name);
            Document::create([
                'article_id' => $id, 
                'file_name' => $save_file_name, 
                'display_title' => '本文' 
            ]);
            return $id.'<br>'.$path;
        }
        return false;
    }

    public function edit(Request $request)
    {
        if (!$request->isJson()) {
            return ['result' => false, 'message' => 'Invalid request format'];
        }
        $validatedData = $request->validate([
            'document_id' => 'required|string|max:5',
            'document_name' => 'required|string|max:10',
        ]);
        $document_id = $validatedData['document_id'];
        $document_name = $validatedData['document_name'];

        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where( 'id', $document_id )->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        try {
            $document = Document::findOrFail($document_id);
            $document->update([
                'display_title' => $document_name
            ]);
            return ['result' => true, 'message' => 'Document updated successfully'];
        } catch (ModelNotFoundException $e) {
            return ['result' => false, 'message' => 'Document not found'];
        } catch (\Exception $e) {
            return ['result' => false, 'message' => 'An error occurred'];
        }
    }
    
    //
    public function delete( string $file_name, Request $request ){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where('file_name', $file_name)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }
        $target_file = Document::where('file_name', '=', $file_name)->firstOrFail();
        $target_path = public_path('uploads/');
        $file = storage_path('app/private/documents/').$target_file->file_name;
        //ファイル削除成功の判定
        if( !unlink($file) ){
            return 'ファイル削除に失敗しました';
        }

        //データベース処理の判定
        if( !$target_file->delete()){
            return 'ファイル削除には成功しましたが、データベース処理に失敗しました。';
            //ログに書き出す、ジョブに残すなど別の仕組みが必要。
        }

        return 'ファイル削除に成功しました';
            
    }

    //
    public function open( string $file_name ){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where('file_name', $file_name)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        $file_path = self::$pdf_path . $file_name;
        abort_if(!Storage::exists($file_path), 404);
    
        return response()->make(Storage::get($file_path), 200, [
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="' . $file_name . '"'
        ]);
    }
    

}
