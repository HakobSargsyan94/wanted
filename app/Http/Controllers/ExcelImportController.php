<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RowsImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ExcelImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.basic'); // Basic Auth
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        $file = $request->file('file');
        $job = new RowsImport($file);
        dispatch($job);

        return response()->json(['message' => 'Import started.']);
    }
}
