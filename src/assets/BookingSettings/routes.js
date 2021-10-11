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
        path: '/service',
        name: "Service",
        component: Service,
       
    },
    {
        path: '/provider',
        name: "Provider",
        component: Provider,

    },
    {
        path: '/general',
        name: "General",
        component: GeneralSettings,

    },


   
];
