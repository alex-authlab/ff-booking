import Services from './Components/Services';
import Service from './Components/Service';
import Bookings from './Components/Bookings';
import BookingInfo from "./Components/BookingInfo";
import Provider from "./Components/Provider";
import GeneralSettings from "./Components/GeneralSettings";

export const routes = [
    {
        path: '/bookings',
        name: "Bookings",
        component: Bookings,
        children:[
            {
                path: ':bookingId',
                name: "BookingInfo",
                component: BookingInfo,
                props: true,
                meta: {
                    show_modal: true
                }
            }
        ]

    },
    {
        path: '/services',
        name: "Services",
        component: Services,

    },
    {
        path: '/service',
        name: "Service",
        component: Service,
        props: route => ({ service_id: route.query.service_id })
    },
    {
        path: '/providers',
        name: "Providers",
        component: Provider,

    },
    {
        path: '/settings',
        name: "Settings",
        component: GeneralSettings,

    },


   
];
