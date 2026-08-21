<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch language
     */
    public function switch(Request $request, string $locale)
    {
        // Validate locale
        if (!in_array($locale, ['km', 'en'])) {
            return back()->with('error', 'Invalid language');
        }
        
        // Store in session
        session(['app_locale' => $locale]);
        
        // Redirect back
        return back();
    }
}
