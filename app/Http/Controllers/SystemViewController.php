<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemViewController extends Controller
{
    /**
     * Show portal selection page
     */
    public function portalSelection()
    {
        return view('portal.selection');
    }

    /**
     * Show test modal page
     */
    public function testModal()
    {
        return view('test-modal');
    }

    /**
     * Show database test page
     */
    public function databaseTest()
    {
        return view('database_test');
    }

    /**
     * Show profile edit page
     */
    public function profile()
    {
        return view('profile.edit');
    }
}
