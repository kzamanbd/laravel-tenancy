import { useId  } from 'react';
import type {ReactNode} from 'react';
import { cn } from '@/lib/utils';

/**
 * Validated field styled with the design system's form classes.
 *
 * The template's version read state from react-hook-form. Inertia hands
 * validation errors back as a plain `errors` object, so the message is passed
 * in directly and the control stays uncontrolled.
 */
export default function FormField({
    name,
    label,
    type = 'text',
    placeholder,
    required,
    as = 'input',
    autoComplete,
    error,
    defaultValue,
    children,
}: {
    name: string;
    label?: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    as?: 'input' | 'select' | 'textarea';
    autoComplete?: string;
    error?: string;
    defaultValue?: string | number;
    children?: ReactNode;
}) {
    const fieldId = useId();
    const errorId = `${fieldId}-error`;
    const hasError = !!error;

    const shared = {
        id: fieldId,
        name,
        defaultValue,
        className: cn('form-control', hasError && 'is-invalid'),
        'aria-invalid': hasError,
        'aria-describedby': hasError ? errorId : undefined,
    };

    return (
        <div className="mb-3">
            {label && (
                <label htmlFor={fieldId} className={cn('form-label', required && 'label-required')}>
                    {label}
                </label>
            )}

            {as === 'textarea' ? (
                <textarea {...shared} placeholder={placeholder} rows={3} />
            ) : as === 'select' ? (
                <select {...shared}>{children}</select>
            ) : (
                <input {...shared} type={type} placeholder={placeholder} autoComplete={autoComplete} />
            )}

            {hasError && (
                <p id={errorId} className="mt-1 text-xs text-danger">
                    {error}
                </p>
            )}
        </div>
    );
}
