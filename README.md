**Развертывание проекта**  
Последовательно выполнить команды  
```
mkdir laravel-test && cd laravel-test
git init
git pull https://github.com/TomLepsky/laravel-test
docker pull composer/composer
docker run --rm -it -v "$(pwd):/app" composer/composer install
./vendor/bin/sail build
./vendor/bin/sail up -d
./vendor/bin/sail php artisan env:decrypt --key=base64:GpqlL+XPfLgPToA00EkgWpFeof5EXGoGWY9ox+azkVg=
./vendor/bin/sail restart
./vendor/bin/sail php artisan migrate
```

*В случае ошибки mysql выполнить*  
```
docker exec -ti laravel-test_mysql_1 bash
mysql
GRANT ALL ON *.* TO 'sail'@'%' WITH GRANT OPTION;
CREATE USER 'sail'@'%' IDENTIFIED BY 'password';
exit
exit
./vendor/bin/sail php artisan migrate
```
