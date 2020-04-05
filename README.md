mkdir myapp

cd ./myapp

curl -LO https://raw.githubusercontent.com/bitnami/bitnami-docker-laravel/master/docker-compose.yml

docker-compose up

docker-compose exec myapp composer require ya-tmch/kick

add to ./routes/web.php:

Route::get('/receive', function (\YaTmch\Kick\Service $service) {
    return response($service->receive(), 200);
});

try http://0.0.0.0:3000/receive
