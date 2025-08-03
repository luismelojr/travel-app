FROM php:8.3-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    supervisor

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Xdebug para cobertura de testes
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copiar configuração customizada do Xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário para aplicação
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copiar configuração do supervisor
COPY supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Mudar ownership dos diretórios de trabalho para o usuário www
RUN chown -R www:www /var/www/html

# Expor porta 8000
EXPOSE 8000

# Comando padrão
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]