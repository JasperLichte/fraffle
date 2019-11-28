<?php

$filePath = '';
$exception = null;
try {
    $filePath = getFileNameByRouteName((isset($_GET['r']) && !empty($_GET['r']) ? $_GET['r'] : ''));
} catch (Exception $e) {
   $exception = $e;
}

if ($exception != null || !checkNode()) {
    http_response_code(500);
    echo json_encode(['error' => ($exception != null
        ? $e->getMessage()
        : 'Failed to run node')]);
    exit();
}

echo run($filePath);

function getFileNameByRouteName(string $routeName): string {
    if (!is_file('./conf/routes/routes.json')) {
        throw new Exception('No such file found: conf/routes/routes.json');
    }

    try {
        $routes = json_decode(file_get_contents('./conf/routes/routes.json'), true);
    } catch(Exception $exception) {
        throw new Exception('An error occurred parsing /conf/routes/routes.json');
    }

    $filePath = (isset($routes[$routeName])
        && is_string($routes[$routeName])
        && !empty($routes[$routeName])
            ? $routes[$routeName]
            : ($routeName === '404'
                ? ''
                : getFileNameByRouteName('404')));

    if (empty($filePath) || !is_file(fullFilePathByRoute($filePath))) {
        throw new Exception('No file ' . $filePath . ' found for route');
    }

    return fullFilePathByRoute($filePath);
}

function fullFilePathByRoute(string $route): string {
    return './api/' . $route;
}

function checkNode(): bool {
    return true;
    $windows = strpos(PHP_OS, 'WIN') === 0;
    $test = $windows ? 'where' : 'command -v';
    return is_executable(trim(shell_exec("$test node")));
}

function run(string $filePath): string {
    $res = '';
    try {
        $res = exec('node ' . $filePath);
    } catch(Exception $e) {}

    return $res;
}
