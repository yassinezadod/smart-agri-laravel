FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip nginx

# Nettoyage du cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP nécessaires à Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Récupération de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du dossier de travail
WORKDIR /var/www

# Copie du projet
COPY . .

# Installation des dépendances via Composer
RUN composer install --no-dev --optimize-autoloader

# Droits sur les dossiers de stockage pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exposition du port
EXPOSE 80

CMD ["php-fpm"]
