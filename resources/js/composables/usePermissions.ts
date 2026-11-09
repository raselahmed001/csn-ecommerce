import { usePage } from '@inertiajs/vue3';
import type { Auth } from '@/types';

/**
 * Lets the UI hide actions the current user has no permission for.
 * This is only a UX convenience: the backend (policies / #[Authorize]) always enforces it.
 */
export function usePermissions() {
    const page = usePage<{ auth: Auth }>();

    const can = (permission: string): boolean =>
        page.props.auth.permissions.includes(permission);

    return { can };
}
