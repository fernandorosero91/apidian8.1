FROM nginx:1.25-alpine

# Eliminar config por defecto
RUN rm /etc/nginx/conf.d/default.conf

# Copiar configuración nginx
COPY nginx.conf /etc/nginx/nginx.conf
COPY sites-available/default.conf /etc/nginx/conf.d/default.conf

# Crear directorios
RUN mkdir -p /var/log/nginx /var/cache/nginx \
    && chown -R nginx:nginx /var/log/nginx /var/cache/nginx

WORKDIR /var/www/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
