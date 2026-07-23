<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Models\Application;
use App\Domain\Archiving\Actions\BuildApplicationArchivePackage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminArchiveController extends Controller
{
    public function downloadPackage(
        Request $request,
        Application $application,
        BuildApplicationArchivePackage $action,
    ): StreamedResponse {
        $package = $action->execute($application, $request->user());

        abort_unless(Storage::disk('private')->exists($package->storage_path), 404);

        return Storage::disk('private')->download(
            $package->storage_path,
            "Archive_{$application->reference}_{$package->package_hash}.json",
            ['Content-Type' => 'application/json']
        );
    }
}
