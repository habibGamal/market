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
import { PageTitle } from "@/Components/ui/page-title";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { AlertTriangle, ArrowRight, Trash } from "lucide-react";
import { useState } from "react";
import { PasswordInput } from "@/Components/ui/password-input";

const formSchema = z.object({
    password: z.string().min(1, { message: "يرجى إدخال كلمة المرور" }),
});

export default function DeleteAccount({ auth }: PageProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            password: "",
        },
    });

    function onSubmit(values: z.infer<typeof formSchema>) {
        setIsSubmitting(true);

        router.delete('/profile',  {
            data: values,
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

            <Card className="border-destructive/30">
                <CardHeader>
                    <CardTitle className="text-right flex items-center gap-2">
                        <Trash className="h-5 w-5 text-destructive" />
                        حذف الحساب
                    </CardTitle>
                    <CardDescription className="text-right">
                        هذا الإجراء سيحذف حسابك نهائيًا من النظام
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Alert className="mb-4 border-destructive/50 text-destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>
                            تنبيه: عند حذف حسابك، سيتم حذف جميع بياناتك نهائياً من النظام. لن تتمكن من استعادة حسابك أو الوصول إلى بياناتك مرة أخرى.
                        </AlertDescription>
                    </Alert>

                    <Form {...form}>
                        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                            <FormField
                                control={form.control}
                                name="password"
                                render={({ field }) => (
                                    <FormItem className="text-right">
                                        <FormLabel>أدخل كلمة المرور للتأكيد</FormLabel>
                                        <FormControl>
                                            <PasswordInput {...field} dir="rtl" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />

                            <div className="flex flex-col-reverse sm:flex-row gap-2 items-center justify-between pt-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => router.visit('/profile')}
                                    disabled={isSubmitting}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={isSubmitting || !form.formState.isValid}
                                    className="w-full sm:w-auto"
                                >
                                    {isSubmitting ? "جاري الحذف..." : "حذف الحساب نهائياً"}
                                </Button>
                            </div>
                        </form>
                    </Form>
                </CardContent>
            </Card>
        </div>
    );
}
