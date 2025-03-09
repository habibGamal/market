import { Button } from "@/Components/ui/button";
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import { PasswordInput } from "@/Components/ui/password-input";

interface Props {
    areas: Array<{ id: string; name: string }>;
    businessTypes: Array<{ id: string; name: string }>;
    govs: Array<{ id: string; name: string }>;
    cities: Array<{ id: string; govId: string; name: string }>;
    citiesWithVillages: string[];
}

const formSchema = z.object({
    name: z.string().min(3, "الاسم يجب أن يكون 3 أحرف على الأقل"),
    gov: z.string().min(1, "المحافظة مطلوبة"),
    city: z.string().min(1, "المدينة مطلوبة"),
    village: z.string().optional(),
    area_id: z.string().min(1, "المنطقة مطلوبة"),
    address: z.string().min(10, "العنوان يجب أن يكون 10 أحرف على الأقل"),
    location: z.string().optional(),
    phone: z.string().min(11, "رقم الهاتف يجب أن يكون 11 رقم"),
    whatsapp: z.string().optional(),
    email: z
        .string()
        .email("البريد الإلكتروني غير صحيح")
        .optional()
        .or(z.literal("")),
    password: z.string().min(8, "كلمة المرور يجب أن تكون 8 أحرف على الأقل"),
    business_type_id: z.string().min(1, "نوع النشاط التجاري مطلوب"),
});

export default function Register({
    areas,
    businessTypes,
    govs,
    cities,
    citiesWithVillages,
}: Props) {
    const [isLoading, setIsLoading] = useState(false);
    const [filteredCities, setFilteredCities] = useState(cities);
    const [selectedCity, setSelectedCity] = useState("");
    const [showVillage, setShowVillage] = useState(false);
    const [filteredAreas, setFilteredAreas] = useState(areas);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: "",
            gov: "",
            city: "",
            village: "",
            area_id: "",
            address: "",
            location: "",
            phone: "",
            whatsapp: "",
            email: "",
            password: "",
            business_type_id: "",
        },
    });

    useEffect(() => {
        // Get geolocation when component mounts
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    form.setValue("location", `${latitude},${longitude}`);
                },
                (error) => {
                    console.error("Error getting location:", error);
                }
            );
        }
    }, []);

    useEffect(() => {
        const gov = form.watch("gov");
        if (gov) {
            setFilteredCities(cities.filter((city) => city.govId === gov));
        }
    }, [form.watch("gov")]);

    useEffect(() => {
        const city = form.watch("city");
        setSelectedCity(city);
        setShowVillage(citiesWithVillages.includes(city));
        // Here you would typically filter areas based on city
        // For now we're just using all areas
    }, [form.watch("city")]);

    const onSubmit = async (data: z.infer<typeof formSchema>) => {
        console.log(data);
        router.post("/register", data, {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
            onError: (errors) => {
                Object.entries(errors).forEach(([key, value]) => {
                    form.setError(key as any, {
                        type: "server",
                        message: value,
                    });
                })
            },
        });
    };
    console.log(form.formState.errors);

    return (
        <div className="container max-w-lg mx-auto py-4 space-y-6">
            <div className="text-center space-y-2">
                <h1 className="text-2xl font-bold">تسجيل حساب جديد</h1>
                <p className="text-secondary-500">
                    أدخل بياناتك للتسجيل في المنصة
                </p>
            </div>

            <Form {...form}>
                <form
                    onSubmit={form.handleSubmit(onSubmit)}
                    className="space-y-4"
                >
                    <FormField
                        control={form.control}
                        name="name"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>الاسم</FormLabel>
                                <FormControl>
                                    <Input {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            control={form.control}
                            name="gov"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>المحافظة</FormLabel>
                                    <Select
                                        onValueChange={field.onChange}
                                        defaultValue={field.value}
                                    >
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="اختر المحافظة" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            {govs.map((gov) => (
                                                <SelectItem
                                                    key={gov.id}
                                                    value={gov.id}
                                                >
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
                            name="city"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>المدينة</FormLabel>
                                    <Select
                                        onValueChange={field.onChange}
                                        defaultValue={field.value}
                                        disabled={!form.watch("gov")}
                                    >
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="اختر المدينة" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            {filteredCities.map((city) => (
                                                <SelectItem
                                                    key={city.id}
                                                    value={city.id}
                                                >
                                                    {city.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    </div>

                    {showVillage && (
                        <FormField
                            control={form.control}
                            name="village"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>القرية</FormLabel>
                                    <FormControl>
                                        <Input {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    )}

                    <FormField
                        control={form.control}
                        name="area_id"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>المنطقة</FormLabel>
                                <Select
                                    onValueChange={field.onChange}
                                    defaultValue={field.value}
                                    disabled={!selectedCity}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر المنطقة" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        {filteredAreas.map((area) => (
                                            <SelectItem
                                                key={area.id}
                                                value={area.id.toString()}
                                            >
                                                {area.name}
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
                        name="address"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>العنوان التفصيلي</FormLabel>
                                <FormControl>
                                    <Textarea
                                        {...field}
                                        className="min-h-[100px]"
                                        placeholder="أدخل العنوان التفصيلي..."
                                    />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            control={form.control}
                            name="phone"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>رقم الهاتف</FormLabel>
                                    <FormControl>
                                        <Input type="tel" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="whatsapp"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>
                                        رقم الواتساب (اختياري)
                                    </FormLabel>
                                    <FormControl>
                                        <Input type="tel" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    </div>

                    <FormField
                        control={form.control}
                        name="email"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>
                                    البريد الإلكتروني (اختياري)
                                </FormLabel>
                                <FormControl>
                                    <Input type="email" {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="business_type_id"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>نوع النشاط التجاري</FormLabel>
                                <Select
                                    onValueChange={field.onChange}
                                    defaultValue={field.value}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر نوع النشاط" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        {businessTypes.map((type) => (
                                            <SelectItem
                                                key={type.id}
                                                value={type.id.toString()}
                                            >
                                                {type.name}
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
                        name="password"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>كلمة المرور</FormLabel>
                                <FormControl>
                                    <PasswordInput {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={isLoading}
                    >
                        {isLoading ? "جاري التسجيل..." : "تسجيل"}
                    </Button>
                </form>
            </Form>
        </div>
    );
}
