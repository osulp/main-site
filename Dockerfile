FROM ghcr.io/osu-wams/php:8.2-apache AS production
ARG SMTP_HOST=${SMTP_HOST}
COPY docker-wams-entry /usr/local/bin
ENV PATH="$PATH:/var/www/html/vendor/bin"
USER root
RUN apt update && apt upgrade -y && apt -y install sendmail
RUN echo "sendmail_path = /usr/sbin/sendmail -t -i " > /usr/local/etc/php/conf.d/docker-php-sendmail.ini \
    && echo "define(`SMART_HOST', `${SMTP_HOST}')dnl" \
    && make -C /etc/mail
WORKDIR /var/www/html
USER www-data
COPY --chown=www-data:www-data . /var/www/html
ARG GITHUB_TOKEN=${GITHUB_TOKEN}
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN} \
  && composer install -o --no-dev
ARG GITHUB_TOKEN=
RUN mkdir -p /var/www/html/docroot/sites/default/files; \
  chown -R www-data:www-data /var/www/html/docroot/sites/default/files; \
  mkdir -p /var/www/files-private; \
  chown -R www-data:www-data /var/www/files-private;
VOLUME /var/www/html/docroot/sites/default/files
ENTRYPOINT [ "docker-wams-entry" ]
CMD [ "apache2-foreground" ]

FROM ghcr.io/osu-wams/php:8.2-apache-dev AS development
ARG SMTP_HOST=${SMTP_HOST}
COPY docker-wams-entry /usr/local/bin
ENV PATH="$PATH:/var/www/html/vendor/bin"
USER root
RUN apt update && apt upgrade -y && apt -y install sendmail
RUN echo "sendmail_path = /usr/sbin/sendmail -t -i " > /usr/local/etc/php/conf.d/docker-php-sendmail.ini \
    && echo "define(`SMART_HOST', `${SMTP_HOST}')dnl" \
    && make -C /etc/mail
USER www-data
WORKDIR /var/www/html
COPY --from=production /var/www/html /var/www/html
ARG GITHUB_TOKEN=${GITHUB_TOKEN}
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN} \
  && composer install -o
ARG GITHUB_TOKEN=
ENTRYPOINT [ "docker-wams-entry" ]
CMD [ "apache2-foreground" ]
