<x-kaikon::app-layout>
    @slot('header')
    バックアップ
    @endslot
    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">バックアップ</h2>
            <div class="row">
                <span class="h5">バックアップログ（最新5日間）</span>
                <table class="table table-striped table-hover text-center">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>ステータス</th>
                            <th>メッセージ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                        <tr>
                                @if($log['status'] == 'Success')
                                <td><a href="{{url('/')}}/admin/backup/{{ $log['file'] }}">{{ $log['date'] }}<br>{{ $log['time'] }}</a></td>
                                @else
                                <td class="text-danger">{{ $log['date'] }}<br>{{ $log['time'] }}</td>
                                @endif
                                <td @class(['text-danger' => $log['status'] == 'Failed'])>{{ $log['status'] }}</td>
                                <td @class(['text-danger' => $log['status'] == 'Failed'])>{!! $log['message'] !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
           </div>
        </div>
    </div>
    @slot('scripts')
    @endslot
</x-kaikon::app-layout>