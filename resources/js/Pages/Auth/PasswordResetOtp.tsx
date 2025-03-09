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
import { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios";
import { PasswordInput } from "@/Components/ui/password-input";

interface Props {
    phone: string;
}

const formSchema = z.object({
    otp: z.string().length(6, "يجب أن يكون رمز التحقق 6 أرقام"),
    password: z.string().min(8, "كلمة المرور يجب أن تكون 8 أحرف على الأقل"),
});

export default function PasswordResetOtp({ phone }: Props) {
    const [isLoading, setIsLoading] = useState(false);
    const [countdown, setCountdown] = useState(0);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            otp: "",
            password: "",
        },
    });

    const startCountdown = () => {
        setCountdown(60);
    };

    useEffect(() => {
        if (countdown > 0) {
            const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
            return () => clearTimeout(timer);
        }
    }, [countdown]);

    useEffect(() => {
        handleSendOtp();
    }, []);

    const handleSendOtp = async () => {
        try {
            const response = await axios.post("/forgot-password/otp/send");
            if (process.env.NODE_ENV === "development" && response.data.otp) {
                console.log("Password Reset OTP:", response.data.otp);
            }
            startCountdown();
        } catch (error) {
            console.error("Failed to send OTP:", error);
        }
    };

    const onSubmit = (data: z.infer<typeof formSchema>) => {
        router.post("/reset-password", data, {
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
            <div className="text-center space-y-2">
                <h1 className="text-2xl font-bold">إعادة تعيين كلمة المرور</h1>
                <p className="text-secondary-500">
                    تم إرسال رمز التحقق إلى {phone}
                </p>
            </div>

            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                    <FormField
                        control={form.control}
                        name="otp"
                        render={({ field }) => (
                            <FormItem dir="rtl">
                                <FormLabel>رمز التحقق</FormLabel>
                                <FormControl>
                                    <Input
                                        type="text"
                                        inputMode="numeric"
                                        maxLength={6}
                                        {...field}
                                    />
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
                                <FormLabel>كلمة المرور الجديدة</FormLabel>
                                <FormControl>
                                    <PasswordInput {...field} />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <Button type="submit" className="w-full" disabled={isLoading}>
                        {isLoading ? "جاري التحقق..." : "تغيير كلمة المرور"}
                    </Button>

                    <div className="text-center">
                        {countdown > 0 ? (
                            <p className="text-sm text-gray-500">
                                يمكنك طلب رمز جديد بعد {countdown} ثانية
                            </p>
                        ) : (
                            <Button
                                type="button"
                                variant="link"
                                className="text-sm"
                                onClick={handleSendOtp}
                            >
                                إعادة إرسال رمز التحقق
                            </Button>
                        )}
                    </div>
                </form>
            </Form>
        </div>
    );
}
