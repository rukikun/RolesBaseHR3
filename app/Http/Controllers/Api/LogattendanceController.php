<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class LogattendanceController extends Controller
{
     function x(){
        return Attendance::all();
    }
}
