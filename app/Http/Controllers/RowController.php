<?php

namespace App\Http\Controllers;

use App\Models\Row;
use Illuminate\Http\Request;

class RowController extends Controller
{
    public function index()
    {
        $rows = Row::all()->groupBy('date');
        return response()->json($rows);
    }
}
