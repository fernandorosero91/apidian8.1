FROM nginx:1.25-alpine

# Eliminar config por defecto
RUN rm /etc/nginx/conf.d/default.conf

# Copiar configuración nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/sites-available/default.conf /etc/nginx/conf.d/default.conf

# Copiar el código del proyecto (solo public para archivos estáticos)
COPY public /var/www/html/public

# Crear directorios
RUN mkdir -p /var/log/nginx /var/cache/nginx /var/www/html/storage/app/public \
    && chown -R nginx:nginx /var/log/nginx /var/cache/nginx

WORKDIR /var/www/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
