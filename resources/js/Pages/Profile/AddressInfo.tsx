import { Button } from "@/Components/ui/button";
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { ArrowRight, InfoIcon, MapPin, SendIcon } from "lucide-react";
import { useState, useEffect } from "react";
import axios from "axios";

interface Area {
    id: number;
    name: string;
    has_village: boolean;
}

interface City {
    id: number;
    name: string;
    areas: Area[];
}

interface Gov {
    id: number;
    name: string;
    cities: City[];
}

interface Props extends PageProps {
    phone: string;
    govs: Gov[];
}

const formSchema = z.object({
    otp: z.string().length(6, { message: "يجب أن يكون رمز التحقق 6 أرقام" }),
    gov_id: z.string(),
    city_id: z.string(),
    area_id: z.string(),
    village: z.string().optional().or(z.literal("")),
    location: z.string().optional().or(z.literal("")),
    address: z.string().min(10, { message: "يرجى إدخال عنوان مفصل (10 أحرف على الأقل)" }),
});

export default function AddressInfo({ auth, phone, govs }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [countdown, setCountdown] = useState(0);
    const [otpSent, setOtpSent] = useState(false);
    const [isRequestingOtp, setIsRequestingOtp] = useState(false);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [selectedGov, setSelectedGov] = useState<Gov | null>(null);
    const [selectedCity, setSelectedCity] = useState<City | null>(null);
    const [selectedArea, setSelectedArea] = useState<Area | null>(null);

    const customer = auth.user;

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            otp: "",
            gov_id: customer.gov_id?.toString() || "",
            city_id: customer.city_id?.toString() || "",
            area_id: customer.area_id?.toString() || "",
            village: customer.village || "",
            location: customer.location || "",
            address: customer.address || "",
        },
    });

    // Initialize selected values based on customer data
    useEffect(() => {
        if (govs && customer.gov_id) {
            const gov = govs.find(g => g.id === customer.gov_id);
            if (gov) {
                setSelectedGov(gov);

                const city = gov.cities.find(c => c.id === customer.city_id);
                if (city) {
                    setSelectedCity(city);

                    const area = city.areas.find(a => a.id === customer.area_id);
                    if (area) {
                        setSelectedArea(area);
                    }
                }
            }
        }
    }, [govs, customer]);

    const startCountdown = () => {
        setCountdown(60);
    };

    useEffect(() => {
        if (countdown > 0) {
            const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
            return () => clearTimeout(timer);
        }
    }, [countdown]);

    const handleSendOtp = async () => {
        setIsRequestingOtp(true);
        try {
            const response = await axios.post("/profile/send-otp");
            if (process.env.NODE_ENV === "development" && response.data.otp) {
                console.log("Address Update OTP:", response.data.otp);
            }
            setOtpSent(true);
            startCountdown();
        } catch (error) {
            console.error("Failed to send OTP:", error);
        } finally {
            setIsRequestingOtp(false);
        }
    };

    function onSubmit(values: z.infer<typeof formSchema>) {
        setIsSubmitting(true);

        router.post('/profile/update-address', values, {
            onSuccess: () => {
                setIsSubmitting(false);
                router.visit('/profile');
            },
            onError: (errors) => {
                setIsSubmitting(false);
                Object.entries(errors).forEach(([key, value]) => {
                    form.setError(key as any, {
                        type: "manual",
                        message: value as string,
                    });
                });
            },
        });
    }

    return (
        <div className="container max-w-lg mx-auto py-4 space-y-6">
            <Button
                variant="ghost"
                onClick={() => router.visit('/profile')}
                className="flex items-center mb-4"
            >
                <ArrowRight className="ml-2 h-4 w-4" />
                العودة إلى الإعدادات
            </Button>

            <Card>
                <CardHeader>
                    <CardTitle className="text-right">تعديل العنوان</CardTitle>
                    <CardDescription className="text-right">
                        سيتم إرسال رمز التحقق إلى رقم هاتفك {phone}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!otpSent && (
                        <Alert className="mb-4">
                            <InfoIcon className="h-4 w-4" />
                            <AlertDescription>
                                يرجى طلب رمز التحقق أولاً لتعديل بيانات العنوان
                            </AlertDescription>
                        </Alert>
                    )}

                    <Form {...form}>
                        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                            <div className="flex items-end gap-2">
                                <FormField
                                    control={form.control}
                                    name="otp"
                                    render={({ field }) => (
                                        <FormItem className="text-right flex-1">
                                            <FormLabel>رمز التحقق</FormLabel>
                                            <FormControl>
                                                <Input
                                                    {...field}
                                                    dir="rtl"
                                                    type="text"
                                                    inputMode="numeric"
                                                    maxLength={6}
                                                />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                                <Button
                                    type="button"
                                    onClick={handleSendOtp}
                                    disabled={countdown > 0 || isRequestingOtp}
                                    className="h-10"
                                >
                                    {isRequestingOtp ? "جارٍ الإرسال..." : countdown > 0 ? `${countdown}` :
                                    <span className="flex items-center gap-1">
                                        <SendIcon className="h-4 w-4" />
                                        طلب الرمز
                                    </span>}
                                </Button>
                            </div>

                            <FormField
                                control={form.control}
                                name="gov_id"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>المحافظة</FormLabel>
                                        <Select
                                            onValueChange={(value) => {
                                                field.onChange(value);
                                                const gov = govs.find(g => g.id.toString() === value);
                                                if (gov) {
                                                    setSelectedGov(gov);
                                                    setSelectedCity(null);
                                                    setSelectedArea(null);
                                                    form.setValue("city_id", "");
                                                    form.setValue("area_id", "");
                                                }
                                            }}
                                            defaultValue={field.value}
                                            disabled={!otpSent}
                                        >
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="اختر المحافظة" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {govs.map((gov) => (
                                                    <SelectItem key={gov.id} value={gov.id.toString()}>
                                                        {gov.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <FormField
                                control={form.control}
                                name="city_id"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>المدينة</FormLabel>
                                        <Select
                                            onValueChange={(value) => {
                                                field.onChange(value);
                                                if (selectedGov) {
                                                    const city = selectedGov.cities.find(c => c.id.toString() === value);
                                                    if (city) {
                                                        setSelectedCity(city);
                                                        setSelectedArea(null);
                                                        form.setValue("area_id", "");
                                                    }
                                                }
                                            }}
                                            defaultValue={field.value}
                                            disabled={!selectedGov || !otpSent}
                                        >
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="اختر المدينة" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {selectedGov?.cities.map((city) => (
                                                    <SelectItem key={city.id} value={city.id.toString()}>
                                                        {city.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <FormField
                                control={form.control}
                                name="area_id"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>المنطقة</FormLabel>
                                        <Select
                                            onValueChange={(value) => {
                                                field.onChange(value);
                                                if (selectedCity) {
                                                    const area = selectedCity.areas.find(a => a.id.toString() === value);
                                                    if (area) {
                                                        setSelectedArea(area);
                                                    }
                                                }
                                            }}
                                            defaultValue={field.value}
                                            disabled={!selectedCity || !otpSent}
                                        >
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="اختر المنطقة" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {selectedCity?.areas.map((area) => (
                                                    <SelectItem key={area.id} value={area.id.toString()}>
                                                        {area.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            {selectedArea?.has_village && (
                                <FormField
                                    control={form.control}
                                    name="village"
                                    render={({ field }) => (
                                        <FormItem className="text-right">
                                            <FormLabel>القرية</FormLabel>
                                            <FormControl>
                                                <Input {...field} dir="rtl" disabled={!otpSent} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                            )}

                            <FormField
                                control={form.control}
                                name="address"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>العنوان التفصيلي</FormLabel>
                                        <FormControl>
                                            <Textarea {...field} dir="rtl" className="min-h-[100px]" disabled={!otpSent} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={isSubmitting || !otpSent}
                            >
                                {isSubmitting ? "جاري الحفظ..." : "حفظ التغييرات"}
                            </Button>
                        </form>
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}
