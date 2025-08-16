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
// echo '<pre>';print_r($columns);
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