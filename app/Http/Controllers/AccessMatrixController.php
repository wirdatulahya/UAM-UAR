<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccessMatrixController extends Controller
{
    /**
     * Show Access Matrix page.
     */
    public function index()
    {
        return view('access-matrix.index');
    }
}