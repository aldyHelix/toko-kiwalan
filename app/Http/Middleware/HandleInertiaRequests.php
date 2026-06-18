<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\Branch\Application\DTO\BranchSummaryData;
use App\Modules\Branch\Application\Queries\ListActiveBranches;
use App\Modules\Branch\Application\Queries\ResolveActiveBranch;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Presentation\Http\Controllers\BranchSelectionController;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => [
                'user' => fn () => $request->user()
                    ? $request->user()->only(['id', 'name', 'email'])
                    : null,
            ],
            // Lazy: only resolved (one query) on requests that actually consume
            // the prop, mirroring `auth.user` above.
            'branch' => fn (): array => $this->sharedBranch($request),
        ];
    }

    /**
     * The active branch and the selectable set for the storefront branch
     * switcher. Resolved from the session-selected branch, defaulting to the
     * first active branch.
     *
     * @return array{active: BranchSummaryData|null, available: list<BranchSummaryData>}
     */
    private function sharedBranch(Request $request): array
    {
        $available = app(ListActiveBranches::class)->handle();
        $sessionBranchId = $request->session()->get(BranchSelectionController::SESSION_KEY);
        $active = app(ResolveActiveBranch::class)->handle(
            $available,
            $sessionBranchId === null ? null : (int) $sessionBranchId,
        );

        return [
            'active' => $active instanceof Branch ? BranchSummaryData::from($active) : null,
            'available' => $available
                ->map(fn (Branch $branch): BranchSummaryData => BranchSummaryData::from($branch))
                ->values()
                ->all(),
        ];
    }
}
