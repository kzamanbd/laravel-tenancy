import { Form, Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import EmptyState from '@/components/empty-state';
import InputError from '@/components/input-error';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { destroy, primary, store, verify } from '@/routes/workspace/domains';

type Instructions = {
    cname: { name: string; value: string };
    txt: { name: string; value: string };
};

type Domain = {
    id: number;
    domain: string;
    isCustom: boolean;
    isPrimary: boolean;
    status: string;
    statusLabel: string;
    verifiedAt: string | null;
    lastCheckedAt: string | null;
    certificateExpiresAt: string | null;
    instructions: Instructions | null;
};

type Props = {
    domains: Domain[];
    plan: { name: string | null; allowsCustomDomain: boolean };
    can: { manage: boolean };
};

const verificationTone: Record<string, string> = {
    verified: 'bg-green-500/15 text-green-700 dark:text-green-300',
    pending: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    failed: 'bg-destructive/15 text-destructive',
};

function DnsRow({ type, name, value }: { type: string; name: string; value: string }) {
    const [copied, setCopied] = useState(false);

    return (
        <div className="flex flex-wrap items-center gap-2 border-b border-border py-2 last:border-0">
            <span className="w-14 shrink-0 text-xs font-semibold text-muted-foreground uppercase">{type}</span>
            <code className="min-w-0 flex-1 truncate font-mono text-xs">{name}</code>
            <code className="min-w-0 flex-1 truncate font-mono text-xs text-muted-foreground">{value}</code>
            <Button
                variant="light"
                size="xs"
                onClick={() => {
                    void navigator.clipboard.writeText(value);
                    setCopied(true);
                    window.setTimeout(() => setCopied(false), 1500);
                }}
            >
                {copied ? 'Copied' : 'Copy'}
            </Button>
        </div>
    );
}

export default function Domains({ domains, plan, can }: Props) {
    const tenant = usePage<{ tenant: { id: number } }>().props.tenant;

    return (
        <>
            <Head title="Domains" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">Domains</h2>
                    <p className="text-sm text-muted-foreground">
                        Serve your status page on your own hostname. Certificates are issued automatically once
                        ownership is proven.
                    </p>
                </div>

                {!plan.allowsCustomDomain && (
                    <div className="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-sm">
                        <strong>Custom domains are not included in the {plan.name} plan.</strong>
                        <p className="mt-1 text-muted-foreground">
                            Your page stays available on its platform subdomain. Upgrade to connect your own hostname.
                        </p>
                    </div>
                )}

                {can.manage && plan.allowsCustomDomain && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Connect a domain</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form({ tenant: tenant.id })}
                                resetOnSuccess={['domain']}
                                className="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-start"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <label htmlFor="domain" className="form-label label-required">
                                                Hostname
                                            </label>
                                            <input
                                                id="domain"
                                                name="domain"
                                                required
                                                placeholder="status.example.com"
                                                className={cn('form-control', errors.domain && 'is-invalid')}
                                            />
                                            <p className="help-text mt-1">
                                                No <code>https://</code> and no trailing path.
                                            </p>
                                            <InputError message={errors.domain} />
                                        </div>

                                        <div>
                                            <label className="form-label sm:opacity-0">Action</label>
                                            <Button type="submit" disabled={processing}>
                                                {processing && <Spinner />}
                                                Add domain
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}

                {domains.length === 0 ? (
                    <Card>
                        <CardContent>
                            <EmptyState title="No domains" description="This page has no hostname yet." />
                        </CardContent>
                    </Card>
                ) : (
                    domains.map((domain) => (
                        <Card key={domain.id}>
                            <CardHeader className="flex-row flex-wrap items-center justify-between gap-2">
                                <div className="flex flex-wrap items-center gap-2">
                                    <CardTitle className="font-mono text-sm">{domain.domain}</CardTitle>
                                    <StatusBadge
                                        value={domain.status}
                                        label={domain.statusLabel}
                                        tones={verificationTone}
                                    />
                                    {domain.isPrimary && (
                                        <span className="rounded-md bg-primary/10 px-2 py-0.5 text-xs text-primary">
                                            Primary
                                        </span>
                                    )}
                                    {!domain.isCustom && (
                                        <span className="text-xs text-muted-foreground">Platform subdomain</span>
                                    )}
                                </div>

                                {can.manage && (
                                    <div className="flex items-center gap-2">
                                        {domain.isCustom && domain.status !== 'verified' && (
                                            <Button
                                                size="xs"
                                                onClick={() =>
                                                    router.post(
                                                        verify({ tenant: tenant.id, domain: domain.id }).url,
                                                        {},
                                                        { preserveScroll: true },
                                                    )
                                                }
                                            >
                                                Verify
                                            </Button>
                                        )}

                                        {domain.status === 'verified' && !domain.isPrimary && (
                                            <Button
                                                variant="light"
                                                size="xs"
                                                onClick={() =>
                                                    router.post(
                                                        primary({ tenant: tenant.id, domain: domain.id }).url,
                                                        {},
                                                        { preserveScroll: true },
                                                    )
                                                }
                                            >
                                                Make primary
                                            </Button>
                                        )}

                                        <Button
                                            variant="soft-destructive"
                                            size="xs"
                                            onClick={() =>
                                                router.delete(
                                                    destroy({ tenant: tenant.id, domain: domain.id }).url,
                                                    { preserveScroll: true },
                                                )
                                            }
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                )}
                            </CardHeader>

                            <CardContent>
                                {domain.instructions && domain.status !== 'verified' && (
                                    <>
                                        <p className="mb-2 text-sm text-muted-foreground">
                                            Add <em>either</em> record, then press Verify. DNS can take a few minutes.
                                        </p>
                                        <div className="rounded-lg border border-border p-3">
                                            <DnsRow
                                                type="CNAME"
                                                name={domain.instructions.cname.name}
                                                value={domain.instructions.cname.value}
                                            />
                                            <DnsRow
                                                type="TXT"
                                                name={domain.instructions.txt.name}
                                                value={domain.instructions.txt.value}
                                            />
                                        </div>
                                    </>
                                )}

                                <p className="mt-2 text-xs text-muted-foreground">
                                    {domain.verifiedAt
                                        ? `Verified ${formatDateTime(domain.verifiedAt)}`
                                        : 'Not verified yet.'}
                                    {domain.certificateExpiresAt &&
                                        ` · Certificate expires ${formatDateTime(domain.certificateExpiresAt)}`}
                                    {domain.lastCheckedAt && ` · Last checked ${formatDateTime(domain.lastCheckedAt)}`}
                                </p>
                            </CardContent>
                        </Card>
                    ))
                )}
            </div>
        </>
    );
}
