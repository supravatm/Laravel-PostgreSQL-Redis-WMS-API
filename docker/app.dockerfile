FROM php:8.4-fpm

RUN apt-get update && apt-get install -y  \
    --no-install-recommends \
    libpq-dev \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add Composer to the PATH
ENV PATH="/usr/local/bin:$PATH"