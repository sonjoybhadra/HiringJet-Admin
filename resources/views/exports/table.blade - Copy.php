<style>
  table {
    width: 100%;
    table-layout: fixed; /* Makes columns respect width */
    border-collapse: collapse;
  }
  th, td {
    border: 1px solid #000;
    padding: 6px;
    word-wrap: break-word;
    font-size: 10px; /* Smaller font if you have many columns */
  }
</style>
<?php
use Illuminate\Support\Facades\DB;

$columns = array_map(function ($col) {
    // keep "Sl. No." exactly as it is
    if ($col === 'Sl. No.') {
        return $col;
    }

    if ($col instanceof \Illuminate\Database\Query\Expression) {
        // Get the raw expression string using grammar
        $value = $col->getValue(DB::connection()->getQueryGrammar());

        if (stripos($value, ' as ') !== false) {
            // Correctly extract alias after "as"
            $alias = substr($value, stripos($value, ' as ') + 4);
            return trim($alias);
        }
        return $value;
    }

    if (strpos($col, '.') !== false) {
        // remove table prefix like "employers.name"
        $parts = explode('.', $col);
        return end($parts);
    }

    return $col;
}, $columns);

$titles[] = 'Status';
// echo '<pre>';print_r($titles);
// echo '<pre>';print_r($data);
// echo '<pre>';print_r($columns);die;

// Generic: remove prefix only if string contains a dot
// $cols = array_map(function ($item) {
//     if ($item === "Sl. No.") {
//         return $item; // keep as is
//     }
//     return preg_replace('/^[^.]+\./', '', $item);
// }, $columns);
// echo '<pre>';print_r($cols);die;
?>
<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            @foreach ($titles as $title)
                <th>{{ $title }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                @foreach ($columns as $col)
                    <!-- <td><?=(($col != 'status')?$row[$col]:(($row[$col])?'Active':'Deactive'))?></td> -->
                    <td><?=$row[$col]?></td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>