<?php
namespace FF_Booking;
use FF_Booking\Request;

class Router
{
    /**
     * All registered routes.
     *
     * @var array
     */
    public $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    public function init($slug = 'handle_booking_ajax_endpoint')
    {
       
        add_action('wp_ajax_'.$slug, function(){
            $this->handleRoute();
        });
        add_action('wp_ajax_nopriv_'.$slug, function(){
            $this->handleRoute();
        });
        
    }
    
    /**
     * Load routes file.
     *
     * @param string $file
     */
    public static function load($file)
    {
        $router = new static;
        
        require FF_BOOKINGDIR_PATH.$file;
        
        return $router;
    }
    
    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param string $controller
     */
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    
    }
    
    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param string $controller
     */
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }
    
    /**
     * Load the requested URI's associated controller method.
     *
     * @param string $uri
     * @param string $requestType
     */
    public function handleRoute()
    {
        $uri = Request::route();
        $requestType = Request::method();

        if (array_key_exists($uri, $this->routes[$requestType])) {
            return $this->callAction(
                ...explode('@', $this->routes[$requestType][$uri])
            );
        }
        
        throw new \Exception('No route defined for this URI.');
    }
    
    /**
     * Load and call the relevant controller action.
     *
     * @param string $controller
     * @param string $action
     */
    protected function callAction($controller, $action)
    {
        $controller = "FF_Booking\\Booking\\{$controller}";
        $controller = new $controller;
        
        if (! method_exists($controller, $action)) {
            throw new Exception(
                "{$controller} does not respond to the {$action} action."
            );
        }
        
        return $controller->$action();
    }
}
