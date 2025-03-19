/**
 * Custom hook for formatting relative time in Arabic
 * Provides human-readable time difference between a date and now
 */
export function useRelativeTime() {
    /**
     * Format a date string into a relative time string in Arabic
     * @param dateStr - The date string to format
     * @returns Formatted relative time in Arabic
     */
    const formatRelativeTime = (dateStr: string): string => {
        const now = new Date();
        const notificationDate = new Date(dateStr);
        const diffMs = now.getTime() - notificationDate.getTime();

        // Different time units in milliseconds
        const minute = 60 * 1000;
        const hour = 60 * minute;
        const day = 24 * hour;

        // Format based on how recent the date is
        if (diffMs < minute) {
            // Less than a minute
            return 'الآن';
        } else if (diffMs < hour) {
            // Less than an hour
            const minutes = Math.floor(diffMs / minute);
            return `منذ ${minutes} ${minutes === 1 ? 'دقيقة' : 'دقائق'}`;
        } else if (diffMs < day) {
            // Less than a day
            const hours = Math.floor(diffMs / hour);
            return `منذ ${hours} ${hours === 1 ? 'ساعة' : 'ساعات'}`;
        } else if (diffMs < 7 * day) {
            // Less than a week
            const days = Math.floor(diffMs / day);
            return `منذ ${days} ${days === 1 ? 'يوم' : 'أيام'}`;
        } else {
            // Format date for older dates
            const options: Intl.DateTimeFormatOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return new Intl.DateTimeFormat('ar-EG', options).format(notificationDate);
        }
    };

    return { formatRelativeTime };
}
