{{-- Skeleton Loading Table --}}
<div class="tbl-wrap">
    <table class="data-table">
        <thead>
            <tr>
                @for($i = 0; $i < ($columns ?? 5); $i++)
                <th>
                    <div class="skeleton" style="width: {{ rand(60, 90) }}%; height: 1rem; background: rgba(203, 213, 225, 0.3); border-radius: 4px;"></div>
                </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @for($row = 0; $row < ($rows ?? 5); $row++)
            <tr>
                @for($col = 0; $col < ($columns ?? 5); $col++)
                <td>
                    <div class="skeleton" style="width: {{ rand(40, 85) }}%; height: 0.9rem; background: rgba(203, 213, 225, 0.2); border-radius: 4px;"></div>
                </td>
                @endfor
            </tr>
            @endfor
        </tbody>
    </table>
</div>