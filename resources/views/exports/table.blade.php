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
$titles[] = 'Status';
// echo '<pre>';print_r($titles);
// echo '<pre>';print_r($data);
// echo '<pre>';print_r($columns);die;

// Generic: remove prefix only if string contains a dot
$cols = array_map(function ($item) {
    if ($item === "Sl. No.") {
        return $item; // keep as is
    }
    return preg_replace('/^[^.]+\./', '', $item);
}, $columns);
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
                @foreach ($cols as $col)
                    <!-- <td><?=(($col != 'status')?$row[$col]:(($row[$col])?'Active':'Deactive'))?></td> -->
                    <td><?=$row[$col]?></td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>