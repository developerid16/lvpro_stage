<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppContent;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;

class AppContentController extends Controller
{
    public function index()
    {
        $terms = AppContent::where('type', 'terms')->first();
        $faq   = AppContent::where('type', 'faq')->first();
        $onboarding = AppContent::where('type', 'onboarding')->first();

        return view('admin.app-content.index', compact('terms', 'faq', 'onboarding'));
    }

public function store(Request $request)
{
    $request->validate([
        'terms_pdf' => 'nullable|file|mimes:pdf|max:5120',
        'faq_pdf'   => 'nullable|file|mimes:pdf|max:5120',
        'onboarding_pdf' => 'nullable|file|mimes:pdf|max:5120',
    ]);

    $parser = new Parser();

    // Make sure folder exists
    if (!file_exists(public_path('uploads/pdf'))) {
        mkdir(public_path('uploads/pdf'), 0755, true);
    }

    // -------------------------
    // TERMS
    // -------------------------
    if ($request->hasFile('terms_pdf')) {

        $terms = AppContent::firstOrCreate(['type' => 'terms']);

        // Delete old file
        if ($terms->file_path && file_exists(public_path($terms->file_path))) {
            unlink(public_path($terms->file_path));
        }

        $file = $request->file('terms_pdf');
        $filename = time().'_terms.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/pdf'), $filename);

        $relativePath = 'uploads/pdf/'.$filename;
        $fullPath = public_path($relativePath);

        // Extract text
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        $terms->update([
            'file_path' => $relativePath,
            'content'   => $text
        ]);
    }

    // -------------------------
    // FAQ
    // -------------------------
    if ($request->hasFile('faq_pdf')) {

        $faq = AppContent::firstOrCreate(['type' => 'faq']);

        if ($faq->file_path && file_exists(public_path($faq->file_path))) {
            unlink(public_path($faq->file_path));
        }

        $file = $request->file('faq_pdf');
        $filename = time().'_faq.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/pdf'), $filename);

        $relativePath = 'uploads/pdf/'.$filename;
        $fullPath = public_path($relativePath);

        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        $faq->update([
            'file_path' => $relativePath,
            'content'   => $text
        ]);
    }


      // -------------------------
    // TERMS
    // -------------------------
    if ($request->hasFile('onboarding_pdf')) {

        $terms = AppContent::firstOrCreate(['type' => 'onboarding']);

        // Delete old file
        if ($terms->file_path && file_exists(public_path($terms->file_path))) {
            unlink(public_path($terms->file_path));
        }

        $file = $request->file('onboarding_pdf');
        $filename = time().'_onboarding.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/pdf'), $filename);

        $relativePath = 'uploads/pdf/'.$filename;
        $fullPath = public_path($relativePath);

        // Extract text
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        $terms->update([
            'file_path' => $relativePath,
            'content'   => $text
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'PDF uploaded and content extracted successfully'
    ]);
}

}
