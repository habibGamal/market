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
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useState } from "react";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";

const formSchema = z.object({
    name: z.string().min(1, "الاسم مطلوب"),
    email: z.string().email("البريد الإلكتروني غير صالح"),
    phone: z.string().min(11, "رقم الهاتف يجب أن يكون 11 رقم"),
    address: z.string().min(1, "العنوان مطلوب"),
});

export default function EditProfile({ auth }: PageProps) {
    const [isLoading, setIsLoading] = useState(false);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: auth.user.name,
            email: auth.user.email,
            phone: auth.user.phone,
            address: auth.user.address,
        },
    });

    const onSubmit = async (data: z.infer<typeof formSchema>) => {
        router.patch('/profile', data, {
            preserveScroll: true,
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
                });
            },
        });
    };

    return (
        <div className="container max-w-lg mx-auto py-4 space-y-6">
            <div className="text-center space-y-2 mb-6">
                <h1 className="text-2xl font-bold">تعديل الملف الشخصي</h1>
                <p className="text-secondary-500">قم بتحديث معلوماتك الشخصية</p>
            </div>

            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                    <FormField
                        control={form.control}
                        name="name"
                        render={({ field }) => (
                            <FormItem dir="rtl">
                                <FormLabel>الاسم</FormLabel>
                                <FormControl>
                                    <Input {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="email"
                        render={({ field }) => (
                            <FormItem dir="rtl">
                                <FormLabel>البريد الإلكتروني</FormLabel>
                                <FormControl>
                                    <Input type="email" {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="phone"
                        render={({ field }) => (
                            <FormItem dir="rtl">
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
                        name="address"
                        render={({ field }) => (
                            <FormItem dir="rtl">
                                <FormLabel>العنوان</FormLabel>
                                <FormControl>
                                    <Input {...field} />
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
                        {isLoading ? "جاري الحفظ..." : "حفظ التغييرات"}
                    </Button>
                </form>
            </Form>
        </div>
    );
}
