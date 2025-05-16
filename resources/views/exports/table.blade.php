<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                @foreach ($columns as $col)
                    <td>{{ $row[$col] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>