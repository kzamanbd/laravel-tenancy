import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordField from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Log in" />

            {status && (
                <div className="mb-4 rounded-md bg-green-500/10 p-3 text-center text-sm font-medium text-green-700 dark:text-green-300">
                    {status}
                </div>
            )}

            <Form {...store.form()} resetOnSuccess={['password']} className="my-3">
                {({ processing, errors }) => (
                    <>
                        <div className="block">
                            <label htmlFor="email" className="form-label">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                placeholder="Email address"
                                aria-invalid={!!errors.email}
                                className={cn('form-control', errors.email && 'is-invalid')}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <PasswordField
                            id="password"
                            name="password"
                            label="Password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            placeholder="Enter password"
                            error={errors.password}
                        />

                        <div className="mb-4 flex items-center justify-between">
                            <label className="inline-flex items-center">
                                <input type="checkbox" name="remember" tabIndex={3} className="form-check-input" />
                                <span className="ms-3 text-sm">Remember me</span>
                            </label>

                            {canResetPassword && (
                                <Link href={request()} tabIndex={5} className="block text-sm text-primary hover:underline">
                                    Forgot password?
                                </Link>
                            )}
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            tabIndex={4}
                            className="w-full"
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            Sign in to your account
                        </Button>
                    </>
                )}
            </Form>

            <p className="text-sm">
                Don&apos;t have an account yet?{' '}
                <Link href={register()} data-test="register-link" className="text-primary hover:underline">
                    Sign up here
                </Link>
            </p>
        </>
    );
}

Login.layout = {
    title: 'Log in to your account',
    description: 'Please sign in to your account and start the adventure',
};
