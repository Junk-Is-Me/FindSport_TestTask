# Booking API — система бронирования слотов

# Рарзаботка велась с помощью Docker и Laravel-sail

## Требования

-   Docker и Docker Compose
-   Git
-   Compose

---

## Установка и запуск

```bash
# Клонируем репозиторий
git clone https://github.com/Junk-Is-Me/FindSport_TestTask
cd <в директорию с клонированным проектом>

# Копируем .env
cp .env.example .env

# Устанавливаем зависимости
composer install

# Запускаем сборку и запуск контейнеров
./vendor/bin/sail up -d

# Генерируем ключ приложения
 ./vendor/bin/sail artisan key:generate

# Применяем миграции
./vendor/bin/sail artisan migrate

# (Опционально, так как в тестах есть условие генерации) Генерируем данные сидером
./vendor/bin/sailartisan db:seed

# Запускаем тесты
./vendor/bin/sail artisan test
```
