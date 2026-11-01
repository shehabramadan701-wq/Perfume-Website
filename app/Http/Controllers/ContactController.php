<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('shop.contact');
    }

    public function store(Request $request)
    {
        ContactMessage::create($request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:3000',
        ]));

        return back()->with('success', 'Thank you! Your message has been sent — we will reply soon.');
    }
}
