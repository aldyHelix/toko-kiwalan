/**
 * Shared Inertia prop types (see app/Http/Middleware/HandleInertiaRequests.php).
 */

export interface BranchSummary {
    id: number;
    code: string;
    name: string;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
}

export interface SharedProps {
    appName: string;
    auth: {
        user: AuthUser | null;
    };
    branch: {
        active: BranchSummary | null;
        available: BranchSummary[];
    };
    [key: string]: unknown;
}
