<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\Branch\Presentation\Http\Requests\SelectBranchRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Persists the customer's active branch in the session. The selection is read
 * back into Inertia shared props by {@see HandleInertiaRequests},
 * so the active branch survives across requests (Fase 2 AC #2).
 */
final class BranchSelectionController extends Controller
{
    public const SESSION_KEY = 'branch_id';

    public function store(SelectBranchRequest $request): RedirectResponse
    {
        $request->session()->put(self::SESSION_KEY, $request->integer('branch_id'));

        return back();
    }
}
