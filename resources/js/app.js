import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import clickOutside from "./click-outside.js";

/* import the fontawesome core */
import { library } from "@fortawesome/fontawesome-svg-core";

/* import font awesome icon component */
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

/* import loading overlay */
import { LoadingPlugin } from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";

/* import Toast Notification */
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

/* import specific icons */
import {
    faGauge,
    faScroll,
    faHouse,
    faUser,
    faUserShield,
    faUserCheck,
    faFile,
    faPlus,
    faX,
    faCheck,
    faCertificate,
    faCalendarCheck,
    faClipboardQuestion,
    faBarsProgress,
    faRightFromBracket
} from "@fortawesome/free-solid-svg-icons";

/* add icons to the library */
library.add(faGauge);
library.add(faHouse);
library.add(faScroll);
library.add(faUser);
library.add(faUserShield);
library.add(faUserCheck);
library.add(faFile);
library.add(faPlus);
library.add(faX);
library.add(faCheck);
library.add(faCertificate);
library.add(faCalendarCheck);
library.add(faClipboardQuestion);
library.add(faBarsProgress);
library.add(faRightFromBracket);

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "FAMSI";

if (localStorage.getItem("theme") == "dark") {
    document.documentElement.classList.remove("light");
    document.documentElement.classList.add("dark");
} else {
    document.documentElement.classList.remove("dark");
    document.documentElement.classList.add("light");
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .component("font-awesome-icon", FontAwesomeIcon)
            .use(clickOutside)
            .use(LoadingPlugin)
            .use(Toast, { timeout: 4000, maxToasts: 5 })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: "#0b8006",
    },
});
