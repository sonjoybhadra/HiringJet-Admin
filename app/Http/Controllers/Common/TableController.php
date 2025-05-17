<?php
namespace App\Http\Controllers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Helpers\Helper;
class TableController extends Controller
{
    public function fetch(Request $request)
    {
        $table = $request->input('table');
        $orderBy = $request->input('orderBy', 'id');
        $orderType = $request->input('orderType', 'desc');
        $columns = explode(',', $request->input('columns'));
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $limit = 50;

        // Make sure 'id' is always selected for encoding
        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }

        $query = DB::table($table)->select($columns);

        // Apply conditions
        $conditions = json_decode(urldecode($request->input('conditions', '[]')), true);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['column'], $condition['operator'], $condition['value']);
                }
            }
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $search . '%');
                }
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Get paginated data
        $data = $query->orderBy($orderBy, $orderType)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->encoded_id = urlencode(base64_encode($item->id));
                return $item;
            });

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $limit),
        ]);
    }

    public function export(Request $request)
    {
        $table = $request->input('table');
        $columns = explode(',', $request->input('columns'));
        $titles = explode(',', $request->input('headers', '')); // <-- NEW
        $format = $request->input('format', 'csv');
        $search = $request->input('search');

        $filename = $request->input('filename'); // Optional
        $defaultName = $table . '_export_' . now()->format('Y-m-d_H-i-s');
        $filename = $filename ?: $defaultName;

        $columns = array_filter($columns, fn($col) => strtolower($col) !== 'actions');

        $query = DB::table($table)->select($columns);

        $conditions = json_decode(urldecode($request->input('conditions')), true);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['column'], $condition['operator'], $condition['value']);
                }
            }
        }

        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $search . '%');
                }
            });
        }

        $rawData = $query->get()->toArray();

        // Add Sl. No. to data
        $data = [];
        foreach ($rawData as $index => $row) {
            $data[] = array_merge(['Sl. No.' => $index + 1], (array) $row);
        }

        // Add Sl. No. to headings
        $columns = array_merge(['Sl. No.'], $columns);

        // Fallback to raw column names if no custom titles given
        $headers = count($titles) === count($columns) ? $titles : $columns;

        switch ($format) {
            case 'csv':
                return $this->exportCsv($headers, $data, $filename . '.csv');

            case 'excel':
                return Excel::download(new \App\Exports\ArrayExport($columns, $data), 'export.xlsx');

            case 'pdf':
                $pdf = PDF::loadView('exports.table', ['columns' => $headers, 'data' => $data]);
                return $pdf->download($filename . '.pdf');
        }

        return response()->json(['error' => 'Invalid format'], 400);
    }

    protected function exportCsv($columns, $data, $filename)
    {
        // $filename = 'export.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        // Write headers
        fputcsv($handle, $columns);

        // Write each row
        foreach ($data as $row) {
            fputcsv($handle, array_values($row));
        }

        fclose($handle);
        exit;
    }
}