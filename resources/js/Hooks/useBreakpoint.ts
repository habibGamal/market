import { useEffect, useState } from "react";

const breakpoints = {
    'sm': 640,
    'md': 768,
    'lg': 1024,
    'xl': 1280,
    '2xl': 1536,
} as const;

type Breakpoint = keyof typeof breakpoints;

export function useBreakpoint(breakpoint: Breakpoint) {
    const [matches, setMatches] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia(`(min-width: ${breakpoints[breakpoint]}px)`);
        setMatches(mediaQuery.matches);

        function handleChange(e: MediaQueryListEvent) {
            setMatches(e.matches);
        }

        mediaQuery.addEventListener('change', handleChange);
        return () => mediaQuery.removeEventListener('change', handleChange);
    }, [breakpoint]);

    return {
        [`is${breakpoint.charAt(0).toUpperCase()}${breakpoint.slice(1)}`]: matches,
        matches,
    };
}