<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GuideController extends Controller
{
    public function flow()
    {
        return view('admin.guide.flow');
    }

    public function manual()
    {
        return view('admin.guide.manual');
    }
}
