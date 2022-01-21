Зависимости
===

Установить ``oauth-token`` от ``Github``

```
composer config -g github-oauth.github.com <токен>
```

Удалить плагин ``Composer`` для зависимостей ``bower`` и ``npm``

```
composer global remove "fxp/composer-asset-plugin"
```

Загрузить зависимости для разработки

```
composer install
```

Если разворачиваете на боевом сервере, то

```
composer install --no-dev
```