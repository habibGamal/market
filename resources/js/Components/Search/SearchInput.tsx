import { useState, useRef, useEffect } from "react"
import { Input } from "@/Components/ui/input"
import { Search, Loader2 } from "lucide-react"
import { useClickAway } from "@/Hooks/useClickAway"
import debounce from "lodash/debounce"
import { router } from "@inertiajs/react"
import axios from "axios"
import { cn } from "@/lib/utils"

interface SearchSuggestion {
    id: number
    name: string
    image: string
    category: string
}

interface SearchInputProps {
    fullWidth?: boolean;
    initialQuery?: string;
    onSearch?: (query: string) => void;
}

export function SearchInput({ fullWidth = false, initialQuery = "", onSearch }: SearchInputProps) {
    const [query, setQuery] = useState(initialQuery)
    const [suggestions, setSuggestions] = useState<SearchSuggestion[]>([])
    const [loading, setLoading] = useState(false)
    const [showSuggestions, setShowSuggestions] = useState(false)
    const containerRef = useRef<HTMLDivElement>(null)
    const inputRef = useRef<HTMLInputElement>(null)

    useClickAway(containerRef, () => setShowSuggestions(false))

    const debouncedSearch = debounce(async (searchQuery: string) => {
        if (!searchQuery || searchQuery.length < 2) {
            setSuggestions([])
            setLoading(false)
            return
        }

        try {
            const { data } = await axios.get('/api/search', {
                params: { q: searchQuery }
            })
            setSuggestions(data)
            setShowSuggestions(true)
        } catch (error) {
            console.error('Search error:', error)
            setSuggestions([])
        } finally {
            setLoading(false)
        }
    }, 300)

    useEffect(() => {
        if (query) {
            setLoading(true)
            debouncedSearch(query)
        } else {
            setSuggestions([])
            setLoading(false)
        }

        return () => {
            debouncedSearch.cancel()
        }
    }, [query])

    const handleSearch = (e?: React.FormEvent) => {
        e?.preventDefault()

        if (query.trim()) {
            if (onSearch) {
                onSearch(query)
            } else {
                router.get(`/search?q=${encodeURIComponent(query)}`)
            }
            setShowSuggestions(false)
        }
    }

    const handleSelect = (suggestion: SearchSuggestion) => {
        router.visit(`/products/${suggestion.id}`)
        setQuery("")
        setShowSuggestions(false)
    }

    return (
        <div ref={containerRef} className="relative">
            <form onSubmit={handleSearch}>
                <div className="relative">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-500" />
                    <Input
                        ref={inputRef}
                        placeholder="ابحث عن منتجات..."
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onFocus={() => query && setShowSuggestions(true)}
                        className="w-full pl-10 pr-8"
                    />
                    {loading && (
                        <Loader2 className="absolute right-3 top-[10px] h-4 w-4 animate-spin text-secondary-500" />
                    )}
                </div>
            </form>

            {/* Suggestions Dropdown */}
            {showSuggestions && suggestions.length > 0 && (
                <div className="absolute z-50 mt-1 w-full overflow-hidden rounded-md border bg-white shadow-lg">
                    <div className="max-h-[60vh] overflow-auto">
                        {suggestions.map((suggestion) => (
                            <button
                                key={suggestion.id}
                                onClick={() => handleSelect(suggestion)}
                                className="flex w-full items-center gap-3 px-4 py-2 hover:bg-secondary-50 transition-colors"
                            >
                                <div className="h-10 w-10 flex-shrink-0 overflow-hidden rounded">
                                    <img
                                        src={suggestion.image}
                                        alt={suggestion.name}
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="flex-1 text-right">
                                    <p className="text-sm font-medium text-secondary-900">
                                        {suggestion.name}
                                    </p>
                                    <p className="text-xs text-secondary-500">
                                        {suggestion.category}
                                    </p>
                                </div>
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    )
}
