<?php
class Router {
    private $routes = [];
    
    public function add($method, $uri, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function route($uri, $method) {
        foreach ($this->routes as $route) {
            // Convert URI pattern to regex (handle {param} placeholders)
            $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^\/]+)', $route['uri']);
            $pattern = "@^" . $pattern . "$@D";
            
            if ($route['method'] === strtoupper($method) && preg_match($pattern, $uri, $matches)) {
                // Filter matches to only get named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                require_once "../app/controllers/{$route['controller']}.php";
                $controllerInstance = new $route['controller']();
                
                // Call the action with parameters
                call_user_func_array([$controllerInstance, $route['action']], $params);
                return;
            }
        }
        
        // 404 Not Found
       // http_response_code(404);
        //require_once "../app/views/errors/404.php";
        //exit();
    }
}
?>