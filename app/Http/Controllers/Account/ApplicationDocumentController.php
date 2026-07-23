<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Actions\UploadSubmittedDocument;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\SubmittedDocument;
use App\Domain\Catalog\Models\DocumentRequirement;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    public function store(
        Request $request,
        Application $application,
        DocumentRequirement $requirement,
        UploadSubmittedDocument $action,
    ): RedirectResponse {
        Gate::authorize('update', $application);

        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $action->execute($application, $requirement, $request->file('file'));

        return back()->with('status', 'document.uploaded');
    }

    public function download(
        Application $application,
        SubmittedDocument $document,
    ): StreamedResponse {
        Gate::authorize('downloadDocument', [$application, $document]);

        abort_unless(Storage::disk($document->disk)->exists($document->file_path), 404);

        return Storage::disk($document->disk)->download(
            $document->file_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type]
        );
    }
}
