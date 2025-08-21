FROM ghcr.io/osu-wams/php:8.2-apache AS production
ENV PATH="$PATH:/var/www/html/vendor/bin"
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
CMD [ "apache2-foreground" ]

FROM ghcr.io/osu-wams/php:8.2-apache-dev AS development
ARG SMTP_HOST=${SMTP_HOST}
ENV PATH="$PATH:/var/www/html/vendor/bin"
WORKDIR /var/www/html
COPY --from=production /var/www/html /var/www/html
ARG GITHUB_TOKEN=${GITHUB_TOKEN}
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN} \
  && composer install -o
ARG GITHUB_TOKEN=
CMD [ "apache2-foreground" ]
