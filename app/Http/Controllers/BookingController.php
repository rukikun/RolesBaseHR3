<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric',
        ]);
        // Here you would save the booking to the database
        // For now, just redirect back with a success message
        return redirect()->back()->with('success', 'Booking created!');
    }
}
