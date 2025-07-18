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
        // Helper::pr($request->all());
        $table = $request->input('table');
        $routes = $request->input('routes');
        $orderBy = $request->input('orderBy', 'id');
        $orderType = $request->input('orderType', 'desc');
        $rawColumns = explode(',', $request->input('columns'));
        $search = $request->input('search');
        $page = $request->input('page', 1);
        // $limit = 50;
        $limit = $request->input('perPage', 50); // Default to 50

        if (!in_array('id', $rawColumns)) {
            $rawColumns[] = 'id';
        }

        // Start query
        $query = DB::table($table);

        // JOINs (PostgreSQL-safe with CAST)
        if ($table === 'employers') {
            $query->leftJoin('industries', DB::raw("CAST($table.industry_id AS TEXT)"), '=', DB::raw("CAST(industries.id AS TEXT)"));
            $query->leftJoin('users', DB::raw("CAST($table.created_by AS TEXT)"), '=', DB::raw("CAST(users.id AS TEXT)"));
        }
        if ($table === 'faq_sub_categories') {
            $query->leftJoin('faq_categories', DB::raw("CAST($table.faq_category_id AS TEXT)"), '=', DB::raw("CAST(faq_categories.id AS TEXT)"));
        }
        if ($table === 'faqs') {
            $query->leftJoin('faq_categories', DB::raw("CAST($table.faq_category_id AS TEXT)"), '=', DB::raw("CAST(faq_categories.id AS TEXT)"));
            $query->leftJoin('faq_sub_categories', DB::raw("CAST($table.faq_sub_category_id AS TEXT)"), '=', DB::raw("CAST(faq_sub_categories.id AS TEXT)"));
        }
        if ($table === 'cities') {
            $query->leftJoin('countries', DB::raw("CAST($table.country_id AS TEXT)"), '=', DB::raw("CAST(countries.id AS TEXT)"));
        }
        if ($table === 'currencies') {
            $query->leftJoin('countries', DB::raw("CAST($table.country_id AS TEXT)"), '=', DB::raw("CAST(countries.id AS TEXT)"));
        }
        if ($table === 'courses') {
            $query->leftJoin('qualifications', DB::raw("CAST($table.qualification_id AS TEXT)"), '=', DB::raw("CAST(qualifications.id AS TEXT)"));
        }
        if ($table === 'specializations') {
            $query->leftJoin('courses', DB::raw("CAST($table.course_id AS TEXT)"), '=', DB::raw("CAST(courses.id AS TEXT)"));
            $query->leftJoin('qualifications', DB::raw("CAST($table.qualification_id AS TEXT)"), '=', DB::raw("CAST(qualifications.id AS TEXT)"));
        }
        if ($table === 'users') {
            $query->leftJoin('roles', DB::raw("CAST($table.role_id AS TEXT)"), '=', DB::raw("CAST(roles.id AS TEXT)"));
            if($routes == 'admin-user'){
                // 🚫 Exclude users with role_id 2 and 3
                $query->whereNotIn("$table.role_id", [2, 3]);
            }
        }
        if ($table === 'post_jobs') {
            $query->leftJoin('users', DB::raw("CAST($table.created_by AS TEXT)"), '=', DB::raw("CAST(users.id AS TEXT)"));
        }
        if ($table === 'contact_us') {
            $query->leftJoin('cities', DB::raw("CAST($table.city_id AS TEXT)"), '=', DB::raw("CAST(cities.id AS TEXT)"));
        }
        if ($table === 'report_bugs') {
            $query->leftJoin('users', DB::raw("CAST($table.user_id AS TEXT)"), '=', DB::raw("CAST(users.id AS TEXT)"));
        }

        // Aliased select columns
        $columns = array_map(function ($col) use ($table) {
            if ($table === 'employers') {
                if($col === 'industry_id'){
                    return 'industries.name as industry_name';
                }
                if($col === 'created_by'){
                    return 'users.first_name as created_by_user';
                }
            }
            if ($table === 'faq_sub_categories' && $col === 'faq_category_id') {
                return 'faq_categories.name as faq_category_name';
            }
            if ($table === 'faqs') {
                if ($col === 'faq_category_id') {
                    return 'faq_categories.name as faq_category_name';
                }
                if ($col === 'faq_sub_category_id') {
                    return 'faq_sub_categories.name as faq_sub_category_name';
                }
            }
            if ($table === 'cities' && $col === 'country_id') {
                return 'countries.name as country_name';
            }
            if ($table === 'currencies' && $col === 'country_id') {
                return 'countries.name as country_name';
            }
            if ($table === 'courses' && $col === 'qualification_id') {
                return 'qualifications.name as qualification_name';
            }
            if ($table === 'specializations' && $col === 'course_id') {
                return 'courses.name as course_name';
            }
            if ($table === 'specializations' && $col === 'qualification_id') {
                return 'qualifications.name as qualification_name';
            }
            if ($table === 'users' && $col === 'role_id') {
                return 'roles.role_name as role_name';
            }
            if ($table === 'contact_us' && $col === 'city_id') {
                return 'cities.name as city_name';
            }
            if ($table === 'report_bugs' && $col === 'user_id') {
                return 'users.first_name as name';
            }
            if ($table === 'post_jobs' && $col === 'created_by') {
                return 'users.first_name as created_by_name';
            }
            return str_contains($col, '.') ? $col : "$table.$col";
        }, $rawColumns);

        $query->select($columns);

        // Apply conditions
        $conditions = json_decode(urldecode($request->input('conditions', '[]')), true);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                    $column = str_contains($condition['column'], '.') ? $condition['column'] : "$table.{$condition['column']}";
                    $query->where($column, $condition['operator'], $condition['value']);
                }
            }
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $col) {
                    $baseCol = explode(' as ', $col)[0];
                    $q->orWhere($baseCol, 'ILIKE', "%{$search}%");
                }
            });
        }

        // Count before pagination
        $total = (clone $query)->count();

        // Paginate
        $data = $query->orderBy("$table.$orderBy", $orderType)
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
        // echo '<pre>';print_r($request->all());die;

        $table = $request->input('table');
        $columns = explode(',', $request->input('columns'));
        $titles = explode(',', $request->input('headers')); // <-- NEW
        $format = $request->input('format', 'csv');
        $search = $request->input('search');

        $filename = $request->input('filename'); // Optional
        $orderBy = $request->input('orderBy', 'id');
        $orderType = $request->input('orderType', 'desc');
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

        $rawData = $query->orderBy("$table.$orderBy", $orderType)->get()->toArray();

        // Add Sl. No. to data
        $data = [];
        // foreach ($rawData as $index => $row) {
        //     $data[] = array_merge(['Sl. No.' => $index + 1], (array) $row);
        //     // echo '<pre>';print_r($data);
        //     // Loop through each row and update status
        //     // foreach ($data as &$row_2) {
        //     //     if (isset($row_2['status'])) {
        //     //         $row_2['status'] = (($row_2['status'] == 1)?'Active' : 'Deactive');
        //     //     }
        //     // }
        //     // echo '<pre>';print_r($data);die;
        // }
        foreach ($rawData as $index => $row) {
            $row = (array) $row;

            // Fix status first
            if (isset($row['status'])) {
                $row['status'] = $row['status'] == 1 ? 'Active' : 'Deactive';
            }

            // Make Sl. No. the first key
            $newRow = array_merge(['Sl. No.' => $index + 1], $row);

            $data[] = $newRow;
        }
        // echo '<pre>';print_r($data);die;

        // Add Sl. No. to headings
        $columns = array_merge(['Sl. No.'], $columns);
        
        // Fallback to raw column names if no custom titles given
        $headers = count($titles) === count($columns) ? $titles : $columns;

        

        switch ($format) {
            case 'csv':
                return $this->exportCsv($titles, $data, $filename . '.csv');

            case 'excel':
                return Excel::download(new \App\Exports\ArrayExport($columns, $data), 'export.xlsx');

            case 'pdf':
                ini_set('memory_limit', '1024M'); // 👈 Increase memory limit
                $pdf = PDF::loadView('exports.table', ['columns' => $headers, 'data' => $data, 'titles' => $titles]);
                // // Option 1: Stream it in browser
                // return $pdf->stream('filename.pdf');
                // die;
                return $pdf->download($filename . '.pdf');
        }

        return response()->json(['error' => 'Invalid format'], 400);
    }

    protected function exportCsv($columns, $data, $filename)
    {
        $columns[] = 'Status';
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