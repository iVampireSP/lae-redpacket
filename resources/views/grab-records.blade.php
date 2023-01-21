@section('title', '红包领取记录')

<x-app-layout>
    <h3>红包领取记录</h3>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>红包 ID</th>
            <th>发起人</th>
            <th>领取人</th>
            <th>金额</th>
            <th>领取时间</th>
        </tr>
        </thead>


        <tbody>
        @foreach ($grabRecords as $record)
            <tr>
                <td>{{ $record->redPacket->id }}</td>
                <td>{{ $record->redPacket->user->name }}</td>
                <td>{{ $record->user->name }}</td>
                <td>{{ $record->amount }} 元</td>

                <td>{{ $record->created_at }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>


    {{ $grabRecords->links() }}
</x-app-layout>
