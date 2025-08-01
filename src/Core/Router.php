// ...existing code...
    public function resolve()
    {
        $path = $this->request->getPath();
// ...existing code...
        $route = $this->routes[$method][$path] ?? false;

        if ($route === false) {
            foreach ($this->routes[$method] as $routePath => $callback) {
                if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $routePath, $matches)) {
                    $pattern = preg_replace_callback('/\{\w+(:([^}]+))?}/', fn($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $routePath);
                    if (preg_match("#^$pattern$#", $path, $routeMatches)) {
                        array_shift($routeMatches);
                        $params = array_combine($matches[1], $routeMatches);
                        $this->request->setRouteParams($params);
                        $route = $callback;
                        break;
                    }
                }
            }
        }

        if ($route === false) {
            $this->response->setStatusCode(404);
// ...existing code...

