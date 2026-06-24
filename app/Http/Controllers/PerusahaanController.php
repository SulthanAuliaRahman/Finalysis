<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PerusahaanController extends Controller
{
    public function index()
    {
        return Inertia::render('Perusahaan/Index');
    }
}
