FROM nginx:1.25-alpine

# Eliminar configuración por defecto
RUN rm /etc/nginx/conf.d/default.conf

# Copiar configuración optimizada
COPY nginx.conf /etc/nginx/nginx.conf
COPY sites-available/default.conf /etc/nginx/conf.d/default.conf

# Crear directorios necesarios
RUN mkdir -p /var/log/nginx /var/cache/nginx \
    && chown -R nginx:nginx /var/log/nginx /var/cache/nginx

WORKDIR /var/www/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
