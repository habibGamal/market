import "../css/app.css";
import "./bootstrap";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import { registerSW } from "./register";
import { MainLayout } from "./Layouts/MainLayout";
const appName = import.meta.env.VITE_APP_NAME || "Laravel";

registerSW();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob("./Pages/**/*.tsx")
        ).then((page: any) => {
            if (page.default.layout === undefined) {
                // @ts-ignore
                page.default.layout = (page) => <MainLayout>{page}</MainLayout>;
            }
            return page;
        }),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: "#4B5563",
    },
});
