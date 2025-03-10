import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Minus, Plus } from "lucide-react";
import { useState, useEffect } from "react";

interface QuantityInputProps {
    value: number;
    onChange: (value: number) => void;
    min?: number;
    max?: number;
    disabled?: boolean;
    className?: string;
}

export function QuantityInput({
    value,
    onChange,
    min = 0,
    max,
    disabled = false,
    className = "",
}: QuantityInputProps) {
    const [inputValue, setInputValue] = useState(value);

    useEffect(() => {
        setInputValue(value);
    }, [value]);

    const handleDecrement = () => {
        if (disabled) return;
        const newValue = Math.max(min, inputValue - 1);
        setInputValue(newValue);
        onChange(newValue);
    };

    const handleIncrement = () => {
        if (disabled) return;
        const newValue =
            max !== undefined ? Math.min(max, inputValue + 1) : inputValue + 1;
        setInputValue(newValue);
        onChange(newValue);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const rawValue = parseInt(e.target.value) || 0;
        let newValue = Math.max(min, rawValue);
        if (max !== undefined) {
            newValue = Math.min(max, newValue);
        }
        setInputValue(newValue);
        onChange(newValue);
    };

    return (
        <div className={`flex items-center ${className}`}>
            <Button
                variant="outline"
                size="icon"
                className="h-8 w-8 rounded-l-none"
                onClick={handleDecrement}
                disabled={disabled || inputValue <= min}
            >
                <Minus className="h-4 w-4" />
            </Button>
            <Input
                type="number"
                min={min}
                max={max}
                value={inputValue}
                onChange={handleChange}
                className="h-8 w-16 rounded-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                disabled={disabled}
                onFocus={(e) => e.target.select()}
            />
            <Button
                variant="outline"
                size="icon"
                className="h-8 w-8 rounded-r-none"
                onClick={handleIncrement}
                disabled={disabled || (max !== undefined && inputValue >= max)}
            >
                <Plus className="h-4 w-4" />
            </Button>
        </div>
    );
}
