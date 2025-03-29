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
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { ArrowLeft, ArrowRight, InfoIcon, SendIcon } from "lucide-react";
import { useState, useEffect } from "react";
import axios from "axios";
import { PasswordInput } from "@/Components/ui/password-input";

interface Props extends PageProps {
    phone: string;
}

const formSchema = z.object({
    otp: z.string().length(6, { message: "يجب أن يكون رمز التحقق 6 أرقام" }),
    password: z.string().min(8, { message: "كلمة المرور يجب أن تكون 8 أحرف على الأقل" }),
});

export default function ChangePassword({ auth, phone }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [countdown, setCountdown] = useState(0);
    const [otpSent, setOtpSent] = useState(false);
    const [isRequestingOtp, setIsRequestingOtp] = useState(false);

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

    const handleSendOtp = async () => {
        setIsRequestingOtp(true);
        try {
            const response = await axios.post("/profile/send-otp");
            if (process.env.NODE_ENV === "development" && response.data.otp) {
                console.log("Password Change OTP:", response.data.otp);
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

        router.post('/profile/update-password', values, {
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
                    <CardTitle className="text-right">تغيير كلمة المرور</CardTitle>
                    <CardDescription className="text-right">
                        سيتم إرسال رمز التحقق إلى رقم هاتفك {phone}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!otpSent && (
                        <Alert className="mb-4">
                            <InfoIcon className="h-4 w-4" />
                            <AlertDescription>
                                يرجى طلب رمز التحقق أولاً لتغيير كلمة المرور
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
                                name="password"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>كلمة المرور الجديدة</FormLabel>
                                        <FormControl>
                                            <PasswordInput {...field} dir="rtl" />
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
                                {isSubmitting ? "جاري التحقق..." : "تغيير كلمة المرور"}
                            </Button>
                        </form>
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}
