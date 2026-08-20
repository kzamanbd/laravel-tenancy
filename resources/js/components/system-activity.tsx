import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { useUi } from '@/context/ui-context';

export default function SystemActivity() {
    const { activityOpen, setActivityOpen } = useUi();

    return (
        <Sheet open={activityOpen} onOpenChange={setActivityOpen}>
            <SheetContent
                side='right'
                className='w-full gap-0 p-0 sm:max-w-md'
                aria-labelledby='activity-title'
                aria-describedby={undefined}>
                <div className='flex items-center justify-between border-b border-border px-5 py-4'>
                    <SheetTitle asChild>
                        <h4 id='activity-title' className='text-base font-bold text-foreground'>
                            Recent Activity
                        </h4>
                    </SheetTitle>
                </div>
                {/* Activity Timeline */}
                <div data-simplebar className='flex h-full min-h-0 flex-1 flex-col overflow-y-auto'>
                    {/* Timeline Items */}
                    <div className='activity-timeline-list' tabIndex={0} role='region' aria-label='Recent activity'>
                        {/* Task Assignment Activity */}
                        <div className='activity-item' data-category='tasks'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-blue-400 to-blue-600 text-sm font-semibold text-white'>
                                    JC
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-blue-600 dark:text-blue-400'>James Collins</span>
                                            added 2 files to task
                                            <span className='inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'>
                                                <i className='icon-[mdi--folder-outline] h-3 w-3' />
                                                PR-3
                                            </span>
                                        </p>
                                        <div className='mt-2 flex gap-2'>
                                            <div className='flex items-center gap-2 rounded-md bg-muted px-2 py-1 text-xs'>
                                                <i className='icon-[mdi--file-document-outline] h-3 w-3 text-muted-foreground' />
                                                <span className='text-foreground dark:text-muted-foreground'>
                                                    list-of-users
                                                </span>
                                                <span className='text-muted-foreground'>35kb</span>
                                            </div>
                                            <div className='flex items-center gap-2 rounded-md bg-muted px-2 py-1 text-xs'>
                                                <i className='icon-[mdi--github] h-3 w-3 text-muted-foreground' />
                                                <span className='text-foreground dark:text-muted-foreground'>
                                                    list-of-users
                                                </span>
                                                <span className='text-muted-foreground'>30kb</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>MAY 04</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* PR Status Update */}
                        <div className='activity-item' data-category='tasks'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-green-400 to-green-600 text-sm font-semibold text-white'>
                                    BD
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-green-600 dark:text-green-400'>Bob Dean</span>
                                            marked
                                            <span className='inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'>
                                                <i className='icon-[mdi--source-pull] h-3 w-3' />
                                                PR-6
                                            </span>
                                            as
                                            <span className='inline-flex items-center gap-1 rounded bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-300'>
                                                <i className='icon-[mdi--check-circle] h-3 w-3' />
                                                Completed
                                            </span>
                                        </p>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>TODAY</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Payment Cards Activity */}
                        <div className='activity-item' data-category='tasks'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-purple-400 to-purple-600 text-sm font-semibold text-white'>
                                    C
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-purple-600 dark:text-purple-400'>Crane</span>
                                            added 5 cards to
                                            <span className='inline-flex items-center gap-1 rounded bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/20 dark:text-purple-300'>
                                                <i className='icon-[mdi--credit-card-outline] h-3 w-3' />
                                                Payments
                                            </span>
                                        </p>
                                        <div className='mt-2 flex gap-1'>
                                            <div className='h-8 w-12 rounded bg-linear-to-r from-secondary to-muted-foreground' />
                                            <div className='h-8 w-12 rounded bg-linear-to-r from-purple-400 to-blue-600' />
                                            <div className='h-8 w-12 rounded bg-linear-to-r from-pink-400 to-red-600' />
                                            <div className='flex h-8 w-8 items-center justify-center rounded bg-muted text-xs text-muted-foreground dark:text-muted-foreground'>
                                                +2
                                            </div>
                                        </div>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>TODAY</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Project Status Update */}
                        <div className='activity-item' data-category='tasks'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-orange-400 to-orange-600 text-sm text-white'>
                                    <i className='icon-[mdi--pencil] h-4 w-4' />
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            Project status updated - marked
                                            <span className='inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'>
                                                <i className='icon-[mdi--source-pull] h-3 w-3' />
                                                PR-3
                                            </span>
                                            as
                                            <span className='inline-flex items-center gap-1 rounded bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300'>
                                                <i className='icon-[mdi--progress-clock] h-3 w-3' />
                                                In progress
                                            </span>
                                        </p>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>FEB 10</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Badge Achievement */}
                        <div className='activity-item' data-category='team'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-amber-400 to-amber-600 text-sm font-semibold text-white'>
                                    MC
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-amber-600 dark:text-amber-400'>Mark Colbert</span>
                                            earned a
                                            <span className='inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'>
                                                <i className='icon-[mdi--medal] h-3 w-3' />
                                                "Top endorsed"
                                            </span>
                                            badge
                                        </p>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>APR 06</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Team Member Added */}
                        <div className='activity-item' data-category='team'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-teal-400 to-teal-600 text-sm font-semibold text-white'>
                                    DL
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-teal-600 dark:text-teal-400'>David Lidell</span>
                                            added a new member to
                                            <span className='inline-flex items-center gap-1 rounded bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-700 dark:bg-teal-900/20 dark:text-teal-300'>
                                                <i className='icon-[mdi--account-group] h-3 w-3' />
                                                Orbin
                                            </span>
                                        </p>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>MAY 15</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Comment Activity */}
                        <div className='activity-item' data-category='comments'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-primary-400 to-primary-600 text-sm font-semibold text-white'>
                                    AS
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-primary-600 dark:text-primary-400'>Alice Smith</span>
                                            commented on
                                            <span className='inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'>
                                                <i className='icon-[mdi--source-pull] h-3 w-3' />
                                                PR-3
                                            </span>
                                        </p>
                                        <div className='mt-2 rounded-md bg-muted p-2'>
                                            <p className='text-xs text-muted-foreground'>
                                                "Great work on the user interface improvements! The new design looks
                                                much cleaner."
                                            </p>
                                        </div>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>2h ago</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Git Commit Activity */}
                        <div className='activity-item' data-category='commits'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-secondary to-muted-foreground text-sm text-white'>
                                    <i className='icon-[mdi--git] h-4 w-4' />
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-muted-foreground'>Mike Johnson</span>
                                            pushed 3 commits to
                                            <span className='inline-flex items-center gap-1 rounded bg-muted px-2 py-0.5 text-xs font-medium text-foreground dark:text-muted-foreground'>
                                                <i className='icon-[mdi--source-branch] h-3 w-3' />
                                                feature/user-auth
                                            </span>
                                        </p>
                                        <div className='mt-2 space-y-1'>
                                            <div className='text-xs text-muted-foreground'>
                                                <code className='font-mono'>a1b2c3d</code>- Add user authentication
                                                validation
                                            </div>
                                            <div className='text-xs text-muted-foreground'>
                                                <code className='font-mono'>d4e5f6g</code>- Fix login form styling
                                                issues
                                            </div>
                                            <div className='text-xs text-muted-foreground'>
                                                <code className='font-mono'>h7i8j9k</code>- Update password requirements
                                            </div>
                                        </div>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>4h ago</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Database Backup Activity */}
                        <div className='activity-item' data-category='system'>
                            <div className='activity-avatar shrink-0'>
                                <div className='flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-emerald-400 to-emerald-600 text-sm text-white'>
                                    <i className='icon-[mdi--database] h-4 w-4' />
                                </div>
                            </div>
                            <div className='activity-content min-w-0 flex-1'>
                                <div className='activity-header flex items-start justify-between'>
                                    <div className='flex-1'>
                                        <p className='text-sm font-medium text-foreground'>
                                            <span className='text-emerald-600 dark:text-emerald-400'>
                                                Automated Backup
                                            </span>
                                            completed successfully
                                            <span className='inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'>
                                                <i className='icon-[mdi--check-circle] h-3 w-3' />
                                                Backup Complete
                                            </span>
                                        </p>
                                        <div className='mt-2 grid grid-cols-2 gap-2 text-xs'>
                                            <div className='rounded bg-muted px-2 py-1'>
                                                <span className='text-muted-foreground'>Size:</span>
                                                <span className='text-foreground dark:text-muted-foreground'>
                                                    2.4 GB
                                                </span>
                                            </div>
                                            <div className='rounded bg-muted px-2 py-1'>
                                                <span className='text-muted-foreground'>Tables:</span>
                                                <span className='text-foreground dark:text-muted-foreground'>47</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='shrink-0 text-end'>
                                        <time className='text-xs text-muted-foreground'>6h ago</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {/* Load More Button */}
                    <div className='flex justify-center border-t border-border px-4 py-3 pb-0'>
                        <Button variant='light'>
                            <i className='icon-[mdi--refresh] h-4 w-4' />
                            Load more activities
                        </Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    );
}
