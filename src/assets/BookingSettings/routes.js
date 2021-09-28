import Service from './Components/Service';
import Bookings from './Components/Bookings';
import Provider from "./Components/Provider";
import GeneralSettings from "./Components/GeneralSettings";

export const routes = [
    {
        path: '/bookings',
        name: "Bookings",
        component: Bookings,

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
