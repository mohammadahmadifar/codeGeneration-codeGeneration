<?php

namespace YourVendorName\YourPackageName\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return view('CodeGenerator::form');
    }
}
