<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class FooterController extends Controller
{
    public function about()
    {
        return Inertia::render('Footer/About');
    }

    public function features()
    {
        return Inertia::render('Footer/Features');
    }

    public function support()
    {
        return Inertia::render('Footer/Support');
    }

    public function terms()
    {
        return Inertia::render('Footer/Terms');
    }

    public function blogs()
    {
        return Inertia::render('Footer/Blog');
    }

    public function contact()
    {
        return Inertia::render('Footer/Contact');
    }
}
