import { useState  } from 'react';
import type {ComponentProps} from 'react';
import InputError from '@/components/input-error';
import { cn } from '@/lib/utils';

/**
 * Password input with a reveal toggle, styled with the design system's form
 * classes. The toggle is a real button so it is reachable by keyboard and
 * announces its state.
 */
export default function PasswordField({
    id,
    label,
    error,
    className,
    ...props
}: ComponentProps<'input'> & { label: string; error?: string }) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="my-2">
            <label htmlFor={id} className="form-label">
                {label}
            </label>

            <div className="relative">
                <input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    aria-invalid={!!error}
                    className={cn('form-control pe-10', error && 'is-invalid', className)}
                    {...props}
                />

                <button
                    type="button"
                    aria-label={visible ? 'Hide password' : 'Show password'}
                    aria-pressed={visible}
                    tabIndex={-1}
                    onClick={() => setVisible((current) => !current)}
                    className="absolute inset-y-0 end-0 z-20 flex cursor-pointer items-center rounded-e-md px-3 text-muted-foreground hover:text-foreground"
                >
                    <span
                        className={cn(
                            'size-4 shrink-0',
                            visible ? 'icon-[mdi--eye-off-outline]' : 'icon-[mdi--eye-outline]',
                        )}
                    />
                </button>
            </div>

            <InputError message={error} />
        </div>
    );
}
