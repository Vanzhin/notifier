# Приложение на Симфони (Сервис уведомлений)

# Сервис уведомлений на Symfony

## 🎯 Назначение системы

Микросервис для обработки событий АТС и управления уведомлениями через:

- Email
- Telegram **
- SMS
- Webhook/Push

** - реализован

## 1. Термины

- Symfony — фреймворк основного контейнера (php-fpm). Можно почитать [здесь](https://symfony.com/doc/current/index.html)

## 2. Используемые технологии

Основные технологии: PHP, Redis, PostgreSQL, RabbitMQ
Код пишем на PHP

### 2.1 Общие технологии

| Технология        | Версия                | Описание                           | Ссылка                     |
|-------------------|-----------------------|------------------------------------|----------------------------|
| PHP (Symfony 7.3) | 8.4                   | PHP                                | https://www.php.net        |
| PostgreSQL        | 17.0-alpine           | Реляционная БД                     | https://www.postgresql.org |
| Nginx             | 1.25.4                | Прокси-сервер                      | https://www.nginx.com      |
| Redis             | 7.2.1                 | Быстрая БД для временного хранения | https://redis.io           |
| RabbitMQ          | 4.0-management-alpine | Брокер сообщений                   | https://www.rabbitmq.com   |

## 3. Подготовка окружения для запуска на локальной машине

1. Проверить, что установлен Git
    ```shell
    git -v
    ```
2. Установить [Docker-compose](https://docs.docker.com/compose/install/linux/#install-the-plugin-manually).

3. Проверить, что установлен php
    ```shell
    php -v
    ```
   если нет установить [php](https://www.php.net/downloads).

4. Проверить, что установлен composer
   ```shell
   composer
   ```

если нет установить [composer](https://getcomposer.org/download/).

## 4. Установка

1. Склонировать репозиторий в текущую директорию
    ```shell
    git clone https://github.com/Vanzhin/notifier.git
    ```
2. Создать файлы .env путем их копирования из .env.example в директориях docker и app, установить значение переменных
    ```shell
    cd ./docker
    ```
3. Перейти в директорию с файлом docker-compose.yaml
    ```shell
    cd ./docker
    ```

## 5. Запуск

   ```shell
   docker compose up -d
   ```

## 6. Инфо для разработки

a. Проверить код на соответствие стилю

   ```shell
    vendor/bin/php-cs-fixer fix --dry-run --diff
   ```

b. Исправить код

   ```shell
    vendor/bin/php-cs-fixer fix --diff
   ```

c. Проверить соответствие зависимостей

   ```shell
    vendor/bin/deptrac analyse --config-file=deptrac-modules.yaml
   ```

   ```shell
    vendor/bin/deptrac analyse --config-file=deptrac-layers.yaml
   ```