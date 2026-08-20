import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Forgot password" />

            {status && (
                <div className="mb-4 rounded-md bg-green-500/10 p-3 text-center text-sm font-medium text-green-700 dark:text-green-300">
                    {status}
                </div>
            )}

            <Form {...email.form()} className="my-3">
                {({ processing, errors }) => (
                    <>
                        <div className="block">
                            <label htmlFor="email" className="form-label label-required">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autoFocus
                                autoComplete="email"
                                placeholder="Email address"
                                aria-invalid={!!errors.email}
                                className={cn('form-control', errors.email && 'is-invalid')}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <Button type="submit" disabled={processing} className="mt-4 w-full">
                            {processing && <Spinner />}
                            Email password reset link
                        </Button>
                    </>
                )}
            </Form>

            <p className="text-sm">
                Or, return to{' '}
                <Link href={login()} className="text-primary hover:underline">
                    log in
                </Link>
            </p>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Forgot your password?',
    description: 'Enter your email and we will send you a reset link',
};
