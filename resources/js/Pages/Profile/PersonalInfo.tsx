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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { useState } from "react";

interface Props extends PageProps {
    businessTypes: {
        id: number;
        name: string;
    }[];
}

const formSchema = z.object({
    name: z.string().min(3, { message: "الاسم يجب أن يكون 3 أحرف على الأقل" }),
    email: z.string().email({ message: "يرجى إدخال بريد إلكتروني صحيح" }).optional().or(z.literal("")),
    whatsapp: z.string().optional().or(z.literal("")),
    business_type_id: z.string(),
});

export default function PersonalInfo({ auth, businessTypes }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const customer = auth.user;

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: customer.name || "",
            email: customer.email || "",
            whatsapp: customer.whatsapp || "",
            business_type_id: customer.business_type_id?.toString() || "",
        },
    });

    function onSubmit(values: z.infer<typeof formSchema>) {
        setIsSubmitting(true);

        router.post('/profile/update-personal-info', values, {
            onSuccess: () => {
                setIsSubmitting(false);
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
                    <CardTitle className="text-right">المعلومات الشخصية</CardTitle>
                    <CardDescription className="text-right">
                        قم بتعديل معلوماتك الشخصية
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form {...form}>
                        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                            <FormField
                                control={form.control}
                                name="name"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>الاسم</FormLabel>
                                        <FormControl>
                                            <Input {...field} dir="rtl" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <FormField
                                control={form.control}
                                name="email"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>البريد الإلكتروني</FormLabel>
                                        <FormControl>
                                            <Input {...field} dir="rtl" type="email" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <FormField
                                control={form.control}
                                name="whatsapp"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>رقم الواتساب</FormLabel>
                                        <FormControl>
                                            <Input {...field} dir="rtl" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <FormField
                                control={form.control}
                                name="business_type_id"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>نوع النشاط التجاري</FormLabel>
                                        <Select
                                            onValueChange={field.onChange}
                                            defaultValue={field.value}
                                        >
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="اختر نوع النشاط التجاري" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {businessTypes.map((type) => (
                                                    <SelectItem key={type.id} value={type.id.toString()}>
                                                        {type.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <Button type="submit" className="w-full" disabled={isSubmitting}>
                                {isSubmitting ? "جاري الحفظ..." : "حفظ التغييرات"}
                            </Button>
                        </form>
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}
