<table class="sjs-render-matrix">
    <thead>
        <tr>
            <th></th>
            @foreach($question['columns'] as $col)
                <th>{{ $col['text'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($question['rows'] as $row)
            <tr>
                <td class="sjs-render-matrix-row-label">{{ $row['text'] }}</td>
                @foreach($question['columns'] as $col)
                    @php $cellValue = $question['cells'][$row['value']][$col['value']] ?? null; @endphp
                    <td class="sjs-render-matrix-cell {{ $cellValue === true ? 'sjs-render-matrix--selected' : '' }}">
                        @if($question['matrixType'] === 'matrix')
                            {{ $cellValue === true ? '●' : '' }}
                        @else
                            {{ $cellValue ?? '' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
