/** Shared formatting so every workspace screen renders timestamps identically. */
export const formatDateTime = (value: string | null | undefined): string =>
    value
        ? new Date(value).toLocaleString(undefined, {
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '—';

/** Value shaped for a `datetime-local` input, which wants local time, no zone. */
export const toDateTimeLocal = (value: string | Date): string => {
    const date = typeof value === 'string' ? new Date(value) : value;
    const offset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
};
