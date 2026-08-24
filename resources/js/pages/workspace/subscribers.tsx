import { Form, Head, router } from '@inertiajs/react';
import EmptyState from '@/components/empty-state';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { destroy, store } from '@/routes/workspace/subscribers';

type Subscriber = {
    id: number;
    channel: string;
    channelLabel: string;
    endpoint: string;
    isDeliverable: boolean;
    confirmedAt: string | null;
    unsubscribedAt: string | null;
    bounceCount: number;
    lastNotifiedAt: string | null;
};

type Props = {
    subscribers: Subscriber[];
    stats: {
        confirmed: number;
        pending: number;
        unsubscribed: number;
        limit: number | null;
        atLimit: boolean;
    };
    channels: { value: string; label: string; metered: boolean }[];
    can: { manage: boolean };
};

function Stat({ label, value }: { label: string; value: string | number }) {
    return (
        <Card>
            <CardContent className="p-5">
                <p className="text-2xl font-semibold tabular-nums">{value}</p>
                <p className="text-sm text-muted-foreground">{label}</p>
            </CardContent>
        </Card>
    );
}

export default function Subscribers({ subscribers, stats, channels, can }: Props) {
    const integrationChannels = channels.filter((channel) => channel.value !== 'email' && !channel.metered);

    return (
        <>
            <Head title="Subscribers" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">Subscribers</h2>
                    <p className="text-sm text-muted-foreground">
                        Email subscribers sign up from the public page and confirm before anything is sent.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <Stat
                        label={stats.limit ? `Confirmed of ${stats.limit}` : 'Confirmed'}
                        value={stats.confirmed}
                    />
                    <Stat label="Awaiting confirmation" value={stats.pending} />
                    <Stat label="Unsubscribed" value={stats.unsubscribed} />
                </div>

                {stats.atLimit && (
                    <div className="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-sm">
                        <strong>You have reached your plan&apos;s subscriber limit.</strong>
                        <p className="mt-1 text-muted-foreground">
                            New sign-ups are refused until you upgrade or remove subscribers.
                        </p>
                    </div>
                )}

                {can.manage && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Add an integration</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                resetOnSuccess={['endpoint']}
                                className="grid gap-4 sm:grid-cols-[1fr_2fr_auto] sm:items-start"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <label htmlFor="channel" className="form-label">
                                                Channel
                                            </label>
                                            <select id="channel" name="channel" className="form-control">
                                                {integrationChannels.map((channel) => (
                                                    <option key={channel.value} value={channel.value}>
                                                        {channel.label}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors.channel} />
                                        </div>

                                        <div>
                                            <label htmlFor="endpoint" className="form-label label-required">
                                                Webhook URL
                                            </label>
                                            <input
                                                id="endpoint"
                                                name="endpoint"
                                                required
                                                placeholder="https://hooks.slack.com/services/..."
                                                className={cn('form-control', errors.endpoint && 'is-invalid')}
                                            />
                                            <InputError message={errors.endpoint} />
                                        </div>

                                        <div>
                                            <label className="form-label sm:opacity-0">Action</label>
                                            <Button type="submit" disabled={processing}>
                                                {processing && <Spinner />}
                                                Add
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Recent subscribers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {subscribers.length === 0 ? (
                            <EmptyState
                                title="No subscribers yet"
                                description="People who subscribe from your public page appear here."
                            />
                        ) : (
                            <div className="table-responsive">
                                <table className="tw-table">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Channel</th>
                                            <th>State</th>
                                            <th>Last notified</th>
                                            {can.manage && <th className="text-right">Actions</th>}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {subscribers.map((subscriber) => (
                                            <tr key={subscriber.id}>
                                                <td className="font-mono text-xs">{subscriber.endpoint}</td>
                                                <td className="text-muted-foreground">{subscriber.channelLabel}</td>
                                                <td>
                                                    {subscriber.unsubscribedAt ? (
                                                        <Badge className="border-transparent bg-muted text-muted-foreground">
                                                            Unsubscribed
                                                        </Badge>
                                                    ) : subscriber.confirmedAt ? (
                                                        <Badge className="border-transparent bg-green-500/15 text-green-700 dark:text-green-300">
                                                            Confirmed
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="border-transparent bg-amber-500/15 text-amber-700 dark:text-amber-300">
                                                            Pending
                                                        </Badge>
                                                    )}
                                                    {subscriber.bounceCount > 0 && (
                                                        <span className="ms-2 text-xs text-destructive">
                                                            {subscriber.bounceCount} bounces
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {formatDateTime(subscriber.lastNotifiedAt)}
                                                </td>
                                                {can.manage && (
                                                    <td className="text-right">
                                                        <Button
                                                            variant="soft-destructive"
                                                            size="xs"
                                                            onClick={() =>
                                                                router.delete(
                                                                    destroy({
                                                                        subscriber: subscriber.id,
                                                                    }).url,
                                                                    { preserveScroll: true },
                                                                )
                                                            }
                                                        >
                                                            Remove
                                                        </Button>
                                                    </td>
                                                )}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
