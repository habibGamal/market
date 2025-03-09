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
import { Link, router } from "@inertiajs/react";
import { PasswordInput } from "@/Components/ui/password-input";

const formSchema = z.object({
    phone: z.string().min(11, "رقم الهاتف يجب أن يكون 11 رقم"),
    password: z.string().min(1, "كلمة المرور مطلوبة"),
});

export default function Login() {
    const [isLoading, setIsLoading] = useState(false);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            phone: "",
            password: "",
        },
    });

    const onSubmit = async (data: z.infer<typeof formSchema>) => {
        router.post("/login", data, {
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

    return (
        <div className="container max-w-lg mx-auto py-4 space-y-6">
            <div className="text-center space-y-2">
                <h1 className="text-2xl font-bold">تسجيل الدخول</h1>
                <p className="text-secondary-500">أدخل بياناتك لتسجيل الدخول</p>
            </div>

            <Form {...form}>
                <form
                    onSubmit={form.handleSubmit(onSubmit)}
                    className="space-y-4"
                >
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
                        name="password"
                        render={({ field }) => (
                            <FormItem dir="rtl">
                                <FormLabel>كلمة المرور</FormLabel>
                                <FormControl>
                                    <PasswordInput {...field} />
                                </FormControl>
                                <FormMessage />
                                <Link
                                    href="/forgot-password"
                                    className="text-sm text-primary hover:underline block text-left"
                                >
                                    نسيت كلمة المرور؟
                                </Link>
                            </FormItem>
                        )}
                    />

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={isLoading}
                    >
                        {isLoading ? "جاري تسجيل الدخول..." : "تسجيل الدخول"}
                    </Button>
                </form>
            </Form>
        </div>
    );
}
