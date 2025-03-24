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
import { Governorate } from "@/types";
import { registerSW } from "@/register";

interface Props {
    businessTypes: Array<{ id: string; name: string }>;
    govs: Array<Governorate>;
}

const formSchema = z.object({
    name: z.string().min(3, "الاسم يجب أن يكون 3 أحرف على الأقل"),
    gov_id: z.string().min(1, "المحافظة مطلوبة"),
    city_id: z.string().min(1, "المدينة مطلوبة"),
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

export default function Register({ businessTypes, govs }: Props) {
    const [isLoading, setIsLoading] = useState(false);
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: "",
            gov_id: "",
            city_id: "",
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
    const cities =
        govs.find((g) => g.id.toString() === form.watch("gov_id"))?.cities ?? [];

    const areas =
        cities.find((c) => c.id.toString() === form.watch("city_id"))?.areas ?? [];

    const showVillage =
        form.watch("area_id") &&
        govs
            .find((g) => g.id.toString() === form.watch("gov_id"))
            ?.cities.find((c) => c.id.toString() === form.watch("city_id"))
            ?.areas.find((a) => a.id.toString() === form.watch("area_id"))
            ?.has_village;

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

    const onSubmit = async (data: z.infer<typeof formSchema>) => {
        console.log(data);
        router.post("/register", data, {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
            onSuccess: async () => {
                await registerSW();
            },
            onError: (errors) => {
                Object.entries(errors).forEach(([key, value]) => {
                    form.setError(key as any, {
                        type: "server",
                        message: value,
                    });
                });
            },
        });
    };

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
                            name="gov_id"
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
                                                    value={gov.id.toString()}
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
                            name="city_id"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>المدينة</FormLabel>
                                    <Select
                                        onValueChange={field.onChange}
                                        defaultValue={field.value}
                                        disabled={!form.watch("gov_id")}
                                    >
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="اختر المدينة" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            {cities.map((city) => (
                                                <SelectItem
                                                    key={city.id}
                                                    value={city.id.toString()}
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

                    <FormField
                        control={form.control}
                        name="area_id"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>المنطقة</FormLabel>
                                <Select
                                    onValueChange={field.onChange}
                                    defaultValue={field.value}
                                    disabled={!form.watch("city_id")}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر المنطقة" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        {areas.map((area) => (
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
