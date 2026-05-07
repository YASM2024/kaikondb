<x-kaikon::app-layout>
  @slot('header')
    標本情報管理 - 新規登録
  @endslot

  <div class="container mt-4 py-2">
    <div class="text-left bg-light p-3 p-sm-5 rounded">
      <h2 class="mb-4">標本情報（新規登録）</h2>

      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="post" action="{{ route('specimen.create') }}">
        @csrf

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">学名</label>
            <input name="species" class="form-control" value="{{ old('species') }}" placeholder="例: Camponotus japonicus">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">和名</label>
            <input name="species_ja" class="form-control" value="{{ old('species_ja') }}" placeholder="例: クロオオアリ">
          </div>

          <div class="col-12">
            <label class="form-label">採集地（locality）</label>
            <input name="locality" class="form-control" value="{{ old('locality') }}" placeholder="例: 山梨県○○市…">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">緯度（decimal_latitude）</label>
            <input name="decimal_latitude" class="form-control" value="{{ old('decimal_latitude') }}" placeholder="例: 35.1234567">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">経度（decimal_longitude）</label>
            <input name="decimal_longitude" class="form-control" value="{{ old('decimal_longitude') }}" placeholder="例: 138.1234567">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">採集日（テキスト）</label>
            <input name="collection_date_text" class="form-control" value="{{ old('collection_date_text') }}" placeholder="例: 2025-06-14 / 2025-06 / 期間 / 不明">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">採集者</label>
            <input name="collected_by" class="form-control" value="{{ old('collected_by') }}">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">同定者</label>
            <input name="identified_by" class="form-control" value="{{ old('identified_by') }}">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">所有者</label>
            <input name="owner" class="form-control" value="{{ old('owner') }}">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">性別</label>
            <input name="sex" class="form-control" value="{{ old('sex') }}" placeholder="例: ♂ / ♀ / 不明">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">タイプ標本区分</label>
            <input name="type_status" class="form-control" value="{{ old('type_status') }}" placeholder="例: Holotype / Paratype">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">ライセンス</label>
            <select name="license_id" class="form-select" required>
              <option value="" @selected(old('license_id') === null || old('license_id') === '')>選択してください</option>
              @foreach($licenses as $license)
                <option value="{{ $license->id }}" @selected((string)old('license_id') === (string)$license->id)>{{ $license->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">画像URL 1</label>
            <input name="image_1" class="form-control" value="{{ old('image_1') }}" placeholder="https://...">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">画像URL 2</label>
            <input name="image_2" class="form-control" value="{{ old('image_2') }}" placeholder="https://...">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">画像URL 3</label>
            <input name="image_3" class="form-control" value="{{ old('image_3') }}" placeholder="https://...">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">保存方法</label>
            <input name="preservation_method" class="form-control" value="{{ old('preservation_method') }}">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">保管機関</label>
            <input name="repository_institution" class="form-control" value="{{ old('repository_institution') }}">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">保管番号</label>
            <input name="repository_catalog_number" class="form-control" value="{{ old('repository_catalog_number') }}">
          </div>

          <div class="col-12">
            <label class="form-label">備考</label>
            <textarea name="remarks" class="form-control" rows="4">{{ old('remarks') }}</textarea>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="is_public" name="is_public" @checked(old('is_public'))>
              <label class="form-check-label" for="is_public">公開する</label>
            </div>
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">登録</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">戻る</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-kaikon::app-layout>

