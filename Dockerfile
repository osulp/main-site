FROM ghcr.io/osu-wams/php:8.2-apache AS production
ARG GITHUB_TOKEN=${GITHUB_TOKEN}
COPY docker-wams-entry /usr/local/bin
ENV PATH="$PATH:/var/www/html/vendor/bin"
USER root
RUN apt update && apt upgrade -y && apt -y install sendmail
WORKDIR /var/www/html
USER www-data
COPY --chown=www-data:www-data . /var/www/html
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN} \
  && composer install -o --no-dev
RUN mkdir -p /var/www/html/docroot/sites/default/files; \
  chown -R www-data:www-data /var/www/html/docroot/sites/default/files; \
  mkdir -p /var/www/files-private; \
  chown -R www-data:www-data /var/www/files-private;
VOLUME /var/www/html/docroot/sites/default/files
ENTRYPOINT [ "docker-wams-entry" ]
CMD [ "apache2-foreground" ]

FROM ghcr.io/osu-wams/php:8.2-apache-dev AS development
COPY docker-wams-entry /usr/local/bin
ENV PATH="$PATH:/var/www/html/vendor/bin"
USER root
RUN apt update && apt upgrade -y && apt -y install sendmail
USER www-data
WORKDIR /var/www/html
COPY --from=production /var/www/html /var/www/html
RUN composer install -o
ENTRYPOINT [ "docker-wams-entry" ]
CMD [ "apache2-foreground" ]
