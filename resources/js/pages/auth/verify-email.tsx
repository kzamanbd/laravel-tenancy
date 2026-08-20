import { Form, Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Email verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 rounded-md bg-green-500/10 p-3 text-center text-sm font-medium text-green-700 dark:text-green-300">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            )}

            <Form {...send.form()} className="my-3 space-y-4 text-center">
                {({ processing }) => (
                    <>
                        <Button type="submit" disabled={processing} variant="light" className="w-full">
                            {processing && <Spinner />}
                            Resend verification email
                        </Button>

                        <Link href={logout()} className="mx-auto block text-sm text-primary hover:underline">
                            Log out
                        </Link>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verify your email',
    description: 'Click the link we just emailed you to finish setting up your account',
};
