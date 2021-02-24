import Service from './Components/Service';
import Bookings from './Components/Bookings';
import index from './Components/index';

export const routes = [
    {
        path: '/service',
        name: "service",
        component: Service,
       
    },
     {
        path: '/bookings',
        name: "bookings",
        component: Bookings,
       
    },
   
];
